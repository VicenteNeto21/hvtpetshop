<?php
include "../config/config.php";
session_start();

// Protege a página
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

<<<<<<< HEAD
// --- FILTRO POR MÊS ---
$mesAtual = (int)date('m');
$anoAtual = (int)date('Y');

// Obtém mês e ano do filtro (padrão: mês atual)
$mesSelecionado = isset($_GET['mes']) ? (int)$_GET['mes'] : $mesAtual;
$anoSelecionado = isset($_GET['ano']) ? (int)$_GET['ano'] : $anoAtual;

// Valida os valores
if ($mesSelecionado < 1 || $mesSelecionado > 12) $mesSelecionado = $mesAtual;
if ($anoSelecionado < 2020 || $anoSelecionado > $anoAtual + 1) $anoSelecionado = $anoAtual;

// Calcula primeiro e último dia do mês selecionado
$primeiroDiaMes = sprintf('%04d-%02d-01', $anoSelecionado, $mesSelecionado);
$ultimoDiaMes = date('Y-m-t', strtotime($primeiroDiaMes));

// Nomes dos meses em português
$nomesMeses = [
    1 => 'Janeiro', 2 => 'Fevereiro', 3 => 'Março', 4 => 'Abril',
    5 => 'Maio', 6 => 'Junho', 7 => 'Julho', 8 => 'Agosto',
    9 => 'Setembro', 10 => 'Outubro', 11 => 'Novembro', 12 => 'Dezembro'
];
$nomeMesSelecionado = $nomesMeses[$mesSelecionado];

// Calcula mês anterior e próximo
$mesAnterior = $mesSelecionado - 1;
$anoAnterior = $anoSelecionado;
if ($mesAnterior < 1) {
    $mesAnterior = 12;
    $anoAnterior--;
=======
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
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
}

$mesProximo = $mesSelecionado + 1;
$anoProximo = $anoSelecionado;
if ($mesProximo > 12) {
    $mesProximo = 1;
    $anoProximo++;
}

<<<<<<< HEAD
// Verifica se é o mês atual
$isMesAtual = ($mesSelecionado == $mesAtual && $anoSelecionado == $anoAtual);

// --- CONSULTAS AOS DADOS (FILTRADAS POR MÊS) ---

