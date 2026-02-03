<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        if (!session()->get('usuario_id')) {
            return redirect()->to('/login');
        }

        $petModel = new \App\Models\PetModel();
        $tutorModel = new \App\Models\TutorModel();
        $agendamentoModel = new \App\Models\AgendamentoModel();

        $dataSelecionada = $this->request->getGet('data') ?? date('Y-m-d');
        $status = $this->request->getGet('status');

        // Agenda do Dia
        $query = $agendamentoModel->select('agendamentos.*, pets.nome as pet_nome, servicos.nome as servico_nome, tutores.nome as tutor_nome')
            ->join('pets', 'pets.id = agendamentos.pet_id')
            ->join('tutores', 'tutores.id = pets.tutor_id')
            ->join('servicos', 'servicos.id = agendamentos.servico_id');

        if ($status === 'Pendente') {
            $query->where('agendamentos.status', 'Pendente');
        } else {
             $query->where("DATE(data_hora)", $dataSelecionada);
             if ($status) {
                 $query->where('agendamentos.status', $status);
             }
        }
        
        $agenda = $query->orderBy('data_hora', 'ASC')->findAll();

        // Estatísticas Rápidas (Cards do Dashboard Principal)
        $stats = [
            'agendamentos_hoje' => $agendamentoModel->where("DATE(data_hora)", date('Y-m-d'))->where('status !=', 'Cancelado')->countAllResults(),
            'pendentes' => $agendamentoModel->where('status', 'Pendente')->countAllResults(),
            'total_pets' => $petModel->countAllResults(),
            'total_tutores' => $tutorModel->countAllResults()
        ];

        // Aniversariantes (Logica simples por enquanto)
        $aniversariantes = $petModel->select('pets.nome as pet_nome, tutores.nome as tutor_nome')
                                    ->join('tutores', 'tutores.id = pets.tutor_id')
                                    ->where("MONTH(pets.nascimento) = MONTH('$dataSelecionada')")
                                    ->where("DAY(pets.nascimento) = DAY('$dataSelecionada')")
                                    ->findAll();

        // Próximos Agendamentos (próximos 5 após agora)
        $proximosAgendamentos = $agendamentoModel->select('agendamentos.*, pets.nome as pet_nome, servicos.nome as servico_nome, tutores.nome as tutor_nome')
            ->join('pets', 'pets.id = agendamentos.pet_id')
            ->join('tutores', 'tutores.id = pets.tutor_id')
            ->join('servicos', 'servicos.id = agendamentos.servico_id')
            ->where('agendamentos.status', 'Pendente')
            ->where('agendamentos.data_hora >=', date('Y-m-d H:i:s'))
            ->orderBy('agendamentos.data_hora', 'ASC')
            ->limit(5)
            ->findAll();

        return view('dashboard/index', [
            'stats' => $stats,
            'agenda' => $agenda,
            'aniversariantes' => $aniversariantes,
            'proximosAgendamentos' => $proximosAgendamentos,
            'dataSelecionada' => $dataSelecionada,
            'statusSelecionado' => $status
        ]);
    }


}
