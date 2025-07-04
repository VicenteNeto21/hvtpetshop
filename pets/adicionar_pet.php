<?php
// filepath: c:\xampp\htdocs\dashboard\hvt_petshop\pets\adicionar_pet.php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Recebe o tutor_id por GET (opcional)
$tutor_id = isset($_GET['tutor_id']) ? intval($_GET['tutor_id']) : null;

// Busca dados do tutor se tutor_id informado
$tutor = null;
if ($tutor_id) {
    $stmt = $pdo->prepare("SELECT id, nome FROM tutores WHERE id = ?");
    $stmt->execute([$tutor_id]);
    $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Cadastro do pet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $especie = $_POST['especie'];
    $raca = $_POST['raca'];
    $tutor_id_post = $_POST['tutor_id'];

    if (!$nome || !$especie || !$tutor_id_post) {
        $_SESSION['mensagem'] = "Preencha todos os campos obrigatórios!";
        $_SESSION['tipo_mensagem'] = "error";
    } else {
        $stmt = $pdo->prepare("INSERT INTO pets (nome, especie, raca, tutor_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nome, $especie, $raca, $tutor_id_post]);
        $_SESSION['mensagem'] = "Pet cadastrado com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
        header("Location: ../tutores/visualizar_tutor.php?id=" . $tutor_id_post);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Adicionar Pet - HVTPETSHOP</title>
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
            <a href="../tutores/listar_tutores.php" class="text-blue-500 hover:text-blue-700 font-semibold transition"><i class="fa fa-users mr-1"></i>Tutores</a>
            <a href="../auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition"><i class="fa fa-sign-out-alt mr-1"></i>Sair</a>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-xl mx-auto mt-10 p-4">
        <div class="bg-white/90 p-8 rounded-2xl shadow-xl animate-fade-in">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-blue-700 mb-1 flex items-center justify-center gap-2">
                    <i class="fa fa-paw"></i> Adicionar Pet
                </h1>
                <div class="text-gray-500 text-sm">
                    <?= $tutor ? "Para o tutor <b>" . htmlspecialchars($tutor['nome']) . "</b>" : "Selecione o tutor do pet abaixo" ?>
                </div>
            </div>
            <?php if (isset($_SESSION['mensagem'])): ?>
                <div class="mb-4 p-3 rounded <?= $_SESSION['tipo_mensagem'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <?= htmlspecialchars($_SESSION['mensagem']) ?>
                    <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
                </div>
            <?php endif; ?>
            <form method="POST" class="space-y-6">
                <?php if (!$tutor): ?>
                    <div>
                        <label class="block text-gray-700 text-sm mb-1">Tutor <span class="text-red-500">*</span></label>
                        <select name="tutor_id" required class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                            <option value="">Selecione o tutor</option>
                            <?php
                            $tutores = $pdo->query("SELECT id, nome FROM tutores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
                            foreach ($tutores as $t) {
                                echo '<option value="'.$t['id'].'">'.htmlspecialchars($t['nome']).'</option>';
                            }
                            ?>
                        </select>
                    </div>
                <?php else: ?>
                    <input type="hidden" name="tutor_id" value="<?= $tutor['id'] ?>">
                    <div class="bg-blue-50 border border-blue-100 rounded px-3 py-2 text-blue-800 font-semibold mb-2 flex items-center gap-2">
                        <i class="fa fa-user"></i> <?= htmlspecialchars($tutor['nome']) ?>
                    </div>
                <?php endif; ?>
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Nome do Pet <span class="text-red-500">*</span></label>
                    <input type="text" name="nome" required class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Espécie <span class="text-red-500">*</span></label>
                    <input type="text" name="especie" required class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Raça</label>
                    <input type="text" name="raca" class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                </div>
                <!-- <div>
                    <label class="block text-gray-700 text-sm mb-1">Data de Nascimento</label>
                    <input type="date" name="data_nascimento" class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                </div> -->
                <div class="flex gap-3 mt-8 justify-center">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-2 rounded-lg hover:bg-blue-700 font-semibold shadow transition flex items-center gap-2">
                        <i class="fa fa-save"></i> Salvar
                    </button>
                    <a href="<?= $tutor ? '../tutores/visualizar_tutor.php?id='.$tutor['id'] : '../tutores/listar_tutores.php' ?>" class="bg-gray-300 text-gray-700 px-8 py-2 rounded-lg hover:bg-gray-400 font-semibold shadow transition flex items-center gap-2">
                        <i class="fa fa-arrow-left"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>
</body></html>