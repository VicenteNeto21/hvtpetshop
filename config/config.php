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
$host     = "localhost"; // Ex: sql101.epizy.com
$dbname   = "testehvt"; // Ex: epiz_12345678_hvtpetshop
$username = "root"; // Ex: epiz_12345678
$password = ""; // A senha que você definiu

$pdo = null; // Inicializa a variável para verificação posterior
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    // Em caso de erro, o script de login irá capturar que $pdo é null e retornar um JSON de erro.
    // Você pode verificar o log de erros do servidor para ver a mensagem real.
}
?>
