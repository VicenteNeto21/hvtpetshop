<?php
include "../config/config.php";
session_start(); // Inicia a sessão

// Protege a página
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}
// --- CONSULTAS AOS DADOS ---

// Filtro de período - suporta dias pré-definidos ou intervalo personalizado
$dataInicio = null;
$dataFim = null;
$dias = 15; // Padrão
$filtroTipo = 'dias'; // 'dias' ou 'personalizado'

// Verifica se há filtro personalizado
if (isset($_GET['data_inicio']) && isset($_GET['data_fim'])) {
    $dataInicio = $_GET['data_inicio'];
    $dataFim = $_GET['data_fim'];
    
    // Valida as datas
    if (DateTime::createFromFormat("Y-m-d", $dataInicio) && DateTime::createFromFormat("Y-m-d", $dataFim)) {
        $filtroTipo = 'personalizado';
    } else {
        // Se as datas forem inválidas, volta ao padrão
        $dataInicio = null;
        $dataFim = null;
    }
}

// Se não houver filtro personalizado, usa o filtro de dias
if ($filtroTipo === 'dias') {
    $dias = isset($_GET['dias']) ? (int) $_GET['dias'] : 15;
    if (!in_array($dias, [7, 15, 30, 90])) {
        $dias = 15; // Garante que o valor seja um dos permitidos
    }
}

// --- Estratégia de Cache Simples para Indicadores ---
$cacheFile = '../cache/indicadores_kpi.json';
$cacheTime = 600; // 10 minutos em segundos

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTime) {
    // Carrega do cache
    $kpis = json_decode(file_get_contents($cacheFile), true);
    $totalPets = $kpis['totalPets'];
    $totalTutores = $kpis['totalTutores'];
    $totalAtendimentos = $kpis['totalAtendimentos'];
    $totalEmAtendimento = $kpis['totalEmAtendimento'];
    $receitaTotal = $kpis['receitaTotal'];
} else {
    // Calcula e salva no cache
    $totalPets = $pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();
    $totalTutores = $pdo->query("SELECT COUNT(*) FROM tutores")->fetchColumn();
    $totalAtendimentos = $pdo->query("SELECT COUNT(DISTINCT pet_id, data_hora) FROM agendamentos WHERE status = 'Finalizado'")->fetchColumn();
    $totalEmAtendimento = $pdo->query("SELECT COUNT(DISTINCT pet_id, data_hora) FROM agendamentos WHERE status = 'Em Atendimento'")->fetchColumn();
    $receitaTotal = $pdo->query("SELECT SUM(s.preco) FROM agendamentos a JOIN servicos s ON a.servico_id = s.id WHERE a.status = 'Finalizado'")->fetchColumn() ?? 0;

    $kpis = [
        'totalPets' => $totalPets,
        'totalTutores' => $totalTutores,
        'totalAtendimentos' => $totalAtendimentos,
        'totalEmAtendimento' => $totalEmAtendimento,
        'receitaTotal' => $receitaTotal,
    ];

    // Cria o diretório de cache se não existir
    if (!is_dir('../cache')) {
        mkdir('../cache', 0755, true);
    }
    file_put_contents($cacheFile, json_encode($kpis));
}

// KPI adicional: Total de Agendados
$totalAgendados = $pdo->query("SELECT COUNT(*) FROM agendamentos WHERE status = 'Agendado'")->fetchColumn();

