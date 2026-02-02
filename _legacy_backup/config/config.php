<?php

// Inclui o autoloader do Composer para outras bibliotecas (como DomPDF)
require_once dirname(__DIR__) . '/vendor/autoload.php';

/**
 * Configuração do Banco de Dados para Epizy.com (InfinityFree)
 * 
 * Como a biblioteca phpdotenv pode causar conflitos de versão do PHP,
 * as credenciais são definidas diretamente aqui.
 * 
 * IMPORTANTE: Substitua os valores abaixo pelas suas credenciais reais do banco de dados
 * fornecidas no painel de controle da sua hospedagem.
 */
$host     = "sql308.infinityfree.com"; // Ex: sql101.epizy.com
$dbname   = "if0_39359166_hvt_petshop_oficial"; // Ex: epiz_12345678_hvtpetshop
$username = "if0_39359166"; // Ex: epiz_12345678
$password = "K3JCBE3vB4XX"; // A senha que você definiu

$pdo = null; // Inicializa a variável para verificação posterior
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Em caso de erro, o script de login irá capturar que $pdo é null e retornar um JSON de erro.
    // Você pode verificar o log de erros do servidor para ver a mensagem real.
}
?>
