<?php
include "config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

// Filtros de data
$data_inicio = isset($_GET['data_inicio']) ? $_GET['data_inicio'] : null;
$data_fim = isset($_GET['data_fim']) ? $_GET['data_fim'] : null;

// Monta os filtros para as queries
$filtro = "";
$params = [];
if ($data_inicio && $data_fim) {
    $filtro = " AND data_hora BETWEEN :inicio AND :fim ";
    $params[':inicio'] = $data_inicio . " 00:00:00";
    $params[':fim'] = $data_fim . " 23:59:59";
}

// Atendimentos Diários
$queryDiaria = "SELECT DATE(data_hora) AS dia, COUNT(*) AS total FROM agendamentos WHERE status IN ('Finalizado', 'Pendente') $filtro GROUP BY dia ORDER BY dia DESC LIMIT 30";
$stmtDiaria = $pdo->prepare($queryDiaria);
$stmtDiaria->execute($params);
$dadosDiarios = $stmtDiaria->fetchAll(PDO::FETCH_ASSOC);

// Atendimentos Mensais
$queryMensal = "SELECT DATE_FORMAT(data_hora, '%Y-%m') AS mes, COUNT(*) AS total FROM agendamentos WHERE status IN ('Finalizado', 'Pendente') $filtro GROUP BY mes ORDER BY mes DESC LIMIT 12";
$stmtMensal = $pdo->prepare($queryMensal);
$stmtMensal->execute($params);
$dadosMensais = $stmtMensal->fetchAll(PDO::FETCH_ASSOC);

// Atendimentos Anuais
$queryAnual = "SELECT YEAR(data_hora) AS ano, COUNT(*) AS total FROM agendamentos WHERE status = 'Finalizado' $filtro GROUP BY ano ORDER BY ano DESC";
$stmtAnual = $pdo->prepare($queryAnual);
$stmtAnual->execute($params);
$dadosAnuais = $stmtAnual->fetchAll(PDO::FETCH_ASSOC);

// Total de vendas (simulação para PDV)
$queryVendas = "SELECT SUM(valor_total) as total_vendas, COUNT(*) as qtd_vendas FROM vendas WHERE 1=1";
if ($data_inicio && $data_fim) {
    $queryVendas .= " AND data_hora BETWEEN :inicio AND :fim";
}
$stmtVendas = $pdo->prepare($queryVendas);
$stmtVendas->execute($params);
$vendas = $stmtVendas->fetch(PDO::FETCH_ASSOC);

// Últimas vendas (simulação para PDV)
$queryUltimas = "SELECT v.id, v.data_hora, v.valor_total, t.nome as cliente 
                 FROM vendas v 
                 LEFT JOIN tutores t ON v.tutor_id = t.id
                 WHERE 1=1";
