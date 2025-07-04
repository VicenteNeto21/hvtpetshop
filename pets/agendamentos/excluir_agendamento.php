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
$query = "SELECT pet_id FROM agendamentos WHERE id = :id";
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

try {
    // Excluir fichas e dependências antes do agendamento
    $stmt = $pdo->prepare("DELETE FROM ficha_servicos_realizados WHERE ficha_id IN (SELECT id FROM fichas_petshop WHERE agendamento_id = :id)");
    $stmt->bindValue(':id', $agendamentoId);
    $stmt->execute();

    $stmt = $pdo->prepare("DELETE FROM ficha_observacoes WHERE ficha_id IN (SELECT id FROM fichas_petshop WHERE agendamento_id = :id)");
    $stmt->bindValue(':id', $agendamentoId);
    $stmt->execute();

    $stmt = $pdo->prepare("DELETE FROM fichas_petshop WHERE agendamento_id = :id");
    $stmt->bindValue(':id', $agendamentoId);
    $stmt->execute();

    // Excluir o agendamento
    $queryDelete = "DELETE FROM agendamentos WHERE id = :id";
    $stmtDelete = $pdo->prepare($queryDelete);
    $stmtDelete->bindValue(':id', $agendamentoId);
    $stmtDelete->execute();

    $_SESSION['mensagem'] = "Agendamento excluído com sucesso!";
    $_SESSION['tipo_mensagem'] = "success";
    header("Location: ../../visualizar_pet.php?id=$petId");
    exit();
} catch (Exception $e) {
    $_SESSION['mensagem'] = "Erro ao excluir agendamento: " . $e->getMessage();
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../../visualizar_pet.php?id=$petId");
    exit();
}
?>
