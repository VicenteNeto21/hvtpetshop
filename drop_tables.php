<?php
$host = 'localhost';
$dbname = 'hvt_petshop';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("DROP TABLE IF EXISTS vendas_itens");
    $pdo->exec("DROP TABLE IF EXISTS vendas");
    $pdo->exec("DROP TABLE IF EXISTS produtos");
    $pdo->exec("DELETE FROM migrations WHERE class LIKE '%AddProdutosTable%' OR class LIKE '%AddVendasTable%' OR class LIKE '%AddVendasItensTable%'");
    echo "Tables dropped successfully\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
