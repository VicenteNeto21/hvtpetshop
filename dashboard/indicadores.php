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
$totalAtendimentos = $pdo->query("SELECT COUNT(*) FROM agendamentos")->fetchColumn();

// Serviços mais realizados
$servicosMais = $pdo->query("
    SELECT s.nome, COUNT(*) as total
    FROM ficha_servicos_realizados fsr
    JOIN servicos s ON fsr.servico_id = s.id
    GROUP BY s.nome
    ORDER BY total DESC
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Atendimentos por dia (últimos 15 dias)
$atendimentosDia = $pdo->query("
    SELECT DATE(data_hora) as dia, COUNT(*) as total
    FROM agendamentos
    GROUP BY dia
    ORDER BY dia DESC
    LIMIT 15
")->fetchAll(PDO::FETCH_ASSOC);

// Após a consulta $atendimentosDia, formate as datas para PT-BR
foreach ($atendimentosDia as &$dia) {
    $dateObj = DateTime::createFromFormat('Y-m-d', $dia['dia']);
    if ($dateObj) {
        $dia['dia'] = $dateObj->format('d/m/Y');
    }
}
unset($dia);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Indicadores</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gradient-to-br from-blue-50 to-blue-200 min-h-screen">
    <nav class="w-full bg-white/90 shadow flex items-center justify-between px-6 py-3">
        <div class="flex items-center gap-3">
            <img src="../icons/pet.jpg" alt="Logo Petshop" class="w-10 h-10 rounded-full shadow">
            <span class="text-2xl font-bold text-blue-700 tracking-tight">HVTPETSHOP</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="../dashboard.php" class="text-blue-600 hover:text-blue-800 font-semibold transition"><i class="fa fa-home mr-1"></i>Dashboard</a>
            <a href="../pets/agendamentos/indicadores.php" class="text-blue-700 font-bold transition"><i class="fa fa-chart-bar mr-1"></i>Indicadores</a>
            <a href="../auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition"><i class="fa fa-sign-out-alt mr-1"></i>Sair</a>
        </div>
    </nav>
    <main class="max-w-5xl mx-auto mt-10 p-4">
        <h1 class="text-2xl font-bold text-blue-700 mb-6 flex items-center gap-2"><i class="fa fa-chart-bar"></i> Dashboard de Indicadores</h1>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                <span class="text-blue-600 text-3xl font-bold"><?= $totalPets ?></span>
                <span class="text-gray-500 mt-2">Pets ativos</span>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                <span class="text-blue-600 text-3xl font-bold"><?= $totalTutores ?></span>
                <span class="text-gray-500 mt-2">Tutores</span>
            </div>
            <div class="bg-white rounded-xl shadow p-6 flex flex-col items-center">
                <span class="text-blue-600 text-3xl font-bold"><?= $totalAtendimentos ?></span>
                <span class="text-gray-500 mt-2">Atendimentos</span>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-blue-700 mb-4">Serviços Mais Realizados</h2>
                <canvas id="servicosChart" height="200"></canvas>
            </div>
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-semibold text-blue-700 mb-4">Atendimentos por Dia</h2>
                <canvas id="atendimentosChart" height="200"></canvas>
            </div>
        </div>
    </main>
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
                        '#2563eb', '#60a5fa', '#818cf8', '#38bdf8', '#f472b6'
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
                        backgroundColor: '#2563eb',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#fff',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#2563eb', font: { weight: 'bold' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e0e7ef' },
                        ticks: { color: '#2563eb', font: { weight: 'bold' } }
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
                    borderColor: '#2563eb',
                    backgroundColor: 'rgba(37,99,235,0.15)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#2563eb',
                    pointBorderColor: '#fff',
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointStyle: 'circle'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: { display: false },
                    title: { display: false },
                    tooltip: {
                        backgroundColor: '#2563eb',
                        titleColor: '#fff',
                        bodyColor: '#fff',
                        borderColor: '#fff',
                        borderWidth: 1
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#2563eb', font: { weight: 'bold' } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e0e7ef' },
                        ticks: { color: '#2563eb', font: { weight: 'bold' } }
                    }
                }
            }
        });
    </script>
</body>
</html>