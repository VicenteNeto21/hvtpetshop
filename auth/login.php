<?php
/**
 * Script de Autenticação de Usuário
 *
 * Este script foi refeito para ser mais robusto e seguro.
 * 1. Sempre retorna uma resposta JSON válida.
 * 2. Verifica se a conexão com o banco de dados foi bem-sucedida.
 * 3. Implementa um limite de tentativas de login para prevenir ataques de força bruta.
 * 4. Regenera o ID da sessão no login para prevenir ataques de fixação de sessão.
 */

// Define o cabeçalho da resposta como JSON
header('Content-Type: application/json');

// Inclui a configuração do banco de dados
include "../config/config.php";

// Inicia a sessão se não houver uma ativa
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// --- Validações Iniciais ---

// 1. Verifica se a conexão com o banco de dados foi estabelecida em config.php
if ($pdo === null) {
    echo json_encode(['success' => false, 'message' => 'Erro crítico: Não foi possível conectar ao banco de dados. Verifique as credenciais no arquivo .env.']);
    exit();
}

// 2. Garante que o método da requisição seja POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido.']);
    exit();
}

// 3. Pega e valida os dados de entrada
$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

if (empty($email) || empty($senha)) {
    echo json_encode(['success' => false, 'message' => 'E-mail e senha são obrigatórios.']);
    exit();
}

// --- Lógica de Autenticação ---

try {
    $stmt = $pdo->prepare("SELECT id, nome, senha, tentativas_login_falhas, bloqueado_ate FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        // Usuário não encontrado, mas retornamos uma mensagem genérica por segurança.
        echo json_encode(['success' => false, 'message' => 'E-mail ou senha incorretos.']);
        exit();
    }

    // Verifica se a conta está bloqueada
    if ($usuario['bloqueado_ate'] && new DateTime() < new DateTime($usuario['bloqueado_ate'])) {
        echo json_encode(['success' => false, 'message' => 'Conta temporariamente bloqueada por excesso de tentativas.']);
        exit();
    }

    // Verifica a senha
    if (password_verify($senha, $usuario['senha'])) {
        // SUCESSO NO LOGIN
        $pdo->prepare("UPDATE usuarios SET tentativas_login_falhas = 0, bloqueado_ate = NULL WHERE id = ?")->execute([$usuario['id']]);
        session_regenerate_id(true); // Previne session fixation
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        echo json_encode(['success' => true]);
    } else {
        // FALHA NO LOGIN
        $tentativas = $usuario['tentativas_login_falhas'] + 1;
        $bloqueado_ate = $usuario['bloqueado_ate'];
        $maxTentativas = 5;

        if ($tentativas >= $maxTentativas) {
            $bloqueado_ate = (new DateTime('+15 minutes'))->format('Y-m-d H:i:s');
        }

        $pdo->prepare("UPDATE usuarios SET tentativas_login_falhas = ?, bloqueado_ate = ? WHERE id = ?")->execute([$tentativas, $bloqueado_ate, $usuario['id']]);
        
        $mensagem = $bloqueado_ate ? 'Conta bloqueada por 15 minutos.' : 'E-mail ou senha incorretos.';
        echo json_encode(['success' => false, 'message' => $mensagem]);
    }

} catch (PDOException $e) {
    // Captura qualquer outro erro de banco de dados
    // error_log('Erro de login: ' . $e->getMessage()); // Opcional: logar o erro real
    echo json_encode(['success' => false, 'message' => 'Ocorreu um erro no servidor. Tente novamente.']);
}
?>
