<?php
include "../config/config.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST["nome"]);
    $email = trim($_POST["email"]);
    $senha = password_hash($_POST["senha"], PASSWORD_DEFAULT);

    // Verifica se o e-mail já está cadastrado
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    
    if ($stmt->rowCount() > 0) {
        echo "E-mail já cadastrado!";
        exit();
    }

    // Insere o novo usuário
    $sql = "INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    if ($stmt->execute([$nome, $email, $senha])) {
        header("Location: ../login.html");
        exit();
    } else {
        echo "Erro ao registrar.";
    }
}
?>
