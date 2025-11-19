<?php
include "../config/config.php";
session_start(); // Inicia a sessão

// Protege a página
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}
// --- CONSULTAS AOS DADOS ---

// Filtro de período (padrão: 15 dias)
$dias = isset($_GET['dias']) ? (int)$_GET['dias'] : 15;
if (!in_array($dias, [7, 15, 30])) {
    $dias = 15; // Garante que o valor seja um dos permitidos
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


// Indicadores principais (código original movido para o bloco de cache acima)
// $totalPets = $pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();
// $totalTutores = $pdo->query("SELECT COUNT(*) FROM tutores")->fetchColumn();
// $totalAtendimentos = $pdo->query("SELECT COUNT(DISTINCT pet_id, data_hora) FROM agendamentos WHERE status = 'Finalizado'")->fetchColumn();
// // Novo: Receita total (considerando que a tabela 'servicos' tem uma coluna 'valor')
// $totalEmAtendimento = $pdo->query("SELECT COUNT(DISTINCT pet_id, data_hora) FROM agendamentos WHERE status = 'Em Atendimento'")->fetchColumn();

// $receitaTotal = $pdo->query("SELECT SUM(s.preco) FROM agendamentos a JOIN servicos s ON a.servico_id = s.id WHERE a.status = 'Finalizado'")->fetchColumn() ?? 0;


// Serviços mais realizados
$servicosMais = $pdo->query("
    SELECT s.nome, COUNT(a.id) as total
    FROM agendamentos a
    JOIN servicos s ON a.servico_id = s.id
    WHERE a.status = 'Finalizado'
    GROUP BY s.nome
    ORDER BY total DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Atendimentos e Receita por dia (período dinâmico)
$queryDia = "
    SELECT
        DATE(a.data_hora) as dia,
        COUNT(DISTINCT a.pet_id) as total_atendimentos,
        SUM(s.preco) as total_receita
    FROM agendamentos a
    JOIN servicos s ON a.servico_id = s.id
    WHERE a.status = 'Finalizado' AND a.data_hora >= CURDATE() - INTERVAL :dias DAY
    GROUP BY dia
    ORDER BY dia ASC
";
$stmtDia = $pdo->prepare($queryDia);
$stmtDia->bindValue(':dias', $dias, PDO::PARAM_INT);
$stmtDia->execute();
$dadosDiarios = $stmtDia->fetchAll(PDO::FETCH_ASSOC);

// Formata os dados para os gráficos
$labelsDiarios = [];
$atendimentosPorDia = [];
$receitaPorDia = [];
foreach ($dadosDiarios as $dia) {
    $dateObj = DateTime::createFromFormat('Y-m-d', $dia['dia']);
    $labelsDiarios[] = $dateObj ? $dateObj->format('d/m') : $dia['dia'];
    $atendimentosPorDia[] = $dia['total_atendimentos'];
    $receitaPorDia[] = $dia['total_receita'];
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
                <div class="flex items-center gap-2 bg-white p-1 rounded-lg shadow-sm border">
                    <a href="?dias=7" class="px-3 py-1 text-sm font-semibold rounded-md <?= $dias == 7 ? 'bg-blue-500 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">7 dias</a>
                    <a href="?dias=15" class="px-3 py-1 text-sm font-semibold rounded-md <?= $dias == 15 ? 'bg-blue-500 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">15 dias</a>
                    <a href="?dias=30" class="px-3 py-1 text-sm font-semibold rounded-md <?= $dias == 30 ? 'bg-blue-500 text-white' : 'text-slate-600 hover:bg-slate-100' ?>">30 dias</a>
                </div>
            </div>
        </div>

        <!-- Indicadores Principais (KPIs) -->
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
            <!-- Card Atendimentos Pendentes -->
            <div class="bg-white border-l-4 border-blue-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Aguardando Finalização</p>
                    <i class="fa-solid fa-hourglass-half text-blue-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalEmAtendimento ?></p>
            </div>
            <!-- Card Total de Atendimentos Finalizados -->
            <div class="bg-white border-l-4 border-sky-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Atendimentos Finalizados</p>
                    <i class="fa-solid fa-check-double text-sky-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalAtendimentos ?></p>
            </div>
            <!-- Card Receita Total -->
            <div class="bg-white border-l-4 border-green-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Receita Total (Finalizados)</p>
                    <i class="fa-solid fa-dollar-sign text-green-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2">R$ <?= number_format($receitaTotal, 2, ',', '.') ?></p>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-chart-pie text-violet-500"></i>
                    Serviços Mais Realizados
                </h2>
                <canvas id="servicosChart" height="200"></canvas>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-chart-line text-sky-500"></i>
                    Animais Atendidos por Dia
                </h2>
                <canvas id="atendimentosChart" height="200"></canvas>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in lg:col-span-1">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-chart-area text-green-500"></i>
                    Receita por Dia
                </h2>
                <canvas id="receitaChart" height="200"></canvas>
            </div>
        </div>
    </main>

    <?php include $path_prefix . 'components/footer.php'; ?>
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: translateY(0);}
        }
        .animate-fade-in {
            animation: fade-in 0.8s ease;
        }
    </style>
    <script>
        // Serviços mais realizados
        const servicosLabels = <?= json_encode(array_column($servicosMais, 'nome')) ?>;
        const servicosData = <?= json_encode(array_column($servicosMais, 'total')) ?>;
        new Chart(document.getElementById('servicosChart'), {
            type: 'bar',
            data: {
                labels: servicosLabels,
                datasets: [{
                    label: 'Quantidade',
                    data: servicosData,
                    backgroundColor: [ // Cores com um pouco de transparência
                        'rgba(139, 92, 246, 0.8)', // violet-500
                        'rgba(59, 130, 246, 0.8)', // blue-500
                        'rgba(245, 158, 11, 0.8)', // amber-500
                        'rgba(14, 165, 233, 0.8)', // sky-500
                        'rgba(236, 72, 153, 0.8)'  // pink-500
                    ],
                    borderColor: [ // Bordas sólidas
                        '#8b5cf6',
                        '#3b82f6',
                        '#f59e0b',
                        '#0ea5e9',
                        '#ec4899'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: {
                        display: false
                    },
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
                        ticks: { color: '#475569', font: { weight: 'bold' } } // slate-600
                    },
                    y: {
                        beginAtZero: true,
                        ticks: { color: '#475569', font: { weight: 'bold' } } // slate-600
                    }
                }
            }
        });

        // Atendimentos por dia (últimos 15 dias)
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

        // Receita por dia
        const receitaData = <?= json_encode($receitaPorDia) ?>;
        new Chart(document.getElementById('receitaChart'), {
            type: 'bar',
            data: {
                labels: labelsDiarios,
                datasets: [{
                    label: 'Receita (R$)',
                    data: receitaData,
                    backgroundColor: 'rgba(34, 197, 94, 0.8)', // green-500 com transparência
                    borderColor: '#16a34a', // green-600
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleFont: { weight: 'bold' },
                        bodyFont: { size: 14 },
                        padding: 12,
                        cornerRadius: 8,
                        callbacks: {
                            label: function(context) {
                                return `Receita: R$ ${context.parsed.y.toFixed(2).replace('.', ',')}`;
                            }
                        }
                    }
                },
                scales: {
                    x: { grid: { display: false }, ticks: { color: '#475569' } },
                    y: { beginAtZero: true, grid: { color: '#e2e8f0' }, ticks: { color: '#475569' } }
                }
            }
        });
    </script>
</body>
</html>