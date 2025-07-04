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

// Deleta os agendamentos relacionados ao pet
$queryAgendamentos = "DELETE FROM agendamentos WHERE pet_id = :pet_id";
$stmtAgendamentos = $pdo->prepare($queryAgendamentos);
$stmtAgendamentos->bindValue(':pet_id', $petId);
$stmtAgendamentos->execute();

// Deleta o pet
$queryPet = "DELETE FROM pets WHERE id = :id";
$stmtPet = $pdo->prepare($queryPet);
$stmtPet->bindValue(':id', $petId);
$stmtPet->execute();

// Redireciona para o dashboard após a exclusão
header("Location: ../dashboard.php");
exit();
?>
