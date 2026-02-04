<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Admin extends BaseController
{
    public function index()
    {
        if (!session()->get('usuario_id')) {
            return redirect()->to('/login');
        }

        // Verificar se é admin
        $userTipo = session()->get('usuario_tipo');
        if ($userTipo !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Acesso negado.');
        }

        $db = \Config\Database::connect();
        $agendamentoModel = new \App\Models\AgendamentoModel();
        $petModel = new \App\Models\PetModel();
        $tutorModel = new \App\Models\TutorModel();

        // 1. Filtro de Data (Padrão: Mês atual)
        $dataInicial = $this->request->getGet('data_inicial') ?? date('Y-m-01');
        $dataFinal = $this->request->getGet('data_final') ?? date('Y-m-t');

        // 2. Cards Principais (No Período - Agrupados)
        $finalizados = $this->countGroupedAppointments($agendamentoModel, 'Finalizado', $dataInicial, $dataFinal);
        $cancelados  = $this->countGroupedAppointments($agendamentoModel, 'Cancelado', $dataInicial, $dataFinal);
        $pendentes   = $this->countGroupedAppointments($agendamentoModel, 'Pendente', $dataInicial, $dataFinal);

        $totalPets = $petModel->countAllResults();
        $totalTutores = $tutorModel->countAllResults();

        // 3. Faturamento Estimado
        $faturamentoData = $agendamentoModel->selectSum('servicos.preco', 'total')
            ->join('servicos', 'servicos.id = agendamentos.servico_id')
            ->where('agendamentos.status', 'Finalizado')
            ->where("DATE(agendamentos.data_hora) >=", $dataInicial)
            ->where("DATE(agendamentos.data_hora) <=", $dataFinal)
            ->first();
        $faturamentoEstimado = ($faturamentoData && isset($faturamentoData['total'])) ? $faturamentoData['total'] : 0;

        // 4. Gráfico: Atendimentos por Dia (Agrupados: conta 1 por pet/hora)
        $sqlPorDia = "SELECT data, COUNT(*) as total FROM (
                        SELECT DATE(data_hora) as data, pet_id, data_hora 
                        FROM agendamentos 
                        WHERE status != 'Cancelado' 
                        AND DATE(data_hora) BETWEEN ? AND ? 
                        GROUP BY DATE(data_hora), pet_id, data_hora
                      ) as grouped_data GROUP BY data ORDER BY data ASC";
        $porDiaResult = $db->query($sqlPorDia, [$dataInicial, $dataFinal])->getResultArray();
        
        $graficoLabels = [];
        $graficoData = [];
        foreach ($porDiaResult as $row) {
            $graficoLabels[] = date('d/m', strtotime($row['data']));
            $graficoData[] = $row['total'];
        }

        // 5. Gráfico: Status (Agrupados)
        $sqlStatus = "SELECT status, COUNT(*) as total FROM (
                        SELECT status, pet_id, data_hora
                        FROM agendamentos 
                        WHERE DATE(data_hora) BETWEEN ? AND ? 
                        GROUP BY status, pet_id, data_hora
                      ) as grouped_status GROUP BY status";
        $statusResult = $db->query($sqlStatus, [$dataInicial, $dataFinal])->getResultArray();
        
        $statusLabels = [];
        $statusData = [];
        foreach ($statusResult as $row) {
            $statusLabels[] = $row['status'];
            $statusData[] = $row['total'];
        }

        // 6. Top 5 Tutores (Agendamentos agrupados)
        $topTutores = $db->query("
            SELECT t.nome, COUNT(DISTINCT a.pet_id, a.data_hora) as total_atendimentos, SUM(s.preco) as total_gasto
            FROM agendamentos a
            JOIN pets p ON p.id = a.pet_id
            JOIN tutores t ON t.id = p.tutor_id
            JOIN servicos s ON s.id = a.servico_id
            WHERE a.status = 'Finalizado'
            AND DATE(a.data_hora) BETWEEN ? AND ?
            GROUP BY t.id
            ORDER BY total_atendimentos DESC
        ", [$dataInicial, $dataFinal])->getResultArray();

        // 7. Top 5 Serviços (Com Faturamento)
        $topServicos = $db->query("
            SELECT s.nome, COUNT(a.id) as total_realizados, SUM(s.preco) as faturamento_servico
            FROM agendamentos a
            JOIN servicos s ON s.id = a.servico_id
            WHERE a.status = 'Finalizado'
            AND DATE(a.data_hora) BETWEEN ? AND ?
            GROUP BY s.id
            ORDER BY total_realizados DESC
            LIMIT 5
        ", [$dataInicial, $dataFinal])->getResultArray();

        // 8. Taxa de Conversão (Finalizados / Total Tentados)
        $totalTentados = $finalizados + $cancelados;
        $taxaConversao = $totalTentados > 0 ? ($finalizados / $totalTentados) * 100 : 0;

        $data = [
            'dataInicial' => $dataInicial,
            'dataFinal' => $dataFinal,
            'stats' => [
                'finalizados' => $finalizados,
                'cancelados' => $cancelados,
                'pendentes' => $pendentes,
                'total_pets' => $totalPets,
                'total_tutores' => $totalTutores,
                'faturamento' => $faturamentoEstimado,
                'conversao' => $taxaConversao
            ],
            'charts' => [
                'timeline' => ['labels' => $graficoLabels, 'data' => $graficoData],
                'status' => ['labels' => $statusLabels, 'data' => $statusData]
            ],
            'top_tutores' => $topTutores,
            'top_servicos' => $topServicos
        ];

        return view('admin/index', $data);
    }

    public function relatorio()
    {
        try {
            if (!session()->get('usuario_id')) {
                return redirect()->to('/login');
            }

        // Verificar se é admin
        $userTipo = session()->get('usuario_tipo');
        if ($userTipo !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Acesso negado.');
        }

        $db = \Config\Database::connect();
        $agendamentoModel = new \App\Models\AgendamentoModel();
        $tutorModel = new \App\Models\TutorModel();
        $petModel = new \App\Models\PetModel();

        // Filtro de Data
        $dataInicial = $this->request->getGet('data_inicial') ?? date('Y-m-01');
        $dataFinal = $this->request->getGet('data_final') ?? date('Y-m-t');

        // =============================================
        // 1. DISTRIBUIÇÃO POR STATUS
        // =============================================
        $finalizados = $this->countGroupedAppointments($agendamentoModel, 'Finalizado', $dataInicial, $dataFinal);
        $cancelados  = $this->countGroupedAppointments($agendamentoModel, 'Cancelado', $dataInicial, $dataFinal);
        $pendentes   = $this->countGroupedAppointments($agendamentoModel, 'Pendente', $dataInicial, $dataFinal);

        $totalAtendimentos = $finalizados + $cancelados + $pendentes;

        // Faturamento
        $faturamentoData = $agendamentoModel->selectSum('servicos.preco', 'total')
            ->join('servicos', 'servicos.id = agendamentos.servico_id')
            ->where('agendamentos.status', 'Finalizado')
            ->where("DATE(agendamentos.data_hora) >=", $dataInicial)
            ->where("DATE(agendamentos.data_hora) <=", $dataFinal)
            ->first();
        $faturamento = ($faturamentoData && isset($faturamentoData['total'])) ? $faturamentoData['total'] : 0;

        // Taxa de Conversão
        $totalTentados = $finalizados + $cancelados;
        $conversao = $totalTentados > 0 ? ($finalizados / $totalTentados) * 100 : 0;

        // =============================================
        // 2. TAXA DE RETORNO DE CLIENTES
        // =============================================
        // Clientes que vieram mais de uma vez no período
        $retornoRow = $db->query("
            SELECT COUNT(*) as total FROM (
                SELECT p.tutor_id, COUNT(a.id) as visitas
                FROM agendamentos a
                JOIN pets p ON p.id = a.pet_id
                WHERE a.status = 'Finalizado'
                AND DATE(a.data_hora) BETWEEN ? AND ?
                GROUP BY p.tutor_id
                HAVING visitas > 1
            ) as recorrentes
        ", [$dataInicial, $dataFinal])->getRow();
        $clientesRetorno = $retornoRow ? $retornoRow->total : 0;

        // Total de clientes únicos no período
        $unicosRow = $db->query("
            SELECT COUNT(DISTINCT p.tutor_id) as total
            FROM agendamentos a
            JOIN pets p ON p.id = a.pet_id
            WHERE a.status = 'Finalizado'
            AND DATE(a.data_hora) BETWEEN ? AND ?
        ", [$dataInicial, $dataFinal])->getRow();
        $totalClientesUnicos = $unicosRow ? $unicosRow->total : 0;

        $taxaRetorno = $totalClientesUnicos > 0 ? ($clientesRetorno / $totalClientesUnicos) * 100 : 0;

        // =============================================
        // 3. NOVOS CADASTROS NO PERÍODO
        // =============================================
        try {
            $novosTutores = $tutorModel->where("DATE(created_at) >=", $dataInicial)
                ->where("DATE(created_at) <=", $dataFinal)
                ->countAllResults();

            $novosPets = $petModel->where("DATE(created_at) >=", $dataInicial)
                ->where("DATE(created_at) <=", $dataFinal)
                ->countAllResults();
        } catch (\Exception $e) {
            // Caso a coluna created_at não exista no banco (InfinityFree), não quebra o sistema
            $novosTutores = 0;
            $novosPets = 0;
        }

        // =============================================
        // 4. COMPARATIVO COM PERÍODO ANTERIOR
        // =============================================
        // Calcular período anterior de mesmo tamanho
        $diasPeriodo = (strtotime($dataFinal) - strtotime($dataInicial)) / 86400;
        $dataInicialAnterior = date('Y-m-d', strtotime($dataInicial . " -" . ($diasPeriodo + 1) . " days"));
        $dataFinalAnterior = date('Y-m-d', strtotime($dataInicial . " -1 day"));

        // Atendimentos período anterior (Agrupados)
        $finalizadosAnterior = $this->countGroupedAppointments($agendamentoModel, 'Finalizado', $dataInicialAnterior, $dataFinalAnterior);

        // Faturamento período anterior
        $faturamentoAnteriorData = $agendamentoModel->selectSum('servicos.preco', 'total')
            ->join('servicos', 'servicos.id = agendamentos.servico_id')
            ->where('agendamentos.status', 'Finalizado')
            ->where("DATE(agendamentos.data_hora) >=", $dataInicialAnterior)
            ->where("DATE(agendamentos.data_hora) <=", $dataFinalAnterior)
            ->first();
        $faturamentoAnterior = ($faturamentoAnteriorData && isset($faturamentoAnteriorData['total'])) ? $faturamentoAnteriorData['total'] : 0;

        // Calcular variações percentuais
        $variacaoAtendimentos = $finalizadosAnterior > 0 
            ? (($finalizados - $finalizadosAnterior) / $finalizadosAnterior) * 100 
            : ($finalizados > 0 ? 100 : 0);

        $variacaoFaturamento = $faturamentoAnterior > 0 
            ? (($faturamento - $faturamentoAnterior) / $faturamentoAnterior) * 100 
            : ($faturamento > 0 ? 100 : 0);

        // =============================================
        // TOP TUTORES E SERVIÇOS
        // =============================================
        $topTutores = $db->query("
            SELECT t.nome, COUNT(DISTINCT a.pet_id, a.data_hora) as total_atendimentos
            FROM agendamentos a
            JOIN pets p ON p.id = a.pet_id
            JOIN tutores t ON t.id = p.tutor_id
            WHERE a.status = 'Finalizado'
            AND DATE(a.data_hora) BETWEEN ? AND ?
            GROUP BY t.id
            ORDER BY total_atendimentos DESC
        ", [$dataInicial, $dataFinal])->getResultArray();

        $topServicos = $db->query("
            SELECT s.nome, COUNT(a.id) as total_realizados
            FROM agendamentos a
            JOIN servicos s ON s.id = a.servico_id
            WHERE a.status = 'Finalizado'
            AND DATE(a.data_hora) BETWEEN ? AND ?
            GROUP BY s.id
            ORDER BY total_realizados DESC
            LIMIT 5
        ", [$dataInicial, $dataFinal])->getResultArray();

            return view('admin/relatorio', [
                'dataInicial' => $dataInicial,
                'dataFinal' => $dataFinal,
                'stats' => [
                    'finalizados' => $finalizados,
                    'cancelados' => $cancelados,
                    'pendentes' => $pendentes,
                    'total' => $totalAtendimentos,
                    'faturamento' => $faturamento,
                    'conversao' => $conversao
                ],
                'retorno' => [
                    'clientes_recorrentes' => $clientesRetorno,
                    'clientes_unicos' => $totalClientesUnicos,
                    'taxa' => $taxaRetorno
                ],
                'novos' => [
                    'tutores' => $novosTutores,
                    'pets' => $novosPets
                ],
                'comparativo' => [
                    'periodo_anterior' => $dataInicialAnterior . ' a ' . $dataFinalAnterior,
                    'atendimentos_anterior' => $finalizadosAnterior,
                    'faturamento_anterior' => $faturamentoAnterior,
                    'variacao_atendimentos' => $variacaoAtendimentos,
                    'variacao_faturamento' => $variacaoFaturamento
                ],
                'top_tutores' => $topTutores,
                'top_servicos' => $topServicos
            ]);
        } catch (\Throwable $e) {
            // Log do erro silencioso para o desenvolvedor (opcional)
            log_message('error', '[Relatório] Falha técnica: ' . $e->getMessage());

            return redirect()->to('/admin')->with('error', 'Não foi possível gerar o relatório completo neste período devido a uma limitação técnica no servidor. Tente um período menor ou contate o suporte.');
        }
    }

    /**
     * Função auxiliar para contar agendamentos agrupados de forma robusta.
     * Necessária pois countAllResults() com groupBy() ou subqueries complexas podem falhar 
     * em ambientes shared hosting limitados (como InfinityFree).
     */
    private function countGroupedAppointments($model, $status, $inicio, $fim)
    {
        $db = \Config\Database::connect();
        
        // Usamos COUNT(DISTINCT ...) para contar agendamentos únicos (mesmo pet no mesmo horário)
        // Isso evita subqueries complexas que consomem muita memória e podem falhar.
        $sql = "SELECT COUNT(DISTINCT pet_id, data_hora) as total 
                FROM agendamentos 
                WHERE status = ? 
                AND DATE(data_hora) BETWEEN ? AND ?";
        
        $row = $db->query($sql, [$status, $inicio, $fim])->getRow();
        return $row ? (int)$row->total : 0;
    }
}
