<?php
include "../config/config.php";
session_start();

header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit();
}

try {
    // A versão aqui DEVE ser a mesma definida em dashboard.php
    $versao_atual_aviso = '1.1.8';
    $usuario_id = $_SESSION['usuario_id'];

    // Atualiza a coluna no banco de dados para o usuário logado
    $stmt = $pdo->prepare("UPDATE usuarios SET versao_aviso_visto = ? WHERE id = ?");
    $stmt->execute([$versao_atual_aviso, $usuario_id]);

    // Define a variável de sessão para evitar uma nova consulta ao DB na mesma sessão
    $_SESSION['aviso_visto_versao'] = $versao_atual_aviso;

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao salvar preferência: ' . $e->getMessage()]);
}
?>