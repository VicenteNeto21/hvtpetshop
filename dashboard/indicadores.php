<?php
include "../config/config.php";
session_start();

// Protege a página
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Indicadores principais
$totalPets = $pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();
$totalTutores = $pdo->query("SELECT COUNT(*) FROM tutores")->fetchColumn();
$totalAtendimentos = $pdo->query("SELECT COUNT(DISTINCT pet_id, data_hora) FROM agendamentos WHERE status = 'Finalizado'")->fetchColumn();

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

// Atendimentos por dia (últimos 15 dias)
$atendimentosDia = $pdo->query("
    SELECT DATE(data_hora) as dia, COUNT(*) as total
    FROM agendamentos WHERE status = 'Finalizado'
    GROUP BY dia
    ORDER BY dia DESC
    LIMIT 15
")->fetchAll(PDO::FETCH_ASSOC);

// Após a consulta $atendimentosDia, formate as datas para PT-BR
foreach ($atendimentosDia as &$dia) {
    $dateObj = DateTime::createFromFormat('Y-m-d', $dia['dia']);
    if ($dateObj) {
        $dia['dia'] = $dateObj->format('d/m');
    }
}
unset($dia);

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
            <h1 class="text-3xl font-bold text-slate-800">Indicadores do Sistema</h1>
            <p class="text-slate-500 mt-1">Visão geral e estatísticas de desempenho.</p>
        </div>

        <!-- Indicadores Principais (KPIs) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10 animate-fade-in">
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
            <!-- Card Total de Atendimentos Finalizados -->
            <div class="bg-white border-l-4 border-sky-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Atendimentos Finalizados</p>
                    <i class="fa-solid fa-calendar-check text-sky-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalAtendimentos ?></p>
            </div>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
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
                    Atendimentos por Dia
                </h2>
                <canvas id="atendimentosChart" height="200"></canvas>
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
                    backgroundColor: [
                        '#8b5cf6', // violet-500
                        '#3b82f6', // blue-500
                        '#f59e0b', // amber-500
                        '#0ea5e9', // sky-500
                        '#ec4899'  // pink-500 (ou outra cor de destaque)
                    ],
                    borderRadius: 8,
                    borderSkipped: false
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
        const atendimentosLabels = <?= json_encode(array_reverse(array_column($atendimentosDia, 'dia'))) ?>;
        const atendimentosData = <?= json_encode(array_reverse(array_column($atendimentosDia, 'total'))) ?>;
        new Chart(document.getElementById('atendimentosChart'), {
            type: 'line',
            data: {
                labels: atendimentosLabels,
                datasets: [{
                    label: 'Atendimentos',
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
    </script>
</body>
</html>