<?php
include "../config/config.php";
session_start();

header('Content-Type: application/json');

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'Não autenticado.']);
    exit();
}

// Verifica se é admin
$stmtAdmin = $pdo->prepare("SELECT tipo FROM usuarios WHERE id = ?");
$stmtAdmin->execute([$_SESSION['usuario_id']]);
$usuarioAtual = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

if ($usuarioAtual['tipo'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Acesso negado.']);
    exit();
}

// Valida os dados
$userId = $_POST['user_id'] ?? null;
$novoStatus = $_POST['status'] ?? null;
$novoTipo = $_POST['tipo'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'ID do usuário não informado.']);
    exit();
}

// Não permite alterar o próprio usuário
if ($userId == $_SESSION['usuario_id']) {
    echo json_encode(['success' => false, 'message' => 'Você não pode alterar suas próprias configurações.']);
    exit();
}

try {
    // Alteração de STATUS
    if ($novoStatus && in_array($novoStatus, ['aprovado', 'rejeitado', 'pendente'])) {
        $stmt = $pdo->prepare("UPDATE usuarios SET status = ? WHERE id = ?");
        $stmt->execute([$novoStatus, $userId]);
        
        $mensagem = $novoStatus === 'aprovado' ? 'Usuário aprovado com sucesso!' : 'Acesso do usuário revogado.';
        echo json_encode(['success' => true, 'message' => $mensagem]);
        exit();
    }
    
    // Alteração de TIPO (admin/funcionario)
    if ($novoTipo && in_array($novoTipo, ['admin', 'funcionario'])) {
        $stmt = $pdo->prepare("UPDATE usuarios SET tipo = ? WHERE id = ?");
        $stmt->execute([$novoTipo, $userId]);
        
        $mensagem = $novoTipo === 'admin' ? 'Usuário promovido a Administrador!' : 'Usuário alterado para Funcionário.';
        echo json_encode(['success' => true, 'message' => $mensagem]);
        exit();
    }
    
    echo json_encode(['success' => false, 'message' => 'Nenhuma ação válida informada.']);
    
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar usuário.']);
}
?>