// Atendimentos finalizados no mês
$stmtAtendimentos = $pdo->prepare("
    SELECT COUNT(DISTINCT pet_id, data_hora) 
    FROM agendamentos 
    WHERE status = 'Finalizado' 
    AND DATE(data_hora) BETWEEN :inicio AND :fim
");
$stmtAtendimentos->execute([':inicio' => $primeiroDiaMes, ':fim' => $ultimoDiaMes]);
$totalAtendimentosMes = $stmtAtendimentos->fetchColumn();

// Receita do mês
$stmtReceita = $pdo->prepare("
    SELECT COALESCE(SUM(s.preco), 0) 
    FROM agendamentos a 
    JOIN servicos s ON a.servico_id = s.id 
    WHERE a.status = 'Finalizado' 
    AND DATE(a.data_hora) BETWEEN :inicio AND :fim
");
$stmtReceita->execute([':inicio' => $primeiroDiaMes, ':fim' => $ultimoDiaMes]);
$receitaMes = $stmtReceita->fetchColumn();

// Total de pets (geral do sistema)
$totalPets = $pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();

// Total de tutores (geral do sistema)
$totalTutores = $pdo->query("SELECT COUNT(*) FROM tutores")->fetchColumn();

// Agendamentos cancelados no mês
$stmtCancelados = $pdo->prepare("
    SELECT COUNT(DISTINCT pet_id, data_hora) 
    FROM agendamentos 
    WHERE status = 'Cancelado' 
    AND DATE(data_hora) BETWEEN :inicio AND :fim
");
$stmtCancelados->execute([':inicio' => $primeiroDiaMes, ':fim' => $ultimoDiaMes]);
$canceladosMes = $stmtCancelados->fetchColumn();

// Top Tutores do mês
$stmtTopTutores = $pdo->prepare("
    SELECT t.nome, COUNT(DISTINCT a.data_hora) as total_agendamentos
    FROM agendamentos a
    JOIN pets p ON a.pet_id = p.id
    JOIN tutores t ON p.tutor_id = t.id
    WHERE a.status = 'Finalizado'
    AND DATE(a.data_hora) BETWEEN :inicio AND :fim
    GROUP BY t.id, t.nome
    ORDER BY total_agendamentos DESC
    LIMIT 5
");
$stmtTopTutores->execute([':inicio' => $primeiroDiaMes, ':fim' => $ultimoDiaMes]);
$topTutores = $stmtTopTutores->fetchAll(PDO::FETCH_ASSOC);

// Serviços mais realizados no mês
$stmtServicos = $pdo->prepare("
=======
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
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
    SELECT s.nome, COUNT(a.id) as total, SUM(s.preco) as receita
    FROM agendamentos a
    JOIN servicos s ON a.servico_id = s.id
    WHERE a.status = 'Finalizado'
<<<<<<< HEAD
    AND DATE(a.data_hora) BETWEEN :inicio AND :fim
=======
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
    GROUP BY s.id, s.nome
    ORDER BY total DESC
    LIMIT 5
");
$stmtServicos->execute([':inicio' => $primeiroDiaMes, ':fim' => $ultimoDiaMes]);
$servicosMais = $stmtServicos->fetchAll(PDO::FETCH_ASSOC);

<<<<<<< HEAD
// Distribuição por Status no mês
$stmtStatus = $pdo->prepare("
    SELECT status, COUNT(*) as total
    FROM agendamentos
    WHERE DATE(data_hora) BETWEEN :inicio AND :fim
    GROUP BY status
");
$stmtStatus->execute([':inicio' => $primeiroDiaMes, ':fim' => $ultimoDiaMes]);
$statusDistribuicao = $stmtStatus->fetchAll(PDO::FETCH_ASSOC);

// Atendimentos por dia do mês
$stmtDiario = $pdo->prepare("
    SELECT
        DATE(a.data_hora) as dia,
        COUNT(DISTINCT a.pet_id) as total_atendimentos
    FROM agendamentos a
    WHERE a.status = 'Finalizado' 
        AND DATE(a.data_hora) BETWEEN :inicio AND :fim
    GROUP BY dia
    ORDER BY dia ASC
");
$stmtDiario->execute([':inicio' => $primeiroDiaMes, ':fim' => $ultimoDiaMes]);
$dadosDiarios = $stmtDiario->fetchAll(PDO::FETCH_ASSOC);
=======
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
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469

// Formata os dados para os gráficos
$labelsDiarios = [];
$atendimentosPorDia = [];
foreach ($dadosDiarios as $dia) {
    $dateObj = DateTime::createFromFormat("Y-m-d", $dia['dia']);
<<<<<<< HEAD
    $labelsDiarios[] = $dateObj ? $dateObj->format('d') : $dia['dia'];
=======
    $labelsDiarios[] = $dateObj ? $dateObj->format('d/m') : $dia['dia'];
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
    $atendimentosPorDia[] = $dia['total_atendimentos'];
}

// Define o prefixo do caminho para o navbar
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
        <!-- Cabeçalho da Página com Seletor de Mês -->
        <div class="mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800">
                        <i class="fa-solid fa-chart-simple text-blue-500 mr-2"></i>
                        Indicadores de <?= $nomeMesSelecionado ?>/<?= $anoSelecionado ?>
                    </h1>
                    <p class="text-slate-500 mt-1">Visão geral do desempenho do mês selecionado.</p>
                </div>
<<<<<<< HEAD
                
                <!-- Navegação por Mês -->
                <div class="flex items-center gap-2">
                    <a href="?mes=<?= $mesAnterior ?>&ano=<?= $anoAnterior ?>" 
                       class="bg-white hover:bg-slate-100 text-slate-700 px-4 py-2 rounded-lg font-semibold shadow-sm border transition flex items-center gap-2">
                        <i class="fa-solid fa-chevron-left"></i>
                        <span class="hidden sm:inline"><?= $nomesMeses[$mesAnterior] ?></span>
                    </a>
                    
                    <?php if (!$isMesAtual): ?>
                    <a href="?mes=<?= $mesAtual ?>&ano=<?= $anoAtual ?>" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-calendar-day"></i>
                        Mês Atual
                    </a>
                    <?php endif; ?>
                    
                    <?php 
                    // Só mostra botão "próximo" se não for um mês futuro
                    $dataProximo = strtotime("$anoProximo-$mesProximo-01");
                    $dataAtual = strtotime("$anoAtual-$mesAtual-01");
                    if ($dataProximo <= $dataAtual): 
                    ?>
                    <a href="?mes=<?= $mesProximo ?>&ano=<?= $anoProximo ?>" 
                       class="bg-white hover:bg-slate-100 text-slate-700 px-4 py-2 rounded-lg font-semibold shadow-sm border transition flex items-center gap-2">
                        <span class="hidden sm:inline"><?= $nomesMeses[$mesProximo] ?></span>
                        <i class="fa-solid fa-chevron-right"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Seção de Geração de Relatórios -->
            <div class="mt-6 bg-white p-5 rounded-lg shadow-sm border">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-violet-100 rounded-lg flex items-center justify-center">
                            <i class="fa-solid fa-file-export text-violet-600"></i>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800">Gerar Relatório</h3>
                            <p class="text-sm text-slate-500">Selecione um período personalizado</p>
                        </div>
                    </div>
                    
                    <form id="formRelatorio" class="flex flex-col sm:flex-row items-stretch sm:items-end gap-3 w-full md:w-auto">
                        <div class="flex-1 sm:flex-initial">
                            <label for="rel_data_inicio" class="block text-xs font-medium text-slate-600 mb-1">
                                <i class="fa-solid fa-calendar text-green-500"></i> Data Inicial
                            </label>
                            <input type="date" id="rel_data_inicio" name="data_inicio" 
                                   value="<?= $primeiroDiaMes ?>"
                                   class="w-full sm:w-40 px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                        </div>
                        <div class="flex-1 sm:flex-initial">
                            <label for="rel_data_fim" class="block text-xs font-medium text-slate-600 mb-1">
                                <i class="fa-solid fa-calendar text-red-500"></i> Data Final
                            </label>
                            <input type="date" id="rel_data_fim" name="data_fim" 
                                   value="<?= date('Y-m-d') ?>"
                                   class="w-full sm:w-40 px-3 py-2 text-sm border border-slate-300 rounded-lg focus:ring-2 focus:ring-violet-500 focus:border-violet-500">
                        </div>
                        <div class="flex gap-2">
                            <button type="button" onclick="gerarRelatorio('visualizar')" 
                                    class="flex-1 sm:flex-initial bg-violet-500 hover:bg-violet-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition flex items-center justify-center gap-2 text-sm">
                                <i class="fa-solid fa-eye"></i>
                                <span class="hidden sm:inline">Visualizar</span>
                            </button>
                            <button type="button" onclick="gerarRelatorio('imprimir')" 
                                    class="flex-1 sm:flex-initial bg-slate-600 hover:bg-slate-700 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition flex items-center justify-center gap-2 text-sm">
                                <i class="fa-solid fa-print"></i>
                                <span class="hidden sm:inline">Imprimir</span>
                            </button>
                        </div>
                    </form>
=======
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
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
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

<<<<<<< HEAD
        <!-- Indicadores Principais (KPIs) - 4 cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-10 animate-fade-in">
            <!-- Card Atendimentos do Mês -->
            <div class="bg-white border-l-4 border-sky-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Atendimentos</p>
                    <i class="fa-solid fa-check-double text-sky-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalAtendimentosMes ?></p>
                <p class="text-xs text-slate-400 mt-1">Finalizados no mês</p>
            </div>

            
=======
        <!-- Indicadores Principais (KPIs) - 5 cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-6 mb-10 animate-fade-in">
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
            <!-- Card Total de Pets -->
            <div class="bg-white border-l-4 border-violet-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Total de Pets</p>
                    <i class="fa-solid fa-paw text-violet-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalPets ?></p>
                <p class="text-xs text-slate-400 mt-1">Cadastrados no sistema</p>
            </div>
            
            <!-- Card Total de Tutores -->
            <div class="bg-white border-l-4 border-amber-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Total de Tutores</p>
                    <i class="fa-solid fa-users text-amber-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalTutores ?></p>
                <p class="text-xs text-slate-400 mt-1">Cadastrados no sistema</p>
            </div>
<<<<<<< HEAD
            
            <!-- Card Cancelamentos -->
            <div class="bg-white border-l-4 border-red-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Cancelamentos</p>
                    <i class="fa-solid fa-ban text-red-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $canceladosMes ?></p>
                <p class="text-xs text-slate-400 mt-1">No mês</p>
=======
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
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
            </div>
        </div>

        <!-- Linha 1: Atendimentos Diários e Top 5 Clientes -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-chart-line text-sky-500"></i>
                    Atendimentos por Dia
                </h2>
<<<<<<< HEAD
                <?php if (empty($dadosDiarios)): ?>
                    <div class="flex items-center justify-center h-[250px] text-slate-400">
                        <div class="text-center">
                            <i class="fa-solid fa-chart-bar text-4xl mb-2"></i>
                            <p>Nenhum atendimento neste mês</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="position: relative; height: 250px;">
                        <canvas id="atendimentosChart"></canvas>
                    </div>
                <?php endif; ?>
=======
                <div style="position: relative; height: 250px;">
                    <canvas id="atendimentosChart"></canvas>
                </div>
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
            </div>
            
            <!-- Top 5 Clientes Mais Frequentes -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-trophy text-amber-500"></i>
<<<<<<< HEAD
                    Top 5 Clientes do Mês
                </h2>
                <?php if (empty($topTutores)): ?>
                    <div class="flex items-center justify-center h-[200px] text-slate-400">
                        <div class="text-center">
                            <i class="fa-solid fa-users text-4xl mb-2"></i>
                            <p>Nenhum cliente atendido neste mês</p>
                        </div>
                    </div>
                <?php else: ?>
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
                <?php endif; ?>
=======
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
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
            </div>
        </div>

        <!-- Linha 2: Top 5 Serviços e Status dos Agendamentos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top 5 Serviços com Barras de Progresso -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-ranking-star text-violet-500"></i>
<<<<<<< HEAD
                    Top 5 Serviços do Mês
                </h2>
                <?php if (empty($servicosMais)): ?>
                    <div class="flex items-center justify-center h-[200px] text-slate-400">
                        <div class="text-center">
                            <i class="fa-solid fa-scissors text-4xl mb-2"></i>
                            <p>Nenhum serviço realizado neste mês</p>
                        </div>
                    </div>
                <?php else: ?>
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
                <?php endif; ?>
=======
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
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
            </div>

            <!-- Status dos Agendamentos (Gráfico de Rosca) -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-chart-pie text-blue-500"></i>
                    Status dos Agendamentos
                </h2>
<<<<<<< HEAD
                <?php if (empty($statusDistribuicao)): ?>
                    <div class="flex items-center justify-center h-[250px] text-slate-400">
                        <div class="text-center">
                            <i class="fa-solid fa-calendar-xmark text-4xl mb-2"></i>
                            <p>Nenhum agendamento neste mês</p>
                        </div>
                    </div>
                <?php else: ?>
                    <div style="position: relative; height: 250px;">
                        <canvas id="statusChart"></canvas>
                    </div>
                <?php endif; ?>
=======
                <div style="position: relative; height: 250px;">
                    <canvas id="statusChart"></canvas>
                </div>
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
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
<<<<<<< HEAD
=======

>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
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
<<<<<<< HEAD
        <?php if (!empty($dadosDiarios)): ?>
=======
        // Função para mostrar/ocultar o seletor de datas
        function toggleDatePicker() {
            const container = document.getElementById('datePickerContainer');
            container.classList.toggle('hidden');
        }

>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
        // Atendimentos por dia
        const labelsDiarios = <?= json_encode($labelsDiarios) ?>;
        const atendimentosData = <?= json_encode($atendimentosPorDia) ?>;
        new Chart(document.getElementById('atendimentosChart'), {
            type: 'line',
            data: {
                labels: labelsDiarios,
                datasets: [{
                    label: 'Atendimentos',
                    data: atendimentosData,
                    borderColor: '#0ea5e9', // sky-500
                    backgroundColor: 'rgba(14, 165, 233, 0.15)',
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#0ea5e9',
                    pointBorderColor: '#fff',
<<<<<<< HEAD
                    pointRadius: 5,
                    pointHoverRadius: 8,
                    borderWidth: 3
=======
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
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
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
<<<<<<< HEAD
                        cornerRadius: 8,
                        callbacks: {
                            title: function(context) {
                                return 'Dia ' + context[0].label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { color: '#475569' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#e2e8f0' },
                        ticks: { 
                            color: '#475569',
                            stepSize: 1
                        }
                    }
=======
                        cornerRadius: 8
                    }
>>>>>>> ec64be0cfdfef1bddf7a259eda062612b5b33469
                }
            }
        });
        <?php endif; ?>

        <?php if (!empty($statusDistribuicao)): ?>
        // Status dos Agendamentos (Gráfico de Rosca)
        const statusLabels = <?= json_encode(array_column($statusDistribuicao, 'status')) ?>;
        const statusData = <?= json_encode(array_column($statusDistribuicao, 'total')) ?>;
        
        // Cores para cada status
        const statusColors = {
            'Pendente': 'rgba(245, 158, 11, 0.8)',    // amber
            'Em Atendimento': 'rgba(14, 165, 233, 0.8)', // sky
            'Finalizado': 'rgba(34, 197, 94, 0.8)',   // green
            'Cancelado': 'rgba(239, 68, 68, 0.8)',   // red
            'Agendado': 'rgba(99, 102, 241, 0.8)'    // indigo
        };
        
        const backgroundColors = statusLabels.map(label => statusColors[label] || 'rgba(148, 163, 184, 0.8)');
        
        new Chart(document.getElementById('statusChart'), {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusData,
                    backgroundColor: backgroundColors,
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
        <?php endif; ?>
        
        // Função para gerar relatório
        function gerarRelatorio(acao) {
            const dataInicio = document.getElementById('rel_data_inicio').value;
            const dataFim = document.getElementById('rel_data_fim').value;
            
            if (!dataInicio || !dataFim) {
                alert('Por favor, selecione as datas de início e fim do período.');
                return;
            }
            
            if (new Date(dataInicio) > new Date(dataFim)) {
                alert('A data inicial não pode ser maior que a data final.');
                return;
            }
            
            const url = `gerar_relatorio.php?data_inicio=${dataInicio}&data_fim=${dataFim}&acao=${acao}`;
            window.open(url, '_blank');
        }
    </script>
</body>

</html>