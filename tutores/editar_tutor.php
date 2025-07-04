<?php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Verifica se o ID do tutor foi fornecido
if (!isset($_GET['id'])) {
    $_SESSION['mensagem'] = "Tutor não informado!";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: listar_tutores.php");
    exit();
}

$tutorId = $_GET['id'];

// Busca os dados do tutor
$stmt = $pdo->prepare("SELECT * FROM tutores WHERE id = ?");
$stmt->execute([$tutorId]);
$tutor = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tutor) {
    $_SESSION['mensagem'] = "Tutor não encontrado!";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: listar_tutores.php");
    exit();
}

// Atualiza os dados se enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $cep = $_POST['cep'];
    $rua = $_POST['rua'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $uf = $_POST['uf'];

    $stmt = $pdo->prepare("UPDATE tutores SET nome = ?, telefone = ?, email = ?, cep = ?, rua = ?, numero = ?, bairro = ?, cidade = ?, uf = ? WHERE id = ?");
    $stmt->execute([$nome, $telefone, $email, $cep, $rua, $numero, $bairro, $cidade, $uf, $tutorId]);

    $_SESSION['mensagem'] = "Tutor atualizado com sucesso!";
    $_SESSION['tipo_mensagem'] = "success";
    header("Location: listar_tutores.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Tutor</title>
    <link rel="icon" type="image/x-icon" href="../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <!-- Navbar padrão -->
    <nav class="w-full bg-white/90 shadow flex items-center justify-between px-6 py-3">
        <div class="flex items-center gap-3">
            <img src="../icons/pet.jpg" alt="Logo Petshop" class="w-10 h-10 rounded-full shadow">
            <span class="text-2xl font-bold text-blue-700 tracking-tight">HVTPETSHOP</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="../dashboard.php" class="text-blue-600 hover:text-blue-800 font-semibold transition"><i class="fa fa-home mr-1"></i>Dashboard</a>
            <a href="../pets/cadastrar_pet.php" class="text-green-600 hover:text-green-800 font-semibold transition"><i class="fa fa-plus mr-1"></i>Novo Pet</a>
            <a href="listar_tutores.php" class="text-blue-500 hover:text-blue-700 font-semibold transition"><i class="fa fa-users mr-1"></i>Tutores</a>
            <a href="../auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition"><i class="fa fa-sign-out-alt mr-1"></i>Sair</a>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-xl mx-auto mt-10 p-4">
        <div class="bg-white/90 p-8 rounded-2xl shadow-xl animate-fade-in">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-blue-700 mb-1 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-user-pen"></i> Editar Tutor
                </h1>
                <div class="text-gray-500 text-sm">Atualize os dados do tutor abaixo</div>
            </div>
            <form method="POST" class="space-y-6" id="form-tutor">
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Nome</label>
                    <input type="text" name="nome" value="<?= htmlspecialchars($tutor['nome']) ?>" required class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Telefone</label>
                    <input type="text" name="telefone" value="<?= htmlspecialchars($tutor['telefone']) ?>" required class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                </div>
                <div>
                    <label class="block text-gray-700 text-sm mb-1">E-mail</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($tutor['email']) ?>" class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                </div>
                <!-- Endereço do Tutor -->
                <div class="mt-8">
                    <div class="font-semibold text-blue-700 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-map-marker-alt"></i> Endereço do Tutor
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-blue-50/60 p-4 rounded-xl border border-blue-100 shadow-sm">
                        <div>
                            <label class="block text-gray-700 text-sm mb-1">CEP</label>
                            <input type="text" name="cep" id="cep"
                                value="<?= isset($tutor['cep']) ? htmlspecialchars($tutor['cep']) : '' ?>"
                                maxlength="9"
                                class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm"
                                oninput="formatarCep(this)"
                                onblur="buscarCep()"
                                placeholder="00000-000">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm mb-1">Rua</label>
                            <input type="text" name="rua" id="rua" value="<?= isset($tutor['rua']) ? htmlspecialchars($tutor['rua']) : '' ?>" class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm mb-1">Número</label>
                            <input type="text" name="numero" id="numero" value="<?= isset($tutor['numero']) ? htmlspecialchars($tutor['numero']) : '' ?>" class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm mb-1">Bairro</label>
                            <input type="text" name="bairro" id="bairro" value="<?= isset($tutor['bairro']) ? htmlspecialchars($tutor['bairro']) : '' ?>" class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm mb-1">Cidade</label>
                            <input type="text" name="cidade" id="cidade" value="<?= isset($tutor['cidade']) ? htmlspecialchars($tutor['cidade']) : '' ?>" class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                        </div>
                        <div>
                            <label class="block text-gray-700 text-sm mb-1">UF</label>
                            <input type="text" name="uf" id="uf" value="<?= isset($tutor['uf']) ? htmlspecialchars($tutor['uf']) : '' ?>" maxlength="2" class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                        </div>
                    </div>
                </div>
                <div class="flex gap-3 mt-8 justify-center">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-2 rounded-lg hover:bg-blue-700 font-semibold shadow transition flex items-center gap-2">
                        <i class="fa-solid fa-save"></i> Salvar
                    </button>
                    <a href="listar_tutores.php" class="bg-gray-300 text-gray-700 px-8 py-2 rounded-lg hover:bg-gray-400 font-semibold shadow transition flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>

    <!-- Script para buscar endereço pelo CEP (ViaCEP) -->
    <script>
    // Formata o CEP automaticamente para 00000-000
    function formatarCep(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.length > 5) {
            value = value.substring(0, 5) + '-' + value.substring(5, 8);
        }
        input.value = value.substring(0, 9);
    }

    function buscarCep() {
        const cep = document.getElementById('cep').value.replace(/\D/g, '');
        if (cep.length !== 8) return;

        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(response => response.json())
            .then(data => {
                if (!data.erro) {
                    document.getElementById('rua').value = data.logradouro || '';
                    document.getElementById('bairro').value = data.bairro || '';
                    document.getElementById('cidade').value = data.localidade || '';
                    document.getElementById('uf').value = data.uf || '';
                }
            });
    }
    </script>

    <!-- Rodapé padrão -->
    <footer class="w-full bg-white/90 py-4 mt-auto">
        <div class="max-w-screen-xl mx-auto text-center text-gray-500 text-sm">
            &copy; 2023 HVTPETSHOP. Todos os direitos reservados.
        </div>
    </footer>
</body>
</html>