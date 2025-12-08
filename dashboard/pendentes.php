<?php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Consulta atendimentos pendentes (status = "Pendente")
$stmtPendentes = $pdo->prepare("
    SELECT
        MIN(a.id) as agendamento_id,
        p.id as pet_id,
        t.id as tutor_id,
        p.nome as pet_nome,
        t.nome as tutor_nome,
        DATE_FORMAT(a.data_hora, '%d/%m/%Y') AS data_agendamento,
        DATE_FORMAT(a.data_hora, '%H:%i') AS horario,
        a.data_hora as data_hora_raw,
        GROUP_CONCAT(DISTINCT s.nome ORDER BY s.nome SEPARATOR ', ') AS servicos
    FROM agendamentos a
    JOIN pets p ON a.pet_id = p.id
    JOIN tutores t ON p.tutor_id = t.id
    JOIN servicos s ON a.servico_id = s.id
    WHERE a.status = 'Pendente'
    GROUP BY p.id, t.id, p.nome, t.nome, a.data_hora
    ORDER BY a.data_hora ASC
");
$stmtPendentes->execute();
$pendentes = $stmtPendentes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendentes para Encerrar - CereniaPet</title>
    <link rel="icon" type="image/x-icon" href="../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <?php
    $path_prefix = '../';
    include '../components/navbar.php';
    ?>
    <?php include '../components/toast.php'; ?>

    <main class="flex-1 w-full p-4 md:p-6 lg:p-8">
        <!-- Cabeçalho -->
        <div class="mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
                        <i class="fa-solid fa-hourglass-half text-orange-500"></i>
                        Pendentes para Encerrar
                    </h1>
                    <p class="text-slate-500 mt-1">Atendimentos em andamento que precisam ser finalizados.</p>
                </div>
                <a href="../dashboard.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 py-2 px-4 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                    <i class="fa fa-arrow-left"></i> Voltar ao Dashboard
                </a>
            </div>
        </div>

        <!-- Tabela de Pendentes -->
        <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b-2 border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Data</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Horário</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Pet</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Tutor</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Serviços</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($pendentes)): ?>
                            <tr>
                                <td colspan="6" class="text-slate-500 text-center py-12">
                                    <i class="fa-solid fa-check-circle text-green-500 text-4xl mb-3 block"></i>
                                    <p class="text-lg font-medium">Nenhum atendimento pendente!</p>
                                    <p class="text-sm mt-1">Todos os atendimentos foram finalizados.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendentes as $pendente): ?>
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-4 font-medium text-slate-800 whitespace-nowrap">
                                    <?= htmlspecialchars($pendente['data_agendamento']) ?>
                                </td>
                                <td class="px-4 py-4 text-slate-600 whitespace-nowrap">
                                    <?= htmlspecialchars($pendente['horario']) ?>
                                </td>
                                <td class="px-4 py-4 text-slate-800 font-semibold">
                                    <a href="../pets/visualizar_pet.php?id=<?= $pendente['pet_id'] ?>" class="hover:underline hover:text-blue-600">
                                        <?= htmlspecialchars($pendente['pet_nome']) ?>
                                    </a>
                                </td>
                                <td class="px-4 py-4 text-slate-500 whitespace-nowrap">
                                    <a href="../tutores/visualizar_tutor.php?id=<?= $pendente['tutor_id'] ?>" class="hover:underline hover:text-blue-600">
                                        <?= htmlspecialchars($pendente['tutor_nome']) ?>
                                    </a>
                                </td>
                                <td class="px-4 py-4 text-slate-500">
                                    <?= htmlspecialchars($pendente['servicos'] ?: 'N/A') ?>
                                </td>
                                <td class="px-4 py-4 text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <!-- Preencher Ficha (Finalizar) -->
                                        <a href="../pets/agendamentos/ficha_atendimento.php?id=<?= $pendente['agendamento_id'] ?>" 
                                           class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2 text-xs"
                                           title="Preencher Ficha e Finalizar">
                                            <i class="fas fa-file-alt"></i> Finalizar
                                        </a>
                                        <!-- Cancelar -->
                                        <a href="javascript:void(0);" 
                                           onclick="openConfirmationModal('Cancelar Atendimento', 'Tem certeza que deseja cancelar este atendimento?', '../pets/agendamentos/cancelar_agendamento_action.php?id=<?= $pendente['agendamento_id'] ?>&redirect=pendentes')"
                                           class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2 text-xs"
                                           title="Cancelar Atendimento">
                                            <i class="fas fa-times"></i> Cancelar
                                        </a>
                                        <!-- Ver Pet -->
                                        <a href="../pets/visualizar_pet.php?id=<?= $pendente['pet_id'] ?>" 
                                           class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-1.5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2 text-xs"
                                           title="Ver Detalhes do Pet">
                                            <i class="fas fa-eye"></i> Ver Pet
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if (!empty($pendentes)): ?>
            <div class="mt-6 pt-4 border-t border-slate-200">
                <p class="text-sm text-slate-500">
                    <i class="fa-solid fa-info-circle text-blue-500 mr-1"></i>
                    Total de <?= count($pendentes) ?> atendimento(s) pendente(s) para encerrar.
                </p>
            </div>
            <?php endif; ?>
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
</body>
</html>
