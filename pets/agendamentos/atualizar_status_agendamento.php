<?php
include "../../config/config.php";
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $agendamentoId = $_POST['agendamento_id'] ?? null;
    $newStatus = $_POST['new_status'] ?? null;

    if (!$agendamentoId || !$newStatus) {
        echo json_encode(['success' => false, 'message' => 'Dados insuficientes.']);
        exit();
    }

    // Validação básica do status
    $allowedStatuses = ['Pendente', 'Em Atendimento', 'Finalizado', 'Cancelado'];
    if (!in_array($newStatus, $allowedStatuses)) {
        echo json_encode(['success' => false, 'message' => 'Status inválido.']);
        exit();
    }

    try {
        $pdo->beginTransaction();

        // Get pet_id and data_hora for the group of services
        $stmt = $pdo->prepare("SELECT pet_id, data_hora FROM agendamentos WHERE id = :agendamento_id");
        $stmt->bindValue(':agendamento_id', $agendamentoId, PDO::PARAM_INT);
        $stmt->execute();
        $agendamentoInfo = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$agendamentoInfo) {
            throw new Exception("Agendamento não encontrado.");
        }

        $petId = $agendamentoInfo['pet_id'];
        $dataHora = $agendamentoInfo['data_hora'];

        // Update status for all services in this group
        $stmtUpdate = $pdo->prepare("UPDATE agendamentos SET status = :new_status WHERE pet_id = :pet_id AND data_hora = :data_hora");
        $stmtUpdate->bindValue(':new_status', $newStatus);
        $stmtUpdate->bindValue(':pet_id', $petId, PDO::PARAM_INT);
        $stmtUpdate->bindValue(':data_hora', $dataHora);
        $stmtUpdate->execute();

        $pdo->commit();
        echo json_encode(['success' => true, 'message' => 'Status atualizado com sucesso!', 'new_status' => $newStatus]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['success' => false, 'message' => 'Erro ao atualizar status: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
}
?>