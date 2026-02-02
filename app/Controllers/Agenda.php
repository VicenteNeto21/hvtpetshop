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
            ->join('servicos', 'servicos.id = agendamentos.servico_id')
            ->where("DATE(data_hora)", $dataSelecionada);

        if ($status) {
            $query->where('agendamentos.status', $status);
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
}
