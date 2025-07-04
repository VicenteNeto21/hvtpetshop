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
    $_SESSION['mensagem'] = "Agendamento não informado!";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../../dashboard.php");
    exit();
}

$agendamentoId = $_GET['id'];

// Busca o agendamento para pegar pet_id e data_hora
$stmt = $pdo->prepare("SELECT pet_id, data_hora FROM agendamentos WHERE id = ?");
$stmt->execute([$agendamentoId]);
$agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agendamento) {
    $_SESSION['mensagem'] = "Agendamento não encontrado!";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../../dashboard.php");
    exit();
}

$petId = $agendamento['pet_id'];
$dataHora = $agendamento['data_hora'];

// Cancela todos os agendamentos do mesmo grupo (pet e data/hora)
$stmt = $pdo->prepare("UPDATE agendamentos SET status = 'Cancelado' WHERE pet_id = ? AND data_hora = ?");
$stmt->execute([$petId, $dataHora]);

$_SESSION['mensagem'] = "Todos os agendamentos do grupo foram cancelados com sucesso!";
$_SESSION['tipo_mensagem'] = "success";

// Redireciona para a tela do pet
header("Location: ../../visualizar_pet.php?id=" . $petId);
exit();