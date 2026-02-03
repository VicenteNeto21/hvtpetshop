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
        
        $query = $agendamentoModel->select('agendamentos.*, pets.nome as pet_nome, servicos.nome as servico_nome, tutores.nome as tutor_nome')
            ->join('pets', 'pets.id = agendamentos.pet_id')
            ->join('tutores', 'tutores.id = pets.tutor_id')
            ->join('servicos', 'servicos.id = agendamentos.servico_id');

        if ($status === 'Pendente') {
            // Se for pendente, mostra todos (global) para encerrar, ordenado por data (antigos primeiro)
            $query->where('agendamentos.status', $status);
        } else {
            // Para outros, filtra por dia
            $query->where("DATE(data_hora)", $dataSelecionada);
            if ($status) {
                $query->where('agendamentos.status', $status);
            }
        }

        $agendamentos = $query->orderBy('data_hora', 'ASC')->findAll();

        // Estatísticas
        $stats = [
            'hoje_total' => $agendamentoModel->where("DATE(data_hora)", date('Y-m-d'))->where('status !=', 'Cancelado')->countAllResults(),
            'hoje_finalizados' => $agendamentoModel->where("DATE(data_hora)", date('Y-m-d'))->where('status', 'Finalizado')->countAllResults(),
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

        $agendamentoModel = new AgendamentoModel();
        $db = \Config\Database::connect();
        
        try {
            $db->transBegin();
            
            $dataHora = $data . ' ' . $horario . ':00';

            foreach ($servicos as $servicoId) {
                $agendamentoModel->insert([
                    'pet_id' => $petId,
                    'servico_id' => $servicoId,
                    'data_hora' => $dataHora,
                    'transporte' => $transporte,
                    'observacoes' => $observacoes,
                    'status' => 'Pendente',
                    'criado_por' => session('user_id') ?? 1 // Fallback temp
                ]);
            }

            if ($db->transStatus() === false) {
                $db->transRollback();
                return redirect()->back()->withInput()->with('error', 'Erro ao salvar agendamento.');
            } else {
                $db->transCommit();
                return redirect()->to('agenda')->with('success', 'Agendamento realizado com sucesso!');
            }
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    /**
     * AJAX: Retorna horários disponíveis
     */
    public function horariosDisponiveis()
    {
        $data = $this->request->getGet('data');
        if (!$data) return $this->response->setJSON([]);

        // Horários de funcionamento (Ex: 08:00 as 18:00)
        $horarios = [];
        for ($h = 8; $h < 18; $h++) {
            $horarios[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':00';
            $horarios[] = str_pad($h, 2, '0', STR_PAD_LEFT) . ':30';
        }

        // Buscar horários ocupados
        $agendamentoModel = new AgendamentoModel();
        $ocupados = $agendamentoModel->where("DATE(data_hora)", $data)
                                     ->where('status !=', 'Cancelado')
                                     ->findColumn('data_hora') ?? [];

        // Filtra horários (Simples: se já existe agendamento naquele horário exato)
        // Melhoria futura: considerar duração do serviço
        $horariosOcupados = array_map(function($dt) {
            return date('H:i', strtotime($dt));
        }, $ocupados);

        $disponiveis = array_diff($horarios, $horariosOcupados);

        return $this->response->setJSON(array_values($disponiveis));
    }

    public function concluir($id)
    {
        $agendamentoModel = new AgendamentoModel();
        $agendamentoModel->update($id, ['status' => 'Finalizado']);
        
        // TODO: Redirecionar para a Ficha de Atendimento (será implementada a seguir)
        return redirect()->back()->with('success', 'Agendamento concluído!');
    }

    public function cancelar($id)
    {
        $agendamentoModel = new AgendamentoModel();
        $agendamentoModel->update($id, ['status' => 'Cancelado']);
        return redirect()->back()->with('success', 'Agendamento cancelado.');
    }

    public function ficha($id)
    {
        $agendamentoModel = new AgendamentoModel();
        $fichaModel = new \App\Models\FichaModel();
        $obsVisualModel = new \App\Models\ObservacaoVisualModel();
        $servicoModel = new \App\Models\ServicoModel();

        // Dados do agendamento
        $agendamento = $agendamentoModel->select('agendamentos.*, pets.nome as pet_nome, pets.especie, pets.raca, pets.sexo, tutores.nome as tutor_nome, tutores.telefone as tutor_telefone')
            ->join('pets', 'pets.id = agendamentos.pet_id')
            ->join('tutores', 'tutores.id = pets.tutor_id')
            ->find($id);

        if (!$agendamento) {
            return redirect()->to('agenda')->with('error', 'Agendamento não encontrado.');
        }

        // Ficha existente?
        $ficha = $fichaModel->where('agendamento_id', $id)->first();
        
        // Dados auxiliares
        $data = [
            'agendamento' => $agendamento,
            'ficha' => $ficha,
            'obs_visuais' => $obsVisualModel->findAll(),
            'servicos' => $servicoModel->where('id !=', 99)->orderBy('nome', 'ASC')->findAll(),
            // Se tiver ficha, buscar relacionamentos (fazer queries diretas por simplicidade ou criar models pivot)
            'obs_marcadas' => [],
            'servicos_realizados' => []
        ];

        if ($ficha) {
            $db = \Config\Database::connect();
            $data['obs_marcadas'] = $db->table('ficha_observacoes')->where('ficha_id', $ficha['id'])->get()->getResultArray();
            $data['servicos_realizados'] = $db->table('ficha_servicos_realizados')->where('ficha_id', $ficha['id'])->get()->getResultArray();
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

            // Verifica se já existe ficha para atualizar ou insert
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
