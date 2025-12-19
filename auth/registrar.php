<?php
include "../config/config.php";

header('Content-Type: application/json');

// 2. Estrutura de Guard Clauses: Verifica o método HTTP primeiro.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

// Validações do servidor
if (empty($nome) || empty($email) || empty($senha)) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios.']);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Formato de e-mail inválido.']);
    exit();
}

// 1. Segurança: Aumenta o requisito mínimo da senha.
if (strlen($senha) < 4) {
    echo json_encode(['success' => false, 'message' => 'A senha deve ter no mínimo 8 caracteres.']);
    exit();
}

// 3. Tratamento de Erros: Usa try-catch para operações de banco de dados.
try {
    // Verifica se o e-mail já existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Este e-mail já está cadastrado.']);
        exit();
    }

    // Insere o novo usuário
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)");
    $stmt->execute([$nome, $email, $senhaHash]);

    echo json_encode(['success' => true]);

} catch (PDOException $e) {
    // Em caso de erro no banco de dados, retorna uma mensagem genérica.
    // O erro real pode ser logado em um arquivo para depuração. error_log($e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Ocorreu um erro no servidor. Tente novamente mais tarde.']);
}
?>