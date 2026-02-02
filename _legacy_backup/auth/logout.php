<?php
// 1. Inicia a sessão para poder manipulá-la.
// É importante que esta seja a primeira coisa a ser feita.
session_start();

// 2. Limpa todas as variáveis da sessão.
// Isso remove todos os dados armazenados na sessão atual (ex: usuario_id).
$_SESSION = array();

// 3. Destrói o cookie da sessão no lado do cliente.
// Esta é a forma mais robusta, pois usa os mesmos parâmetros com os quais o cookie foi criado.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// 4. Finalmente, destrói a sessão no lado do servidor.
session_destroy();

// 5. Redireciona o usuário para a página de login com uma mensagem de sucesso.
header("Location: ../login.html");
exit();
?>
