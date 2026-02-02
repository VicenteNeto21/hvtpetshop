<?php
include "../config/config.php";
header('Content-Type: application/json');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$servicos = [];
if ($id) {
    $sql = "SELECT nome, preco FROM servico WHERE pet_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    $servicos = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
echo json_encode($servicos);