<?php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Verifica se o id do pet foi passado na URL
if (!isset($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit();
}

$petId = $_GET['id'];

try {
    $pdo->beginTransaction();

    // Deleta os agendamentos relacionados ao pet
    $stmtAgendamentos = $pdo->prepare("DELETE FROM agendamentos WHERE pet_id = :pet_id");
    $stmtAgendamentos->bindValue(':pet_id', $petId);
    $stmtAgendamentos->execute();

    // Deleta o pet
    $stmtPet = $pdo->prepare("DELETE FROM pets WHERE id = :id");
    $stmtPet->bindValue(':id', $petId);
    $stmtPet->execute();

    $pdo->commit();
    $_SESSION['mensagem'] = "Pet e todos os seus registros foram excluídos com sucesso.";
    $_SESSION['tipo_mensagem'] = "success";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensagem'] = "Erro ao excluir o pet: " . $e->getMessage();
    $_SESSION['tipo_mensagem'] = "error";
}

// Redireciona para o dashboard após a exclusão
header("Location: ../dashboard.php");
exit();
?>
