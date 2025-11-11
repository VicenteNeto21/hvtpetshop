<?php
include "../../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit();
}

// Verifica se o ID do agendamento e do pet foram fornecidos
if (!isset($_GET['id']) || !isset($_GET['pet_id'])) {
    $_SESSION['mensagem'] = "Informações insuficientes para o cancelamento.";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../../dashboard.php");
    exit();
}

$agendamentoId = $_GET['id'];
$petId = $_GET['pet_id'];

try {
    // Busca a data_hora do agendamento principal para cancelar o grupo
    $stmt = $pdo->prepare("SELECT data_hora FROM agendamentos WHERE id = ?");
    $stmt->execute([$agendamentoId]);
    $data_hora = $stmt->fetchColumn();

    // Cancela todos os agendamentos do mesmo grupo (pet e data/hora)
    $stmtUpdate = $pdo->prepare("UPDATE agendamentos SET status = 'Cancelado' WHERE pet_id = ? AND data_hora = ?");
    $stmtUpdate->execute([$petId, $data_hora]);

    $_SESSION['mensagem'] = "Agendamento(s) cancelado(s) com sucesso!";
    $_SESSION['tipo_mensagem'] = "success";
} catch (PDOException $e) {
    $_SESSION['mensagem'] = "Erro ao cancelar agendamento: " . $e->getMessage();
    $_SESSION['tipo_mensagem'] = "error";
}

header("Location: ../visualizar_pet.php?id=" . $petId);
exit();
?>