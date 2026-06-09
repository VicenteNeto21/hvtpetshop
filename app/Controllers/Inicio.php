<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Inicio extends BaseController
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

        // Agenda do Dia - Chamando método centralizado no Model que já faz o agrupamento
        $agenda = $agendamentoModel->getAgendamentosDoDia($dataSelecionada);

        // Se filtrar por status, aplicamos manualmente (ou poderíamos passar pro model)
        if ($status) {
            $agenda = array_values(array_filter($agenda, function($ag) use ($status) {
                return $ag['status'] === $status;
            }));
        }

        // Estatísticas Rápidas (Cards do Dashboard Principal)
        $hoje = date('Y-m-d');
        $agendamentosHojeGrouped = $agendamentoModel->getAgendamentosDoDia($hoje);
        
        // Pendentes (Considerando agrupamento: pet + data_hora único)
        $pendentesCount = $agendamentoModel->select('id')
                                           ->where('status', 'Pendente')
                                           ->groupBy('pet_id, data_hora')
                                           ->countAllResults();

        // Dados de usuários pendentes (apenas para Admin)
        $usuarioModel = new \App\Models\UsuarioModel();
        $usuarioLogado = $usuarioModel->find(session()->get('usuario_id'));
        $isAdmin = ($usuarioLogado['tipo'] === 'admin');
        $usuariosPendentesCount = 0;
        
        if ($isAdmin) {
            $usuariosPendentesCount = $usuarioModel->where('status', 'pendente')->countAllResults();
        }

        $stats = [
            'agendamentos_hoje' => count($agendamentosHojeGrouped),
            'pendentes' => $pendentesCount,
            'total_pets' => $petModel->countAllResults(),
            'total_tutores' => $tutorModel->countAllResults(),
            'usuarios_pendentes' => $usuariosPendentesCount
        ];

        // Aniversariantes (Logica simples por enquanto)
        $aniversariantes = $petModel->select('pets.nome as pet_nome, tutores.nome as tutor_nome')
                                    ->join('tutores', 'tutores.id = pets.tutor_id')
                                    ->where("MONTH(pets.nascimento) = MONTH('$dataSelecionada')")
                                    ->where("DAY(pets.nascimento) = DAY('$dataSelecionada')")
                                    ->findAll();

        // Próximos Agendamentos (Agrupados - Próximos 5)
        $proximosAgendamentos = $agendamentoModel->select('MIN(agendamentos.id) as id, pets.nome as pet_nome, GROUP_CONCAT(servicos.nome SEPARATOR ", ") as servico_nome, tutores.nome as tutor_nome, agendamentos.data_hora, agendamentos.status')
            ->join('pets', 'pets.id = agendamentos.pet_id')
            ->join('tutores', 'tutores.id = pets.tutor_id')
            ->join('servicos', 'servicos.id = agendamentos.servico_id')
            ->where('agendamentos.status', 'Pendente')
            ->where('agendamentos.data_hora >=', date('Y-m-d H:i:s'))
            ->groupBy('agendamentos.pet_id, agendamentos.data_hora')
            ->orderBy('agendamentos.data_hora', 'ASC')
            ->limit(5)
            ->findAll();

        // Vacinas Vencendo (Próximos 15 dias ou já vencidas)
        $vacinaModel = new \App\Models\VacinaModel();
        $vacinasVencendo = $vacinaModel->getVacinasVencendo(15);

        return view('inicio/index', [
            'stats' => $stats,
            'agenda' => $agenda,
            'aniversariantes' => $aniversariantes,
            'vacinasVencendo' => $vacinasVencendo,
            'proximosAgendamentos' => $proximosAgendamentos,
            'dataSelecionada' => $dataSelecionada,
            'statusSelecionado' => $status,
            'isAdmin' => $isAdmin
        ]);
    }

    /**
     * AJAX endpoint - Retorna dados da agenda em JSON
     */
    public function getAgendaData()
    {
        if (!session()->get('usuario_id')) {
            return $this->response->setJSON(['error' => 'Não autorizado'])->setStatusCode(401);
        }

        $agendamentoModel = new \App\Models\AgendamentoModel();
        $petModel = new \App\Models\PetModel();

        $dataSelecionada = $this->request->getGet('data') ?? date('Y-m-d');

        // Agenda do Dia (Garantindo agrupamento)
        $agenda = $agendamentoModel->getAgendamentosDoDia($dataSelecionada);

        // Aniversariantes
        $aniversariantes = $petModel->select('pets.nome as pet_nome, tutores.nome as tutor_nome')
            ->join('tutores', 'tutores.id = pets.tutor_id')
            ->where("MONTH(pets.nascimento) = MONTH('$dataSelecionada')")
            ->where("DAY(pets.nascimento) = DAY('$dataSelecionada')")
            ->findAll();

        // Formatar data para exibição
        $diasSemana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
        $meses = ['', 'Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        
        $dataFormatada = [
            'dia' => date('d', strtotime($dataSelecionada)),
            'mes' => $meses[(int)date('m', strtotime($dataSelecionada))],
            'diaSemana' => $diasSemana[(int)date('w', strtotime($dataSelecionada))],
            'completa' => date('d/m/Y', strtotime($dataSelecionada))
        ];

        return $this->response->setJSON([
            'agenda' => $agenda,
            'aniversariantes' => $aniversariantes,
            'dataFormatada' => $dataFormatada,
            'dataSelecionada' => $dataSelecionada
        ]);
    }
    
    public function sobre()
    {
        return view('sobre');
    }
}
