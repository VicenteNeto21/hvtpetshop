<?php
include "../config/config.php";
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado.']);
    exit();
}

header('Content-Type: application/json');

$dataSelecionada = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

// Consulta agendamentos para a data selecionada
$stmtAgendamentos = $pdo->prepare("
    SELECT
        MIN(a.id) as agendamento_id,
        p.id as pet_id,
        t.id as tutor_id,
        p.nome as pet_nome,
        t.nome as tutor_nome,
        DATE_FORMAT(a.data_hora, '%H:%i') AS horario,
        GROUP_CONCAT(DISTINCT s.nome ORDER BY s.nome SEPARATOR ', ') AS servicos,
        CASE
            WHEN SUM(CASE WHEN a.status = 'Em Atendimento' THEN 1 ELSE 0 END) > 0 THEN 'Em Atendimento'
            WHEN SUM(CASE WHEN a.status = 'Pendente' THEN 1 ELSE 0 END) > 0 THEN 'Pendente'
            ELSE MIN(a.status)
        END AS status
    FROM agendamentos a
    JOIN pets p ON a.pet_id = p.id
    JOIN tutores t ON p.tutor_id = t.id
    JOIN servicos s ON a.servico_id = s.id
    WHERE DATE(a.data_hora) = :data_selecionada
    GROUP BY p.id, t.id, p.nome, t.nome, horario
    ORDER BY horario ASC
");
$stmtAgendamentos->execute([':data_selecionada' => $dataSelecionada]);
$agendamentos = $stmtAgendamentos->fetchAll(PDO::FETCH_ASSOC);

ob_start();
if (empty($agendamentos)) {
    echo '<tr><td colspan="6" class="text-slate-500 text-center py-8 border-t-2 border-dashed">Nenhum agendamento para esta data.</td></tr>';
} else {
    foreach ($agendamentos as $ag) {
        echo '<tr class="hover:bg-slate-50">';
        echo '<td class="px-4 py-4 font-medium text-slate-800 whitespace-nowrap">' . htmlspecialchars($ag['horario']) . '</td>';
        echo '<td class="px-4 py-4 text-slate-800 font-semibold"><a href="./pets/visualizar_pet.php?id=' . $ag['pet_id'] . '" class="hover:underline">' . htmlspecialchars($ag['pet_nome']) . '</a></td>';
        echo '<td class="px-4 py-4 text-slate-500 whitespace-nowrap"><a href="./tutores/visualizar_tutor.php?id=' . $ag['tutor_id'] . '" class="hover:underline">' . htmlspecialchars($ag['tutor_nome']) . '</a></td>';
        echo '<td class="px-4 py-4 text-slate-500">' . htmlspecialchars($ag['servicos'] ?: 'N/A') . '</td>';
        echo '<td class="px-4 py-4"><span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold ' .
             ($ag['status'] == 'Pendente' ? 'bg-yellow-100 text-yellow-800' :
             ($ag['status'] == 'Em Atendimento' ? 'bg-blue-100 text-blue-800' :
             ($ag['status'] == 'Finalizado' ? 'bg-green-100 text-green-800' :
             'bg-red-100 text-red-800'))) .
             '">' . htmlspecialchars($ag['status']) . '</span></td>';
        echo '<td class="px-4 py-4 text-center"><div class="flex items-center justify-center gap-3">';
        if ($ag['status'] == 'Pendente') {
            echo '<button onclick="updateAgendamentoStatus(' . $ag['agendamento_id'] . ', \'Em Atendimento\')" class="w-8 h-8 flex items-center justify-center rounded-full text-green-600 hover:bg-green-100 hover:text-green-800 transition" title="Iniciar Atendimento"><i class="fas fa-play-circle fa-lg"></i></button>';
            echo '<a href="./pets/agendamentos/reiditar_agendamento.php?id=' . $ag['agendamento_id'] . '&data=' . $dataSelecionada . '" class="w-8 h-8 flex items-center justify-center rounded-full text-blue-600 hover:bg-blue-100 hover:text-blue-800 transition" title="Editar Agendamento"><i class="fas fa-pencil-alt"></i></a>';
            echo '<a href="javascript:void(0);" onclick="openConfirmationModal(\'Cancelar Agendamento\', \'Tem certeza que deseja cancelar este agendamento?\', \'pets/agendamentos/cancelar_agendamento_action.php?id=' . $ag['agendamento_id'] . '&data=' . $dataSelecionada . '\')" class="w-8 h-8 flex items-center justify-center rounded-full text-red-600 hover:bg-red-100 hover:text-red-800 transition" title="Cancelar"><i class="fas fa-times-circle fa-lg"></i></a>';
        } elseif ($ag['status'] == 'Em Atendimento') {
            echo '<a href="./pets/agendamentos/ficha_atendimento.php?id=' . $ag['agendamento_id'] . '" class="w-8 h-8 flex items-center justify-center rounded-full text-amber-600 hover:bg-amber-100 hover:text-amber-800 transition" title="Preencher Ficha"><i class="fas fa-file-alt fa-lg"></i></a>';
        } elseif ($ag['status'] == 'Finalizado') {
            echo '<a href="./pets/agendamentos/visualizar_ficha.php?id=' . $ag['agendamento_id'] . '" class="w-8 h-8 flex items-center justify-center rounded-full text-green-600 hover:bg-green-100 hover:text-green-800 transition" title="Visualizar Ficha"><i class="fas fa-eye fa-lg"></i></a>';
        } else {
            echo '<span class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400" title="Agendamento Cancelado"><i class="fas fa-ban fa-lg"></i></span>';
        }
        echo '</div></td>';
        echo '</tr>';
    }
}
$tableContent = ob_get_clean();

$novoTitulo = "Agenda para " . date('d/m/Y', strtotime($dataSelecionada));

echo json_encode([
    'tableContent' => $tableContent,
    'novoTitulo' => $novoTitulo
]);
?>