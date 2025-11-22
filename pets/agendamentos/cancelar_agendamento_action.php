<?php
include "../../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit();
}

$agendamento_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$data_retorno = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

if ($agendamento_id > 0) {
    try {
        $pdo->beginTransaction();

        // 1. Busca o pet_id e data_hora do agendamento a ser cancelado
        $stmtInfo = $pdo->prepare("SELECT pet_id, data_hora FROM agendamentos WHERE id = :id");
        $stmtInfo->execute([':id' => $agendamento_id]);
        $agendamentoInfo = $stmtInfo->fetch(PDO::FETCH_ASSOC);

        if ($agendamentoInfo) {
            // 2. Atualiza o status de TODOS os serviços para este pet neste horário
            $stmtUpdate = $pdo->prepare("UPDATE agendamentos SET status = 'Cancelado' WHERE pet_id = :pet_id AND data_hora = :data_hora");
            $stmtUpdate->execute([
                ':pet_id' => $agendamentoInfo['pet_id'],
                ':data_hora' => $agendamentoInfo['data_hora']
            ]);
        }
        $pdo->commit();

        $_SESSION['mensagem'] = "Agendamento cancelado com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
    } catch (PDOException $e) {
        $_SESSION['mensagem'] = "Erro ao cancelar o agendamento: " . $e->getMessage();
        $_SESSION['tipo_mensagem'] = "error";
    }
} else {
    $_SESSION['mensagem'] = "ID de agendamento inválido.";
    $_SESSION['tipo_mensagem'] = "error";
}

// Redireciona de volta para o dashboard com a data correta
header("Location: ../../dashboard.php?data=" . $data_retorno);
exit();