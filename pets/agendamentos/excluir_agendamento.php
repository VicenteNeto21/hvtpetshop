<?php
include "../../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit();
}

// Verifica se o ID do agendamento foi fornecido
if (!isset($_GET['id'])) {
    header("Location: ../../dashboard.php");
    exit();
}

$agendamentoId = $_GET['id'];

// Consulta para verificar se o agendamento existe
$query = "SELECT pet_id, data_hora FROM agendamentos WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':id', $agendamentoId);
$stmt->execute();
$agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

// Se o agendamento não for encontrado, redireciona
if (!$agendamento) {
    $_SESSION['mensagem'] = "Agendamento não encontrado!";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../../dashboard.php");
    exit();
}

$petId = $agendamento['pet_id'];
$dataHora = $agendamento['data_hora'];

try {
    $pdo->beginTransaction();

    // 1. Encontra todos os IDs de agendamento para este grupo (mesmo pet, mesma data/hora)
    $stmtAgendamentosGrupo = $pdo->prepare("SELECT id FROM agendamentos WHERE pet_id = :pet_id AND data_hora = :data_hora");
    $stmtAgendamentosGrupo->execute([':pet_id' => $petId, ':data_hora' => $dataHora]);
    $agendamentoIds = $stmtAgendamentosGrupo->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($agendamentoIds)) {
        // Cria uma string de placeholders (?,?,?) para a cláusula IN
        $placeholders = implode(',', array_fill(0, count($agendamentoIds), '?'));

        // 2. Deleta as fichas e suas dependências (o ON DELETE CASCADE no DB cuida disso)
        $stmtDeleteFichas = $pdo->prepare("DELETE FROM fichas_petshop WHERE agendamento_id IN ($placeholders)");
        $stmtDeleteFichas->execute($agendamentoIds);

        // 3. Deleta todos os agendamentos do grupo
        $stmtDeleteAgendamentos = $pdo->prepare("DELETE FROM agendamentos WHERE id IN ($placeholders)");
        $stmtDeleteAgendamentos->execute($agendamentoIds);
    }

    $pdo->commit();

    $_SESSION['mensagem'] = "Agendamento excluído com sucesso!";
    $_SESSION['tipo_mensagem'] = "success";
    header("Location: ../visualizar_pet.php?id=$petId");
    exit();
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensagem'] = "Erro ao excluir agendamento: " . $e->getMessage();
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../visualizar_pet.php?id=$petId");
    exit();
}
?>
