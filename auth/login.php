<?php
include "../config/config.php";
session_start();

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"] ?? '');
    $senha = trim($_POST["senha"] ?? '');

    if (empty($email) || empty($senha)) {
        echo json_encode(['success' => false, 'message' => 'Campos obrigatórios não preenchidos.']);
        exit();
    }

    $stmt = $pdo->prepare("SELECT id, nome, senha FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario && password_verify($senha, $usuario["senha"])) {
        // Login bem-sucedido
        $_SESSION["usuario_id"] = $usuario["id"];
        $_SESSION["usuario_nome"] = $usuario["nome"];
    $_SESSION['mensagem'] = "Login efetuado com sucesso! Bem-vindo(a), " . htmlspecialchars($usuario["nome"]) . "!";
    $_SESSION['tipo_mensagem'] = "success";
        echo json_encode(['success' => true]);
    } else {
        // Login falhou
        echo json_encode(['success' => false, 'message' => 'Credenciais inválidas.']);
    }
}
?>
