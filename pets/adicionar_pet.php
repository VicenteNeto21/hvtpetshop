<?php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Recebe o tutor_id por GET (opcional)
$tutor_id_get = isset($_GET['tutor_id']) ? intval($_GET['tutor_id']) : null;

// Busca dados do tutor se tutor_id informado via GET
$tutor = null;
if ($tutor_id_get) {
    $stmt = $pdo->prepare("SELECT id, nome FROM tutores WHERE id = ?");
    $stmt->execute([$tutor_id_get]);
    $tutor = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Busca todos os tutores para o dropdown, caso nenhum seja pré-selecionado
$all_tutores = [];
if (!$tutor) {
    $all_tutores = $pdo->query("SELECT id, nome FROM tutores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
}

// Cadastro do pet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $especie = $_POST['especie'];
    $raca = $_POST['raca'];
    $sexo = $_POST['sexo'];
    $nascimento = !empty($_POST['nascimento']) ? $_POST['nascimento'] : null;
    $peso = !empty($_POST['peso']) ? $_POST['peso'] : null;
    $pelagem = $_POST['pelagem'];
    $observacoes = $_POST['observacoes'] ?? null;
    $tutor_id_post = $_POST['tutor_id'] ?? null;

    if (empty($nome) || empty($especie) || empty($sexo) || empty($tutor_id_post)) {
        $_SESSION['mensagem'] = "Preencha todos os campos obrigatórios!";
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: adicionar_pet.php?tutor_id=" . $tutor_id_post);
        exit();
    } else {
        $stmt = $pdo->prepare("INSERT INTO pets (nome, especie, raca, sexo, nascimento, peso, pelagem, observacoes, tutor_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$nome, $especie, $raca, $sexo, $nascimento, $peso, $pelagem, $observacoes, $tutor_id_post]);

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Adicionar Pet - CereniaPet</title>
    <link rel="icon" type="image/x-icon" href="../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">
    <?php
    $path_prefix = '../';
    include '../components/navbar.php';
    ?>
    <?php include '../components/toast.php'; ?>

    <main class="flex-1 w-full p-4 md:p-6 lg:p-8">
        <!-- Cabeçalho -->
        <div class="mb-8 animate-fade-in">
            <h1 class="text-3xl font-bold text-slate-800">Adicionar Novo Pet</h1>
            <p class="text-slate-500 mt-1">Cadastre um novo pet para um tutor existente.</p>
        </div>

        <div class="bg-white p-6 md:p-8 rounded-lg shadow-sm animate-fade-in">
            <form method="POST" class="space-y-6">
                <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mb-6 flex items-center gap-3">
                    <i class="fa-solid fa-dog text-violet-500"></i> Informações do Pet
                </h3>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Tutor <span class="text-red-500">*</span></label>
                    <?php if ($tutor): ?>
                        <div class="w-full p-2 border border-slate-200 bg-slate-50 rounded-md text-slate-700 font-semibold">
                            <?= htmlspecialchars($tutor['nome']) ?>
                        </div>
                        <input type="hidden" name="tutor_id" value="<?= $tutor['id'] ?>">
                    <?php else: ?>
                        <select name="tutor_id" required class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                            <option value="">Selecione o tutor</option>
                            <?php foreach ($all_tutores as $t): ?>
                                <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['nome']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    <?php endif; ?>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Nome do Pet <span class="text-red-500">*</span></label>
                        <input type="text" name="nome" required class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Sexo <span class="text-red-500">*</span></label>
                        <select name="sexo" required class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                            <option value="">Selecione</option>
                            <option value="Macho">Macho</option>
                            <option value="Fêmea">Fêmea</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Espécie <span class="text-red-500">*</span></label>
                        <input type="text" name="especie" required class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" placeholder="Ex: Canina, Felina">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Raça</label>
                        <input type="text" name="raca" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Data de Nascimento</label>
                        <input type="date" name="nascimento" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Peso (kg)</label>
                        <input type="number" name="peso" min="0" step="0.01" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" placeholder="Peso do Pet">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-600 mb-1">Pelagem</label>
                        <input type="text" name="pelagem" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" placeholder="Tipo de pelagem">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-sm font-medium text-slate-600 mb-1">Observações</label>
                        <textarea name="observacoes" rows="3" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" placeholder="Alguma informação importante sobre o pet"></textarea>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-8 mt-8 border-t border-slate-200">
                    <a href="<?= $tutor ? '../tutores/visualizar_tutor.php?id='.$tutor['id'] : '../tutores/listar_tutores.php' ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        <i class="fa fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        <i class="fa fa-save"></i> Salvar Pet
                    </button>
                </div>
            </form>
        </div>
    </main>

    <?php include $path_prefix . 'components/footer.php'; ?>

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: translateY(0);}
        }
        .animate-fade-in {
            animation: fade-in 0.8s ease;
        }
    </style>
</body>
</html>