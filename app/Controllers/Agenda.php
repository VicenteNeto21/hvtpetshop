<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\AgendamentoModel;
use App\Models\PetModel;
use App\Models\ServicoModel;
use CodeIgniter\I18n\Time;

class Agenda extends BaseController
{
    public function index()
    {
        $dataSelecionada = $this->request->getGet('data') ?? date('Y-m-d');
        $status = $this->request->getGet('status');

        $agendamentoModel = new AgendamentoModel();

        // Usando query agrupada para evitar duplicidade de serviços por linha
        $query = $agendamentoModel->select('MIN(agendamentos.id) as id, pets.nome as pet_nome, pets.especie as pet_especie, tutores.nome as tutor_nome, GROUP_CONCAT(servicos.nome SEPARATOR ", ") as servico_nome, agendamentos.data_hora, agendamentos.status, agendamentos.transporte, agendamentos.observacoes')
                    ->join('pets', 'pets.id = agendamentos.pet_id')
                    ->join('tutores', 'tutores.id = pets.tutor_id')
                    ->join('servicos', 'servicos.id = agendamentos.servico_id')
                    ->groupBy('agendamentos.pet_id, agendamentos.data_hora, pets.nome, pets.especie, tutores.nome, agendamentos.status, agendamentos.transporte, agendamentos.observacoes');

        if ($status === 'Pendente') {
            $query->where('agendamentos.status', $status);
        } else {
            $query->where("DATE(agendamentos.data_hora)", $dataSelecionada);
            if ($status) {
                $query->where('agendamentos.status', $status);
            }
        }

        $agendamentos = $query->orderBy('data_hora', 'ASC')->findAll();

        // Estatísticas agrupadas
        $stats = [
            'hoje_total' => $agendamentoModel->where("DATE(data_hora)", date('Y-m-d'))
                                            ->where('status !=', 'Cancelado')
                                            ->groupBy('pet_id, data_hora')
                                            ->countAllResults(),
            'hoje_finalizados' => $agendamentoModel->where("DATE(data_hora)", date('Y-m-d'))
                                                  ->where('status', 'Finalizado')
                                                  ->groupBy('pet_id, data_hora')
                                                  ->countAllResults(),
        ];

        return view('agenda/index', [
            'agendamentos' => $agendamentos,
            'dataSelecionada' => $dataSelecionada,
            'statusSelecionado' => $status,
            'stats' => $stats
        ]);
    }

    public function novo()
    {
        $petModel = new PetModel();
        $servicoModel = new ServicoModel();
        
        $data = [
            'pets' => $petModel->select('pets.*, tutores.nome as tutor_nome')
                               ->join('tutores', 'tutores.id = pets.tutor_id')
                               ->orderBy('pets.nome', 'ASC')
                               ->findAll(),
            'servicos' => $servicoModel->orderBy('nome', 'ASC')->findAll(),
            'preselected_pet_id' => $this->request->getGet('pet')
        ];

        return view('agenda/novo', $data);
    }

    public function cadastroRapido()
    {
        return view('agenda/cadastro_rapido');
    }

