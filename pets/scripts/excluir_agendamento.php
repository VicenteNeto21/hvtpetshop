<?php
include "../../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Verifica se o id do agendamento foi passado na URL
if (!isset($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit();
}

$agendamentoId = $_GET['id'];

// Deleta o agendamento
$query = "DELETE FROM agendamentos WHERE id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':id', $agendamentoId);
$stmt->execute();

// Redireciona de volta para a página do pet
header("Location: ../visualizar_pet.php?id=" . $_GET['pet_id']);
exit();
?>
