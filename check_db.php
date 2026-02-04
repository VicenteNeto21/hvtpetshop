<?php
/**
 * Script de Diagnóstico e Limpeza - CereniaPet
 * Upload este arquivo para a raiz (htdocs) e acesse: https://hvtpetshop.rf.gd/check_db.php (ou similar)
 */

// 1. Tentar limpar o cache do CodeIgniter
$cachePath = __DIR__ . '/writable/cache/';
$files = glob($cachePath . '*');
$count = 0;
foreach($files as $file){
    if(is_file($file) && basename($file) !== 'index.html' && basename($file) !== '.gitignore') {
        unlink($file);
        $count++;
    }
}

// 2. Carregar o framework para ver as configs reais
require __DIR__ . '/app/Config/Paths.php';
$paths = new \Config\Paths();
require $paths->systemDirectory . '/Config/DotEnv.php';

// Tentar carregar .env ou env
if (file_exists(__DIR__ . '/.env')) {
    (new \CodeIgniter\Config\DotEnv(__DIR__))->load();
    echo "✅ Arquivo .env encontrado e carregado.<br>";
} elseif (file_exists(__DIR__ . '/env')) {
    (new \CodeIgniter\Config\DotEnv(__DIR__, 'env'))->load();
    echo "⚠️ Arquivo 'env' (sem ponto) encontrado e carregado.<br>";
} else {
    echo "❌ Nenhum arquivo de ambiente (.env ou env) encontrado na raiz.<br>";
}

// 3. Mostrar a baseURL que o sistema está usando agora
require __DIR__ . '/app/Config/App.php';
$appConfig = new \Config\App();

echo "<h2>Relatório de Sistema:</h2>";
echo "<b>Base URL Detectada:</b> <code style='background:#eee;padding:2px 5px;'>" . $appConfig->baseURL . "</code><br>";
echo "<b>Ambiente (CI_ENVIRONMENT):</b> " . (getenv('CI_ENVIRONMENT') ?: 'Não definido (usando default)') . "<br>";
echo "<b>Arquivos de cache apagados:</b> " . $count . "<br>";

echo "<hr>";
echo "<h3>Instruções:</h3>";
echo "1. Se a Base URL acima ainda for 'localhost', verifique se você subiu o arquivo <code>app/Config/App.php</code> corretamente.<br>";
echo "2. Se você usa um arquivo chamado <code>env</code> (sem ponto), renomeie-o para <code>.env</code> (com ponto) se possível.<br>";
echo "3. <b>IMPORTANTE:</b> Limpe o cache do seu navegador (Ctrl+F5) após rodar este script.";
