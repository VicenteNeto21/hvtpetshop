<?php
include "../config/config.php";
session_start();

// Protege a página
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Obtém os parâmetros
$dataInicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : date('Y-m-01');
$dataFim = isset($_GET['data_fim']) ? $_GET['data_fim'] : date('Y-m-d');
$acao = isset($_GET['acao']) ? $_GET['acao'] : 'visualizar';

// Valida as datas
if (!DateTime::createFromFormat("Y-m-d", $dataInicio) || !DateTime::createFromFormat("Y-m-d", $dataFim)) {
    die("Datas inválidas");
}

// Busca o nome do usuário logado
$stmtUsuario = $pdo->prepare("SELECT nome FROM usuarios WHERE id = ?");
$stmtUsuario->execute([$_SESSION['usuario_id']]);
$nomeUsuario = $stmtUsuario->fetchColumn() ?: 'Usuário';

// Formata as datas para exibição
$dataInicioFormatada = date('d/m/Y', strtotime($dataInicio));
$dataFimFormatada = date('d/m/Y', strtotime($dataFim));

// Dias da semana em português
$diasSemana = [
    'Sunday' => 'Domingo',
    'Monday' => 'Segunda-feira',
    'Tuesday' => 'Terça-feira',
    'Wednesday' => 'Quarta-feira',
    'Thursday' => 'Quinta-feira',
    'Friday' => 'Sexta-feira',
    'Saturday' => 'Sábado'
];

// --- CONSULTAS AOS DADOS DO PERÍODO ---