if ($data_inicio && $data_fim) {
    $queryUltimas .= " AND v.data_hora BETWEEN :inicio AND :fim";
}
$queryUltimas .= " ORDER BY v.data_hora DESC LIMIT 10";
$stmtUltimas = $pdo->prepare($queryUltimas);
$stmtUltimas->execute($params);
$ultimasVendas = $stmtUltimas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Caixa & Dashboard - HVTPETSHOP</title>
    <link rel="icon" type="image/x-icon" href="icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">
    <!-- Navbar padrão -->
    <nav class="w-full bg-white/90 shadow flex items-center justify-between px-6 py-3">
        <div class="flex items-center gap-3">
            <img src="icons/pet.jpg" alt="Logo Petshop" class="w-10 h-10 rounded-full shadow">
            <span class="text-2xl font-bold text-blue-700 tracking-tight">HVTPETSHOP</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 font-semibold transition"><i class="fa fa-home mr-1"></i>Dashboard</a>
            <a href="tutores/listar_tutores.php" class="text-blue-500 hover:text-blue-700 font-semibold transition"><i class="fa fa-users mr-1"></i>Tutores</a>
            <a href="auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition"><i class="fa fa-sign-out-alt mr-1"></i>Sair</a>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-7xl mx-auto mt-10 p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Card Total de Vendas -->
            <div class="bg-white/90 p-6 rounded-2xl shadow-xl flex flex-col items-center justify-center animate-fade-in">
                <div class="text-4xl text-green-600 mb-2"><i class="fa fa-cash-register"></i></div>
                <div class="text-lg text-gray-500">Total em vendas</div>
                <div class="text-2xl font-bold text-green-700 mb-1">
                    R$ <?= number_format($vendas['total_vendas'] ?? 0, 2, ',', '.') ?>
                </div>
                <div class="text-xs text-gray-400">Qtd. vendas: <?= $vendas['qtd_vendas'] ?? 0 ?></div>
            </div>
            <!-- Card Atendimentos Diários -->
            <div class="bg-white/90 p-6 rounded-2xl shadow-xl flex flex-col items-center justify-center animate-fade-in">
                <div class="text-4xl text-blue-600 mb-2"><i class="fa fa-calendar-day"></i></div>
                <div class="text-lg text-gray-500">Atendimentos hoje</div>
                <div class="text-2xl font-bold text-blue-700 mb-1">
                    <?= $dadosDiarios[0]['total'] ?? 0 ?>
                </div>
                <div class="text-xs text-gray-400">Último dia: <?= isset($dadosDiarios[0]['dia']) ? date('d/m/Y', strtotime($dadosDiarios[0]['dia'])) : '-' ?></div>
            </div>
            <!-- Card Atendimentos Mensais -->
            <div class="bg-white/90 p-6 rounded-2xl shadow-xl flex flex-col items-center justify-center animate-fade-in">
                <div class="text-4xl text-orange-500 mb-2"><i class="fa fa-calendar-alt"></i></div>
                <div class="text-lg text-gray-500">Atendimentos mês</div>
                <div class="text-2xl font-bold text-orange-600 mb-1">
                    <?= $dadosMensais[0]['total'] ?? 0 ?>
                </div>
                <div class="text-xs text-gray-400">Último mês: <?= isset($dadosMensais[0]['mes']) ? date('m/Y', strtotime($dadosMensais[0]['mes'].'-01')) : '-' ?></div>
            </div>
        </div>

        <!-- Filtros -->
        <div class="bg-white/90 p-8 rounded-2xl shadow-xl animate-fade-in mb-8">
            <h3 class="text-xl font-semibold text-blue-700 mb-4 flex items-center gap-2">
                <i class="fa fa-filter"></i> Filtros
            </h3>
            <form method="GET" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="data_inicio" class="block text-gray-700">Data Início</label>
                    <input type="date" name="data_inicio" id="data_inicio" value="<?= htmlspecialchars($data_inicio) ?>" class="p-2 border rounded w-full">
                </div>
                <div>
                    <label for="data_fim" class="block text-gray-700">Data Fim</label>
                    <input type="date" name="data_fim" id="data_fim" value="<?= htmlspecialchars($data_fim) ?>" class="p-2 border rounded w-full">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-4 rounded-lg flex items-center gap-2">
                        <i class="fa fa-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
            <div class="bg-white/90 p-6 rounded-2xl shadow-xl animate-fade-in">
                <h3 class="text-xl font-semibold text-blue-700 mb-2 flex items-center gap-2">
                    <i class="fa fa-calendar-day"></i> Atendimentos Diários
                </h3>
                <canvas id="graficoDiario"></canvas>
            </div>
            <div class="bg-white/90 p-6 rounded-2xl shadow-xl animate-fade-in">
                <h3 class="text-xl font-semibold text-blue-700 mb-2 flex items-center gap-2">
                    <i class="fa fa-calendar-alt"></i> Atendimentos Mensais
                </h3>
                <canvas id="graficoMensal"></canvas>
            </div>
            <div class="bg-white/90 p-6 rounded-2xl shadow-xl animate-fade-in md:col-span-2">
                <h3 class="text-xl font-semibold text-blue-700 mb-2 flex items-center gap-2">
                    <i class="fa fa-chart-line"></i> Atendimentos Anuais (Apenas Finalizados)
                </h3>
                <canvas id="graficoAnual"></canvas>
            </div>
        </div>

        <!-- Últimas vendas (PDV) -->
        <div class="bg-white/90 p-8 rounded-2xl shadow-xl animate-fade-in mt-8">
            <h3 class="text-xl font-semibold text-blue-700 mb-4 flex items-center gap-2">
                <i class="fa fa-receipt"></i> Últimas vendas
            </h3>
            <?php if (empty($ultimasVendas)): ?>
                <div class="text-gray-400 text-sm">Nenhuma venda registrada neste período.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-100 bg-white rounded-xl shadow">
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-bold text-blue-700 uppercase">Data/Hora</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-blue-700 uppercase">Cliente</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-blue-700 uppercase">Valor</th>
                                <th class="px-4 py-2 text-left text-xs font-bold text-blue-700 uppercase">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-50">
                            <?php foreach ($ultimasVendas as $venda): ?>
                                <tr>
                                    <td class="px-4 py-2"><?= date('d/m/Y H:i', strtotime($venda['data_hora'])) ?></td>
                                    <td class="px-4 py-2"><?= htmlspecialchars($venda['cliente'] ?? 'Não informado') ?></td>
                                    <td class="px-4 py-2 text-green-700 font-bold">R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></td>
                                    <td class="px-4 py-2">
                                        <a href="vendas/visualizar_venda.php?id=<?= $venda['id'] ?>" class="text-blue-600 hover:underline flex items-center gap-1 text-xs">
                                            <i class="fa fa-eye"></i> Visualizar
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div class="flex justify-end mb-4">
        <a href="vendas/cadastrar_venda.php" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-semibold shadow flex items-center gap-2 transition">
            <i class="fa fa-plus"></i> Nova Venda
        </a>
    </div>

    <footer class="w-full py-3 bg-white/80 text-center text-gray-400 text-xs mt-8">
        &copy; 2025 HVTPETSHOP. Todos os direitos reservados.
    </footer>

    <script>
        // Dados do PHP para JavaScript
        const dadosDiarios = <?= json_encode($dadosDiarios); ?>;
        const dadosMensais = <?= json_encode($dadosMensais); ?>;
        const dadosAnuais = <?= json_encode($dadosAnuais); ?>;

        // Função para formatar a data no formato "dd/mm"
        function formatarDataBrasil(data) {
            if (!data) return '';
            const partes = data.split('-');
            if (partes.length === 3) return partes[2] + '/' + partes[1];
            if (partes.length === 2) return partes[1] + '/' + partes[0];
            return data;
        }

        // Gráfico Diário
        new Chart(document.getElementById('graficoDiario'), {
            type: 'line',
            data: {
                labels: dadosDiarios.map(d => formatarDataBrasil(d.dia)),
                datasets: [{
                    label: 'Atendimentos Diários',
                    data: dadosDiarios.map(d => d.total),
                    borderColor: 'blue',
                    backgroundColor: 'rgba(0, 0, 255, 0.2)',
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });

        // Gráfico Mensal
        new Chart(document.getElementById('graficoMensal'), {
            type: 'bar',
            data: {
                labels: dadosMensais.map(d => formatarDataBrasil(d.mes + '-01')),
                datasets: [{
                    label: 'Atendimentos Mensais',
                    data: dadosMensais.map(d => d.total),
                    backgroundColor: 'rgba(255, 165, 0, 0.7)'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });

        // Gráfico Anual
        new Chart(document.getElementById('graficoAnual'), {
            type: 'bar',
            data: {
                labels: dadosAnuais.map(d => d.ano),
                datasets: [{
                    label: 'Atendimentos Anuais',
                    data: dadosAnuais.map(d => d.total),
                    backgroundColor: 'rgba(0, 128, 0, 0.7)'
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });
    </script>
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: translateY(0);}
        }
        .animate-fade-in {
            animation: fade-in 0.8s ease;
        }
    </style>
</body>
</html>