    public function salvarCadastroRapido()
    {
        $rules = [
            'tutor_nome' => 'required|min_length[3]',
            'tutor_telefone' => 'required',
            'pet_nome' => 'required',
            'pet_especie' => 'required',
            'pet_sexo' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $db = \Config\Database::connect();
        $tutorModel = new \App\Models\TutorModel();
        $petModel = new \App\Models\PetModel();

        try {
            $db->transBegin();

            // 1. Criar Tutor
            $tutorData = [
                'nome' => $this->request->getPost('tutor_nome'),
                'telefone' => $this->request->getPost('tutor_telefone'),
                'email' => null // Simplificado
            ];
            $tutorId = $tutorModel->insert($tutorData);

            if (!$tutorId) {
                throw new \Exception('Erro ao criar tutor.');
            }

            // 2. Criar Pet
            $petData = [
                'tutor_id' => $tutorId,
                'nome' => $this->request->getPost('pet_nome'),
                'especie' => $this->request->getPost('pet_especie'),
                'sexo' => $this->request->getPost('pet_sexo'),
                'raca' => $this->request->getPost('pet_raca'),
            ];
            $petId = $petModel->insert($petData);

            if (!$petId) {
                throw new \Exception('Erro ao criar pet.');
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Erro na transação.');
            } else {
                $db->transCommit();
                // Redireciona de volta para Agendamento com o Pet Pré-selecionado
                return redirect()->to('agenda/novo?pet=' . $petId)->with('success', 'Cadastro realizado! Prossiga com o agendamento.');
            }

        } catch (\Exception $e) {
            $db->transRollback();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function salvar()
    {
        $rules = [
            'pet_id' => 'required|integer',
            'data' => 'required|valid_date',
            'horario' => 'required',
            'servicos' => 'required' // Array check needs validation logic or manual check
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $petId = $this->request->getPost('pet_id');
        $data = $this->request->getPost('data');
        $horario = $this->request->getPost('horario');
        $servicos = $this->request->getPost('servicos'); // Array
        $observacoes = $this->request->getPost('observacoes');
        $transporte = $this->request->getPost('transporte');
        $recorrenciaTipo = $this->request->getPost('recorrencia_tipo') ?? 'unico';
        $repeticoes = (int)($this->request->getPost('recorrencia_repeticoes') ?? 1);
        if ($recorrenciaTipo === 'unico') $repeticoes = 1;

        $agendamentoModel = new AgendamentoModel();
        $db = \Config\Database::connect();
        $recorrenciaGrupoId = ($recorrenciaTipo !== 'unico') ? uniqid('rec_') : null;
        
        try {
            $db->transBegin();
            
            for ($i = 0; $i < $repeticoes; $i++) {
                // Calcular data baseada na recorrência
                $dataObjeto = new \DateTime($data . ' ' . $horario);
                if ($i > 0) {
                    if ($recorrenciaTipo === 'semanal') {
                        $dataObjeto->modify("+$i week");
                    } elseif ($recorrenciaTipo === 'quinzenal') {
                        $dias = $i * 14;
                        $dataObjeto->modify("+$dias days");
                    } elseif ($recorrenciaTipo === 'mensal') {
                        $dataObjeto->modify("+$i month");
                    }
                }
                
                $dataHoraFinal = $dataObjeto->format('Y-m-d H:i:s');

                // Verificar se o pet já tem agendamento nesse exato momento (evitar duplicados no loop)
                // Ou se o slot está ocupado (opcional, mas bom para robustez)
                
                foreach ($servicos as $servicoId) {
                    $agendamentoModel->insert([
                        'pet_id' => $petId,
                        'servico_id' => $servicoId,
                        'data_hora' => $dataHoraFinal,
                        'transporte' => $transporte,
                        'observacoes' => ($i > 0 ? "[Recorrência " . ($i+1) . "/$repeticoes] " : "") . $observacoes,
                        'status' => 'Pendente',
                        'usuario_id' => session()->get('usuario_id') ?? 1,
                        'recorrencia_grupo_id' => $recorrenciaGrupoId,
                        'recorrencia_tipo' => $recorrenciaTipo
                    ]);
                }
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Erro ao salvar agendamento recorrente.');
            } else {
                $db->transCommit();
                $msg = ($recorrenciaTipo !== 'unico') ? "Agendamento recorrente ($repeticoes vezes) realizado com sucesso!" : "Agendamento realizado com sucesso!";
                return redirect()->to('agenda')->with('success', $msg);
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * AJAX: Retorna horários com status de disponibilidade
     */
    public function horariosDisponiveis()
    {
        $data = $this->request->getGet('data');
        if (!$data) return $this->response->setJSON([]);

        // Horários de funcionamento (Ex: 08:00 as 18:00)
        $todosHorarios = [];
        for ($h = 8; $h < 18; $h++) {
            $todosHorarios[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
            $todosHorarios[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':30';
        }

        // Buscar horários ocupados - usando BETWEEN para usar o índice
        $agendamentoModel = new AgendamentoModel();
        $ocupados = $agendamentoModel
            ->select('data_hora')
            ->where('data_hora >=', $data . ' 00:00:00')
            ->where('data_hora <=', $data . ' 23:59:59')
            ->where('status !=', 'Cancelado')
            ->findColumn('data_hora') ?? [];

        // Mapeia para formato H:i
        $horariosOcupados = [];
        foreach ($ocupados as $dt) {
            $horariosOcupados[] = date('H:i', strtotime($dt));
        }

        // Retorna todos os horários com status
        $resultado = [];
        foreach ($todosHorarios as $horario) {
            $resultado[] = [
                'horario' => $horario,
                'disponivel' => !in_array($horario, $horariosOcupados)
            ];
        }

        return $this->response->setJSON($resultado);
    }

    public function concluir($id)
    {
        $agendamentoModel = new AgendamentoModel();
        $ag = $agendamentoModel->find($id);
        
        if ($ag) {
            // Finalizar todos os serviços deste pet neste horário
            $agendamentoModel->where('pet_id', $ag['pet_id'])
                             ->where('data_hora', $ag['data_hora'])
                             ->where('status !=', 'Cancelado')
                             ->set(['status' => 'Finalizado'])
                             ->update();
            
            return redirect()->to('agenda/ficha/' . $id)->with('success', 'Atendimento iniciado/concluído!');
        }
        
        return redirect()->back()->with('error', 'Agendamento não encontrado.');
    }

    public function cancelar($id)
    {
        $agendamentoModel = new AgendamentoModel();
        $ag = $agendamentoModel->find($id);
        
        if ($ag) {
            // Cancelar todos os serviços deste pet neste horário
            $agendamentoModel->where('pet_id', $ag['pet_id'])
                             ->where('data_hora', $ag['data_hora'])
                             ->set(['status' => 'Cancelado'])
                             ->update();
            
            return redirect()->back()->with('success', 'Atendimento cancelado por completo.');
        }
        
        return redirect()->back()->with('error', 'Agendamento não encontrado.');
    }

    public function excluir($id)
    {
        $agendamentoModel = new AgendamentoModel();
        $ag = $agendamentoModel->find($id);
        
        if ($ag) {
            // Excluir todos os serviços deste pet neste horário
            $agendamentoModel->where('pet_id', $ag['pet_id'])
                             ->where('data_hora', $ag['data_hora'])
                             ->delete();
            
            return redirect()->back()->with('success', 'Agendamento excluído com sucesso.');
        }
        
        return redirect()->back()->with('error', 'Agendamento não encontrado.');
    }

    public function ficha($id)
    {
        $agendamentoModel = new AgendamentoModel();
        $fichaModel = new \App\Models\FichaModel();
        $obsVisualModel = new \App\Models\ObservacaoVisualModel();
        $servicoModel = new \App\Models\ServicoModel();

        // 1. Pegar info base do agendamento solicitado
        $agBase = $agendamentoModel->find($id);
        if (!$agBase) {
            return redirect()->to('agenda')->with('error', 'Agendamento não encontrado.');
        }

        // 2. Buscar dados agrupados por Pet e Horário (Harmonia)
        $agendamento = $agendamentoModel->select('agendamentos.*, pets.nome as pet_nome, pets.especie, pets.raca, pets.sexo, tutores.nome as tutor_nome, tutores.telefone as tutor_telefone, GROUP_CONCAT(servicos.nome SEPARATOR ", ") as servicos_previstos, GROUP_CONCAT(servicos.id) as servicos_ids')
            ->join('pets', 'pets.id = agendamentos.pet_id')
            ->join('tutores', 'tutores.id = pets.tutor_id')
            ->join('servicos', 'servicos.id = agendamentos.servico_id')
            ->where('agendamentos.pet_id', $agBase['pet_id'])
            ->where('agendamentos.data_hora', $agBase['data_hora'])
            ->groupBy('agendamentos.pet_id, agendamentos.data_hora')
            ->first();

        if (!$agendamento) {
            return redirect()->to('agenda')->with('error', 'Erro ao carregar dados do atendimento.');
        }

        // Ficha existente?
        $ficha = $fichaModel->where('agendamento_id', $id)->first();
        
        // Dados auxiliares
        $data = [
            'agendamento' => $agendamento,
            'ficha' => $ficha,
            'obs_visuais' => $obsVisualModel->findAll(),
            'servicos' => $servicoModel->where('id !=', 99)->orderBy('nome', 'ASC')->findAll(),
            'obs_marcadas' => [],
            'servicos_realizados' => []
        ];

        if ($ficha) {
            $db = \Config\Database::connect();
            $data['obs_marcadas'] = $db->table('ficha_observacoes')->where('ficha_id', $ficha['id'])->get()->getResultArray();
            $data['servicos_realizados'] = $db->table('ficha_servicos_realizados')->where('ficha_id', $ficha['id'])->get()->getResultArray();
        } else {
            // Se for ficha nova, pré-carregar os serviços que foram agendados
            $idsServicosAgendados = explode(',', $agendamento['servicos_ids']);
            foreach ($idsServicosAgendados as $sId) {
                $data['servicos_realizados'][] = ['servico_id' => $sId];
            }
        }

        // Se o status for finalizado, mostra a tela de visualização (somente leitura)
        if ($agendamento['status'] === 'Finalizado') {
            return view('agenda/visualizar_ficha', $data);
        }

        return view('agenda/ficha', $data);
    }

    public function salvarFicha()
    {
        $agendamentoId = $this->request->getPost('agendamento_id');
        $fichaModel = new \App\Models\FichaModel();
        $db = \Config\Database::connect();

        try {
            $db->transBegin();

            // Dados básicos da ficha
            $fichaData = [
                'agendamento_id' => $agendamentoId,
                'funcionario_id' => session('user_id') ?? 1,
                'altura_pelos' => $this->request->getPost('altura_pelos'),
                'doenca_pre_existente' => $this->request->getPost('doenca_pre_existente'),
                'doenca_ouvido' => $this->request->getPost('doenca_ouvido'),
                'doenca_pele' => $this->request->getPost('doenca_pele'),
                'observacoes' => $this->request->getPost('observacoes'),
                'comportamento_pet' => $this->request->getPost('comportamento_pet'),
                'recomendacoes_tutor' => $this->request->getPost('recomendacoes_tutor'),
            ];

            // Ficha básica
            $fichaExistente = $fichaModel->where('agendamento_id', $agendamentoId)->first();

            if ($fichaExistente) {
                $fichaModel->update($fichaExistente['id'], $fichaData);
                $fichaId = $fichaExistente['id'];
                
                // Limpar relacionamentos antigos
                $db->table('ficha_observacoes')->where('ficha_id', $fichaId)->delete();
                $db->table('ficha_servicos_realizados')->where('ficha_id', $fichaId)->delete();
            } else {
                $fichaId = $fichaModel->insert($fichaData);
            }

            // --- Harmonia: Finalizar todo o grupo ---
            $agModel = new \App\Models\AgendamentoModel();
            $agOriginal = $agModel->find($agendamentoId);
            if ($agOriginal) {
                $agModel->where('pet_id', $agOriginal['pet_id'])
                        ->where('data_hora', $agOriginal['data_hora'])
                        ->where('status !=', 'Cancelado')
                        ->set(['status' => 'Finalizado'])
                        ->update();
            }
            // ----------------------------------------

            // Salvar Observações Visuais
            $obsVisuais = $this->request->getPost('observacao_visual');
            if ($obsVisuais) {
                foreach ($obsVisuais as $obsId => $val) {
                    $outros = ($obsId == 7) ? $this->request->getPost('observacao_visual_outros') : null;
                    $db->table('ficha_observacoes')->insert([
                        'ficha_id' => $fichaId, 
                        'observacao_id' => $obsId, 
                        'outros_detalhes' => $outros
                    ]);
                }
            }

            // Salvar Serviços Realizados
            $servicosRealizados = $this->request->getPost('servicos_realizados');
            if ($servicosRealizados) {
                foreach ($servicosRealizados as $servId) {
                    $db->table('ficha_servicos_realizados')->insert([
                        'ficha_id' => $fichaId,
                        'servico_id' => $servId
                    ]);
                }
            }

            // Atualizar status do agendamento para Finalizado
            $agendamentoModel = new AgendamentoModel();
            $agendamentoModel->update($agendamentoId, ['status' => 'Finalizado']);

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Erro ao salvar ficha.');
            } else {
                $db->transCommit();
                return redirect()->to('agenda')->with('success', 'Ficha salva e atendimento finalizado!');
            }

        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }
}
