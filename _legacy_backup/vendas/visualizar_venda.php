<?php
// filepath: c:\xampp\htdocs\dashboard\hvt_petshop\vendas\visualizar_venda.php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Recebe o ID da venda
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if (!$id) {
    header("Location: ../caixa.php");
    exit();
}

// Busca os dados da venda
$stmt = $pdo->prepare("SELECT v.*, t.nome as cliente 
                       FROM vendas v 
                       LEFT JOIN tutores t ON v.tutor_id = t.id 
                       WHERE v.id = ?");
$stmt->execute([$id]);
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    $_SESSION['mensagem'] = "Venda não encontrada!";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../caixa.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Detalhes da Venda - HVTPETSHOP</title>
    <link rel="icon" type="image/x-icon" href="../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">
    <nav class="w-full bg-white/90 shadow flex items-center justify-between px-6 py-3">
        <div class="flex items-center gap-3">
            <img src="../icons/pet.jpg" alt="Logo Petshop" class="w-10 h-10 rounded-full shadow">
            <span class="text-2xl font-bold text-blue-700 tracking-tight">HVTPETSHOP</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="../dashboard.php" class="text-blue-600 hover:text-blue-800 font-semibold transition"><i class="fa fa-home mr-1"></i>Dashboard</a>
            <a href="../caixa.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold shadow flex items-center gap-2 transition">
                <i class="fa fa-cash-register"></i> PDV
            </a>
            <a href="../tutores/listar_tutores.php" class="text-blue-500 hover:text-blue-700 font-semibold transition"><i class="fa fa-users mr-1"></i>Tutores</a>
            <a href="../auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition"><i class="fa fa-sign-out-alt mr-1"></i>Sair</a>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-md mx-auto mt-10 p-4">
        <div class="bg-white/90 p-8 rounded-2xl shadow-xl animate-fade-in">
            <h1 class="text-2xl font-bold text-blue-700 mb-6 flex items-center gap-2">
                <i class="fa fa-receipt"></i> Detalhes da Venda
            </h1>
            <div class="mb-4">
                <div class="text-gray-600 text-sm mb-1"><i class="fa fa-hashtag text-blue-400"></i> <b>ID:</b> <?= $venda['id'] ?></div>
                <div class="text-gray-600 text-sm mb-1"><i class="fa fa-calendar-alt text-blue-400"></i> <b>Data/Hora:</b> <?= date('d/m/Y H:i', strtotime($venda['data_hora'])) ?></div>
                <div class="text-gray-600 text-sm mb-1"><i class="fa fa-user text-blue-400"></i> <b>Cliente:</b> <?= $venda['cliente'] ? htmlspecialchars($venda['cliente']) : '<span class="italic text-gray-400">Avulsa</span>' ?></div>
                <div class="text-gray-600 text-sm mb-1"><i class="fa fa-money-bill-wave text-green-500"></i> <b>Valor Total:</b> <span class="font-bold text-green-700">R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></span></div>
            </div>
            <div class="flex gap-3 mt-8 justify-center">
                <a href="../caixa.php" class="bg-gray-300 text-gray-700 px-8 py-2 rounded-lg hover:bg-gray-400 font-semibold shadow transition flex items-center gap-2">
                    <i class="fa fa-arrow-left"></i> Voltar
                </a>
            </div>
        </div>
    </main>
</body>
</html>