<?php
session_start();

// Destruir todas as variáveis da sessão
$_SESSION = array();

// Se a sessão for registrada, destruir a sessão
if (session_id() != "" || isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 42000, '/');
    session_destroy();
}

// Redirecionar para a página de login
header("Location: ../login.html");
exit();
?>