// Top Tutores que mais vieram ao petshop (apenas finalizados, agrupados por data/hora)
$topTutores = $pdo->query("
    SELECT t.nome, COUNT(DISTINCT a.data_hora) as total_agendamentos
    FROM agendamentos a
    JOIN pets p ON a.pet_id = p.id
    JOIN tutores t ON p.tutor_id = t.id
    WHERE a.status = 'Finalizado'
    GROUP BY t.id, t.nome
    ORDER BY total_agendamentos DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Serviços mais realizados com receita
$servicosMais = $pdo->query("
    SELECT s.nome, COUNT(a.id) as total, SUM(s.preco) as receita
    FROM agendamentos a
    JOIN servicos s ON a.servico_id = s.id
    WHERE a.status = 'Finalizado'
    GROUP BY s.id, s.nome
    ORDER BY total DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Distribuição por Status
$statusDistribuicao = $pdo->query("
    SELECT status, COUNT(*) as total
    FROM agendamentos
    GROUP BY status
")->fetchAll(PDO::FETCH_ASSOC);

// Atendimentos por dia (período dinâmico)
if ($filtroTipo === 'personalizado') {
    $queryDia = "
        SELECT
            DATE(a.data_hora) as dia,
            COUNT(DISTINCT a.pet_id) as total_atendimentos
        FROM agendamentos a
        WHERE a.status = 'Finalizado' 
            AND DATE(a.data_hora) BETWEEN :data_inicio AND :data_fim
        GROUP BY dia
        ORDER BY dia ASC
    ";
    $stmtDia = $pdo->prepare($queryDia);
    $stmtDia->bindValue(':data_inicio', $dataInicio, PDO::PARAM_STR);
    $stmtDia->bindValue(':data_fim', $dataFim, PDO::PARAM_STR);
} else {
    $queryDia = "
        SELECT
            DATE(a.data_hora) as dia,
            COUNT(DISTINCT a.pet_id) as total_atendimentos
        FROM agendamentos a
        WHERE a.status = 'Finalizado' AND a.data_hora >= CURDATE() - INTERVAL :dias DAY
        GROUP BY dia
        ORDER BY dia ASC
    ";
    $stmtDia = $pdo->prepare($queryDia);
    $stmtDia->bindValue(':dias', $dias, PDO::PARAM_INT);
}

$stmtDia->execute();
$dadosDiarios = $stmtDia->fetchAll(PDO::FETCH_ASSOC);

// Formata os dados para os gráficos
$labelsDiarios = [];
$atendimentosPorDia = [];
foreach ($dadosDiarios as $dia) {
    $dateObj = DateTime::createFromFormat("Y-m-d", $dia['dia']);
    $labelsDiarios[] = $dateObj ? $dateObj->format('d/m') : $dia['dia'];
    $atendimentosPorDia[] = $dia['total_atendimentos'];
}

// Define o prefixo do caminho para o navbar. Como estamos em uma subpasta, é '../'
$path_prefix = '../';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Indicadores - CereniaPet</title>
    <link rel="icon" type="image/x-icon" href="<?= $path_prefix ?>icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <?php include '../components/navbar.php'; ?>
    <?php include '../components/toast.php'; ?>

    <main class="flex-1 w-full p-4 md:p-6 lg:p-8">
        <!-- Cabeçalho da Página -->
        <div class="mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">Indicadores do Sistema</h1>
                    <p class="text-slate-500 mt-1">Visão geral e estatísticas de desempenho.</p>
                </div>
                <!-- Filtros de Período -->
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2">
                    <!-- Botões rápidos -->
                    <div class="flex items-center gap-2 bg-white p-1 rounded-lg shadow-sm border">
                        <a href="?dias=7"
                            class="px-3 py-1 text-sm font-semibold rounded-md <?= ($filtroTipo === 'dias' && $dias == 7) ? 'bg-blue-500 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">7
                            dias</a>
                        <a href="?dias=15"
                            class="px-3 py-1 text-sm font-semibold rounded-md <?= ($filtroTipo === 'dias' && $dias == 15) ? 'bg-blue-500 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">15
                            dias</a>
                        <a href="?dias=30"
                            class="px-3 py-1 text-sm font-semibold rounded-md <?= ($filtroTipo === 'dias' && $dias == 30) ? 'bg-blue-500 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">30
                            dias</a>
                        <a href="?dias=90"
                            class="px-3 py-1 text-sm font-semibold rounded-md <?= ($filtroTipo === 'dias' && $dias == 90) ? 'bg-blue-500 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">90
                            dias</a>
                    </div>
                    <!-- Botão Personalizado -->
                    <button onclick="toggleDatePicker()"
                        class="px-4 py-2 text-sm font-semibold rounded-lg <?= $filtroTipo === 'personalizado' ? 'bg-violet-500 text-white' : 'bg-white text-slate-600 hover:bg-slate-100' ?> shadow-sm border flex items-center gap-2">
                        <i class="fa-solid fa-calendar-days"></i>
                        Personalizado
                    </button>
                </div>
            </div>
            
            <!-- Seletor de Data Personalizado (inicialmente oculto) -->
            <div id="datePickerContainer" class="mt-4 bg-white p-4 rounded-lg shadow-sm border <?= $filtroTipo === 'personalizado' ? '' : 'hidden' ?>">
                <form method="GET" class="flex flex-col sm:flex-row gap-3 items-end">
                    <div class="flex-1">
                        <label for="data_inicio" class="block text-sm font-medium text-slate-700 mb-1">
                            <i class="fa-solid fa-calendar-check text-blue-500"></i> Data Inicial
                        </label>
                        <input type="date" id="data_inicio" name="data_inicio" value="<?= $dataInicio ?? '' ?>"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex-1">
                        <label for="data_fim" class="block text-sm font-medium text-slate-700 mb-1">
                            <i class="fa-solid fa-calendar-xmark text-red-500"></i> Data Final
                        </label>
                        <input type="date" id="data_fim" name="data_fim" value="<?= $dataFim ?? '' ?>"
                            class="w-full px-3 py-2 border border-slate-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" 
                            class="px-4 py-2 bg-blue-500 text-white font-semibold rounded-md hover:bg-blue-600 transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-filter"></i>
                            Filtrar
                        </button>
                        <a href="?" 
                            class="px-4 py-2 bg-slate-200 text-slate-700 font-semibold rounded-md hover:bg-slate-300 transition-colors flex items-center gap-2">
                            <i class="fa-solid fa-rotate-left"></i>
                            Limpar
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Indicadores Principais (KPIs) - 5 cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-10 animate-fade-in">
            <!-- Card Total de Pets -->
            <div class="bg-white border-l-4 border-violet-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Total de Pets</p>
                    <i class="fa-solid fa-paw text-violet-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalPets ?></p>
            </div>
            <!-- Card Total de Tutores -->
            <div class="bg-white border-l-4 border-amber-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Total de Tutores</p>
                    <i class="fa-solid fa-users text-amber-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalTutores ?></p>
            </div>
            <!-- Card Agendados -->
            <div class="bg-white border-l-4 border-indigo-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Agendados</p>
                    <i class="fa-solid fa-calendar-check text-indigo-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalAgendados ?></p>
            </div>
            <!-- Card Em Atendimento -->
            <div class="bg-white border-l-4 border-blue-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Em Atendimento</p>
                    <i class="fa-solid fa-hourglass-half text-blue-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalEmAtendimento ?></p>
            </div>
            <!-- Card Finalizados -->
            <div class="bg-white border-l-4 border-sky-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Finalizados</p>
                    <i class="fa-solid fa-check-double text-sky-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalAtendimentos ?></p>
            </div>
        </div>

        <!-- Linha 1: Atendimentos Diários e Top 5 Clientes -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-chart-line text-sky-500"></i>
                    Animais Atendidos por Dia
                </h2>
                <div style="position: relative; height: 250px;">
                    <canvas id="atendimentosChart"></canvas>
                </div>
            </div>
            
            <!-- Top 5 Clientes Mais Frequentes -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-trophy text-amber-500"></i>
                    Top 5 Clientes Mais Frequentes
                </h2>
                <div class="space-y-4">
                    <?php foreach ($topTutores as $index => $tutor): ?>
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-amber-500 flex items-center justify-center font-bold text-white text-sm">
                                <?= $index + 1 ?>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-semibold text-slate-700 text-sm"><?= htmlspecialchars($tutor['nome']) ?></span>
                                    <span class="text-sm font-bold text-slate-800"><?= $tutor['total_agendamentos'] ?> visitas</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2.5">
                                    <?php 
                                    $maxTotal = $topTutores[0]['total_agendamentos'];
                                    $percentage = ($tutor['total_agendamentos'] / $maxTotal) * 100;
                                    ?>
                                    <div class="bg-amber-500 h-2.5 rounded-full transition-all duration-500" style="width: <?= $percentage ?>%"></div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Linha 2: Top 5 Serviços e Status dos Agendamentos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top 5 Serviços com Barras de Progresso -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-ranking-star text-violet-500"></i>
                    Top 5 Serviços Mais Realizados
                </h2>
                <div class="space-y-4">
                    <?php foreach ($servicosMais as $index => $servico): ?>
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-violet-500 flex items-center justify-center font-bold text-white text-sm">
                                <?= $index + 1 ?>
                            </div>
                            <div class="flex-1">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="font-semibold text-slate-700 text-sm"><?= htmlspecialchars($servico['nome']) ?></span>
                                    <span class="text-sm font-bold text-slate-800"><?= $servico['total'] ?> vezes</span>
                                </div>
                                <div class="w-full bg-slate-200 rounded-full h-2.5">
                                    <?php 
                                    $maxTotal = $servicosMais[0]['total'];
                                    $percentage = ($servico['total'] / $maxTotal) * 100;
                                    ?>
                                    <div class="bg-violet-500 h-2.5 rounded-full transition-all duration-500" style="width: <?= $percentage ?>%"></div>
                                </div>
                                <p class="text-xs text-slate-500 mt-1">Receita: R$ <?= number_format($servico['receita'], 2, ',', '.') ?></p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Status dos Agendamentos (Gráfico de Rosca) -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-chart-pie text-blue-500"></i>
                    Status dos Agendamentos
                </h2>
                <div style="position: relative; height: 250px;">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </main>

    <?php include $path_prefix . 'components/footer.php'; ?>
    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(30px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.8s ease;
        }
    </style>
    <script>
        // Função para mostrar/ocultar o seletor de datas
        function toggleDatePicker() {
            const container = document.getElementById('datePickerContainer');
            container.classList.toggle('hidden');
        }

        // Atendimentos por dia
        const labelsDiarios = <?= json_encode($labelsDiarios) ?>;
        const atendimentosData = <?= json_encode($atendimentosPorDia) ?>;
        new Chart(document.getElementById('atendimentosChart'), {
            type: 'line',
            data: {
                labels: labelsDiarios,
                datasets: [{
                    label: 'Animais Atendidos',
                    data: atendimentosData,
                    borderColor: '#0ea5e9', // sky-500
                    backgroundColor: 'rgba(14, 165, 233, 0.15)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0ea5e9',
                    pointBorderColor: '#fff',
                    pointRadius: 4,
                    pointHoverRadius: 7,
                    pointStyle: 'circle'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b', // slate-800
                        titleFont: { weight: 'bold' },
                        bodyFont: { size: 14 },
                        padding: 12,
                        cornerRadius: 8,
                        boxPadding: 4
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#475569' } // slate-600
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e2e8f0' }, // slate-200
                        ticks: { color: '#475569' } // slate-600
                    }
                }
            }
        });

        // Status dos Agendamentos (Gráfico de Rosca)
        const statusLabels = <?= json_encode(array_column($statusDistribuicao, 'status')) ?>;
        const statusData = <?= json_encode(array_column($statusDistribuicao, 'total')) ?>;
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: [
                        'rgba(99, 102, 241, 0.8)',   // indigo
                        'rgba(14, 165, 233, 0.8)',   // sky
                        'rgba(34, 197, 94, 0.8)',    // green
                        'rgba(245, 158, 11, 0.8)',   // amber
                        'rgba(239, 68, 68, 0.8)'     // red
                    ],
                    borderColor: '#fff',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 12,
                            font: { size: 12 },
                            usePointStyle: true
                        }
                    },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { weight: 'bold' },
                        bodyFont: { size: 14 },
                        padding: 12,
                        cornerRadius: 8
                    }
                }
            }
        });
    </script>
</body>

</html>