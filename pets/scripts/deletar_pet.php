<?php
include "../../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Verifica se o ID do pet foi passado
if (!isset($_GET['id'])) {
    header("Location: ../../dashboard.php");
    exit();
}

$petId = $_GET['id'];

// Verifica se o pet existe
$queryCheck = "SELECT id FROM pets WHERE id = :id";
$stmtCheck = $pdo->prepare($queryCheck);
$stmtCheck->bindValue(':id', $petId);
$stmtCheck->execute();

if (!$stmtCheck->fetch(PDO::FETCH_ASSOC)) {
    echo "<script>alert('Pet não encontrado!'); window.location.href='../../dashboard.php';</script>";
    exit();
}

// Exclui os agendamentos relacionados ao pet
$queryDeleteAgendamentos = "DELETE FROM agendamentos WHERE pet_id = :id";
$stmtDeleteAgendamentos = $pdo->prepare($queryDeleteAgendamentos);
$stmtDeleteAgendamentos->bindValue(':id', $petId);
$stmtDeleteAgendamentos->execute();

// Exclui o pet
$queryDeletePet = "DELETE FROM pets WHERE id = :id";
$stmtDeletePet = $pdo->prepare($queryDeletePet);
$stmtDeletePet->bindValue(':id', $petId);
$stmtDeletePet->execute();

// Redireciona com sucesso
echo "<script>alert('Pet excluído com sucesso!'); window.location.href='../../dashboard.php';</script>";
exit();
?>