// Atendimentos finalizados
$stmtAtendimentos = $pdo->prepare("
    SELECT COUNT(DISTINCT pet_id, data_hora) 
    FROM agendamentos 
    WHERE status = 'Finalizado' 
    AND DATE(data_hora) BETWEEN :inicio AND :fim
");
$stmtAtendimentos->execute([':inicio' => $dataInicio, ':fim' => $dataFim]);
$totalAtendimentos = $stmtAtendimentos->fetchColumn();

// Cancelamentos
$stmtCancelados = $pdo->prepare("
    SELECT COUNT(DISTINCT pet_id, data_hora) 
    FROM agendamentos 
    WHERE status = 'Cancelado' 
    AND DATE(data_hora) BETWEEN :inicio AND :fim
");
$stmtCancelados->execute([':inicio' => $dataInicio, ':fim' => $dataFim]);
$totalCancelados = $stmtCancelados->fetchColumn();

// Total de pets
$totalPets = $pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();

// Total de tutores
$totalTutores = $pdo->query("SELECT COUNT(*) FROM tutores")->fetchColumn();

// Top Tutores do período
$stmtTopTutores = $pdo->prepare("
    SELECT t.nome, COUNT(DISTINCT a.data_hora) as total_agendamentos
    FROM agendamentos a
    JOIN pets p ON a.pet_id = p.id
    JOIN tutores t ON p.tutor_id = t.id
    WHERE a.status = 'Finalizado'
    AND DATE(a.data_hora) BETWEEN :inicio AND :fim
    GROUP BY t.id, t.nome
    ORDER BY total_agendamentos DESC
    LIMIT 10
");
$stmtTopTutores->execute([':inicio' => $dataInicio, ':fim' => $dataFim]);
$topTutores = $stmtTopTutores->fetchAll(PDO::FETCH_ASSOC);

// Serviços mais realizados (sem receita)
$stmtServicos = $pdo->prepare("
    SELECT s.nome, COUNT(a.id) as total
    FROM agendamentos a
    JOIN servicos s ON a.servico_id = s.id
    WHERE a.status = 'Finalizado'
    AND DATE(a.data_hora) BETWEEN :inicio AND :fim
    GROUP BY s.id, s.nome
    ORDER BY total DESC
    LIMIT 10
");
$stmtServicos->execute([':inicio' => $dataInicio, ':fim' => $dataFim]);
$servicosMais = $stmtServicos->fetchAll(PDO::FETCH_ASSOC);

// Distribuição por Status
$stmtStatus = $pdo->prepare("
    SELECT status, COUNT(*) as total
    FROM agendamentos
    WHERE DATE(data_hora) BETWEEN :inicio AND :fim
    GROUP BY status
    ORDER BY total DESC
");
$stmtStatus->execute([':inicio' => $dataInicio, ':fim' => $dataFim]);
$statusDistribuicao = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);

// Atendimentos por dia
$stmtDiario = $pdo->prepare("
    SELECT DATE(a.data_hora) as dia, COUNT(DISTINCT a.pet_id) as total
    FROM agendamentos a
    WHERE a.status = 'Finalizado' 
    AND DATE(a.data_hora) BETWEEN :inicio AND :fim
    GROUP BY dia
    ORDER BY dia ASC
");
$stmtDiario->execute([':inicio' => $dataInicio, ':fim' => $dataFim]);
$dadosDiarios = $stmtDiario->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório - <?= $dataInicioFormatada ?> a <?= $dataFimFormatada ?></title>
    <link rel="icon" type="image/x-icon" href="../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #94a3b8;
            color: #1e293b;
            padding: 20px;
            line-height: 1.4;
        }
        .container {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            padding: 15mm 20mm;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.3);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e40af;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .header h1 {
            font-size: 22px;
            color: #1e40af;
            margin-bottom: 6px;
        }
        .header .periodo {
            font-size: 14px;
            color: #475569;
            font-weight: 600;
        }
        .header .meta-info {
            font-size: 11px;
            color: #64748b;
            margin-top: 8px;
            display: flex;
            justify-content: center;
            gap: 20px;
        }
        .kpis {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            margin-bottom: 20px;
        }
        .kpi {
            background: #f8fafc;
            padding: 12px 10px;
            border-radius: 8px;
            text-align: center;
            border-left: 3px solid #3b82f6;
        }
        .kpi.violet { border-left-color: #8b5cf6; }
        .kpi.amber { border-left-color: #f59e0b; }
        .kpi.red { border-left-color: #ef4444; }
        .kpi-value {
            font-size: 24px;
            font-weight: bold;
            color: #1e293b;
        }
        .kpi-label {
            font-size: 10px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .section {
            margin-bottom: 16px;
        }
        .section h2 {
            font-size: 13px;
            color: #1e40af;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            gap: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .section h2 i {
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }
        th, td {
            padding: 8px 6px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        th {
            background: #f1f5f9;
            font-weight: 600;
            color: #475569;
            text-transform: uppercase;
            font-size: 9px;
            letter-spacing: 0.3px;
        }
        tr:nth-child(even) {
            background: #f8fafc;
        }
        .no-data {
            text-align: center;
            padding: 20px;
            color: #94a3b8;
            font-style: italic;
            font-size: 11px;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 9px;
            font-weight: 600;
        }
        .status-finalizado { background: #dcfce7; color: #166534; }
        .status-pendente { background: #fef3c7; color: #92400e; }
        .status-cancelado { background: #fee2e2; color: #991b1b; }
        .status-em-atendimento { background: #dbeafe; color: #1e40af; }
        .status-agendado { background: #e0e7ff; color: #3730a3; }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding-top: 12px;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 10px;
        }
        .print-buttons {
            text-align: center;
            margin-bottom: 15px;
        }
        .print-buttons button {
            background: #3b82f6;
            color: white;
            border: none;
            padding: 10px 24px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin: 0 5px;
            transition: background 0.2s;
        }
        .print-buttons button:hover {
            background: #2563eb;
        }
        .print-buttons button.secondary {
            background: #64748b;
        }
        .print-buttons button.secondary:hover {
            background: #475569;
        }
        .ranking-number {
            display: inline-block;
            width: 20px;
            height: 20px;
            background: #e2e8f0;
            border-radius: 50%;
            text-align: center;
            line-height: 20px;
            font-weight: bold;
            font-size: 10px;
            color: #475569;
        }
        .ranking-number.top3 {
            background: #fcd34d;
            color: #78350f;
        }
        @media print {
            body {
                background: white;
                padding: 0;
            }
            .container {
                box-shadow: none;
                padding: 10mm 15mm;
                width: 100%;
            }
            .print-buttons {
                display: none;
            }
            @page {
                size: A4;
                margin: 0;
            }
        }
    </style>
</head>
<body>
    <div class="print-buttons">
        <button onclick="window.print()"><i class="fa-solid fa-print"></i> Imprimir</button>
        <button class="secondary" onclick="window.close()"><i class="fa-solid fa-xmark"></i> Fechar</button>
    </div>

    <div class="container">
        <div class="header">
            <h1><i class="fa-solid fa-paw"></i> Relatório de Indicadores - CereniaPet</h1>
            <div class="periodo">
                Período: <?= $dataInicioFormatada ?> a <?= $dataFimFormatada ?>
            </div>
            <div class="meta-info">
                <span><i class="fa-solid fa-user"></i> Gerado por: <strong><?= htmlspecialchars($nomeUsuario) ?></strong></span>
                <span><i class="fa-solid fa-clock"></i> Em: <?= date('d/m/Y \à\s H:i') ?></span>
            </div>
        </div>

        <!-- KPIs -->
        <div class="kpis">
            <div class="kpi">
                <div class="kpi-value"><?= $totalAtendimentos ?></div>
                <div class="kpi-label">Atendimentos</div>
            </div>
            <div class="kpi violet">
                <div class="kpi-value"><?= $totalPets ?></div>
                <div class="kpi-label">Total Pets</div>
            </div>
            <div class="kpi amber">
                <div class="kpi-value"><?= $totalTutores ?></div>
                <div class="kpi-label">Total Tutores</div>
            </div>
            <div class="kpi red">
                <div class="kpi-value"><?= $totalCancelados ?></div>
                <div class="kpi-label">Cancelamentos</div>
            </div>
        </div>

        <!-- Distribuição por Status -->
        <div class="section">
            <h2><i class="fa-solid fa-chart-pie"></i> Status dos Agendamentos</h2>
            <?php if (empty($statusDistribuicao)): ?>
                <div class="no-data">Nenhum agendamento no período</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Status</th>
                            <th style="text-align: center;">Qtd</th>
                            <th style="text-align: right;">%</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalStatus = array_sum(array_column($statusDistribuicao, 'total'));
                        foreach ($statusDistribuicao as $status): 
                            $percentual = ($status['total'] / $totalStatus) * 100;
                            $statusClass = 'status-' . strtolower(str_replace(' ', '-', $status['status']));
                        ?>
                        <tr>
                            <td><span class="status-badge <?= $statusClass ?>"><?= htmlspecialchars($status['status']) ?></span></td>
                            <td style="text-align: center;"><?= $status['total'] ?></td>
                            <td style="text-align: right;"><?= number_format($percentual, 1, ',', '.') ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Top Clientes -->
        <div class="section">
            <h2><i class="fa-solid fa-trophy"></i> Top Clientes</h2>
            <?php if (empty($topTutores)): ?>
                <div class="no-data">Nenhum cliente atendido</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tutor</th>
                            <th style="text-align: right;">Visitas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($topTutores as $index => $tutor): ?>
                        <tr>
                            <td><span class="ranking-number <?= $index < 3 ? 'top3' : '' ?>"><?= $index + 1 ?></span></td>
                            <td><?= htmlspecialchars($tutor['nome']) ?></td>
                            <td style="text-align: right;"><?= $tutor['total_agendamentos'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Top Serviços -->
        <div class="section">
            <h2><i class="fa-solid fa-scissors"></i> Top Serviços</h2>
            <?php if (empty($servicosMais)): ?>
                <div class="no-data">Nenhum serviço realizado</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Serviço</th>
                            <th style="text-align: right;">Qtd</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicosMais as $index => $servico): ?>
                        <tr>
                            <td><span class="ranking-number <?= $index < 3 ? 'top3' : '' ?>"><?= $index + 1 ?></span></td>
                            <td><?= htmlspecialchars($servico['nome']) ?></td>
                            <td style="text-align: right;"><?= $servico['total'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Atendimentos por Dia -->
        <div class="section">
            <h2><i class="fa-solid fa-calendar-check"></i> Atendimentos por Dia</h2>
            <?php if (empty($dadosDiarios)): ?>
                <div class="no-data">Nenhum atendimento no período</div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Data</th>
                            <th>Dia</th>
                            <th style="text-align: right;">Atend.</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dadosDiarios as $dia): 
                            $dataObj = new DateTime($dia['dia']);
                            $diaSemanaEN = $dataObj->format('l');
                            $diaSemana = $diasSemana[$diaSemanaEN] ?? $diaSemanaEN;
                        ?>
                        <tr>
                            <td><?= $dataObj->format('d/m/Y') ?></td>
                            <td><?= $diaSemana ?></td>
                            <td style="text-align: right;"><?= $dia['total'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="footer">
            <p><strong>CereniaPet</strong> - Sistema de Gestão para Pet Shop</p>
        </div>
    </div>

    <?php if ($acao === 'imprimir'): ?>
    <script>
        window.onload = function() {
            window.print();
        };
    </script>
    <?php endif; ?>
</body>
</html>
