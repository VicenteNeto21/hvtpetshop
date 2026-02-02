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

        // Estatísticas
        $stats = [
            'total_pets' => $petModel->countAll(),
            'total_tutores' => $tutorModel->countAll(),
            'agendamentos_hoje' => $agendamentoModel->where('DATE(data_hora)', date('Y-m-d'))->where('status !=', 'Cancelado')->countAllResults(),
            'pendentes' => $agendamentoModel->where('status', 'Pendente')->countAllResults()
        ];

        // Agenda do Dia
        $agenda = $agendamentoModel->getAgendamentosDoDia($dataSelecionada);

        // Aniversariantes do Mês (Simplificado para o dia selecionado por enquanto, conforme legacy)
        $aniversariantes = $petModel->select('pets.nome as pet_nome, tutores.nome as tutor_nome')
                                    ->join('tutores', 'tutores.id = pets.tutor_id')
                                    ->where("MONTH(pets.nascimento) = MONTH('$dataSelecionada')")
                                    ->where("DAY(pets.nascimento) = DAY('$dataSelecionada')")
                                    ->findAll();

        return view('dashboard/index', [
            'stats' => $stats,
            'agenda' => $agenda,
            'aniversariantes' => $aniversariantes,
            'dataSelecionada' => $dataSelecionada
        ]);
    }
}
