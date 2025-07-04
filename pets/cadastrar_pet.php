<?php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Processa o formulário de cadastro de pet e tutor
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Cadastra o tutor primeiro
    $nome_tutor = $_POST['nome_tutor'];
    $email_tutor = $_POST['email_tutor'];
    $telefone_tutor = $_POST['telefone_tutor'];

    try {
        // Inicia transação
        $pdo->beginTransaction();

        // Insere o tutor no banco de dados
        $sql_tutor = "INSERT INTO tutores (nome, email, telefone) VALUES (?, ?, ?)";
        $stmt_tutor = $pdo->prepare($sql_tutor);
        $stmt_tutor->execute([$nome_tutor, $email_tutor, $telefone_tutor]);
        $tutor_id = $pdo->lastInsertId();

        // Cadastra o pet associado ao tutor
        $nome_pet = $_POST['nome'];
        $especie = $_POST['especie'];
        $raca = $_POST['raca'];
        $idade = $_POST['idade'];
        $sexo = $_POST['sexo'];
        $peso = $_POST['peso'];
        $pelagem = $_POST['pelagem'];
        $observacoes = isset($_POST['observacoes']) ? $_POST['observacoes'] : null;

        $sql_pet = "INSERT INTO pets (nome, tutor_id, especie, raca, idade, sexo, peso, pelagem, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_pet = $pdo->prepare($sql_pet);
        $stmt_pet->execute([$nome_pet, $tutor_id, $especie, $raca, $idade, $sexo, $peso, $pelagem, $observacoes]);

        // Confirma a transação
        $pdo->commit();

        // Redireciona para a lista de pets
        $_SESSION['mensagem'] = "Pet e tutor cadastrados com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
        header("Location: ../dashboard.php");
        exit();

    } catch (PDOException $e) {
        // Em caso de erro, reverte a transação
        $pdo->rollBack();
        $_SESSION['mensagem'] = "Erro ao cadastrar: " . $e->getMessage();
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: cadastrar_pet.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Pet - HVTPETSHOP</title>
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
            <a href="cadastrar_pet.php" class="text-green-600 hover:bg-green-700 font-semibold transition"><i class="fa fa-plus mr-1"></i>Novo Pet</a>
            <a href="../tutores/listar_tutores.php" class="text-blue-500 hover:text-blue-700 font-semibold transition"><i class="fa fa-users mr-1"></i>Tutores</a>
            <a href="../auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition"><i class="fa fa-sign-out-alt mr-1"></i>Sair</a>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-2xl mx-auto mt-10 p-4">
        <div class="bg-white/90 p-8 rounded-2xl shadow-xl animate-fade-in mb-8">
            <h2 class="text-2xl font-bold text-blue-700 mb-2 flex items-center gap-2">
                <i class="fa fa-paw"></i> Cadastrar Pet e Tutor
            </h2>
            <?php if (isset($_SESSION['mensagem'])): ?>
                <div class="mt-4 p-4 rounded-lg <?php echo $_SESSION['tipo_mensagem'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                    <?php echo $_SESSION['mensagem']; ?>
                    <?php unset($_SESSION['mensagem']); unset($_SESSION['tipo_mensagem']); ?>
                </div>
            <?php endif; ?>
        </div>

        <form action="" method="POST" class="bg-white/90 p-8 rounded-2xl shadow-xl space-y-8 animate-fade-in">
            <!-- Seção do Tutor -->
            <div>
                <h3 class="text-lg font-semibold text-blue-700 mb-4 flex items-center gap-2"><i class="fa fa-user"></i> Informações do Tutor</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nome_tutor" class="block text-gray-700 mb-1">Nome do Tutor *</label>
                        <input type="text" name="nome_tutor" id="nome_tutor" required 
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" 
                               placeholder="Nome completo do Tutor">
                    </div>
                    <div>
                        <label for="telefone_tutor" class="block text-gray-700 mb-1">Telefone do Tutor *</label>
                        <input type="tel" name="telefone_tutor" id="telefone_tutor" required
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" 
                               placeholder="(88) 9.9227-4307" maxlength="15" oninput="mascaraTelefone(this)">
                    </div>
                    <div class="md:col-span-2">
                        <label for="email_tutor" class="block text-gray-700 mb-1">Email do Tutor *</label>
                        <input type="email" name="email_tutor" id="email_tutor" required
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" 
                               placeholder="Email válido do Tutor">
                    </div>
                </div>
            </div>

            <!-- Seção do Pet -->
            <div>
                <h3 class="text-lg font-semibold text-blue-700 mb-4 flex items-center gap-2"><i class="fa-solid fa-dog"></i> Informações do Pet</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="nome" class="block text-gray-700 mb-1">Nome do Pet *</label>
                        <input type="text" name="nome" id="nome" required 
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400" 
                               placeholder="Nome do Pet">
                    </div>
                    <div>
                        <label for="sexo" class="block text-gray-700 mb-1">Sexo *</label>
                        <select name="sexo" id="sexo" required class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">Selecione</option>
                            <option value="Macho">Macho</option>
                            <option value="Fêmea">Fêmea</option>
                        </select>
                    </div>
                    <div>
                        <label for="especie" class="block text-gray-700 mb-1">Espécie *</label>
                        <input type="text" name="especie" id="especie" required
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                               placeholder="Ex: Canina, Felina">
                    </div>
                    <div>
                        <label for="raca" class="block text-gray-700 mb-1">Raça *</label>
                        <input type="text" name="raca" id="raca" required
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                               placeholder="Raça do Pet">
                    </div>
                    <div>
                        <label for="idade" class="block text-gray-700 mb-1">Idade *</label>
                        <input type="number" name="idade" id="idade" min="0" required
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                               placeholder="Idade em anos">
                    </div>
                    <div>
                        <label for="peso" class="block text-gray-700 mb-1">Peso (kg) *</label>
                        <input type="number" name="peso" id="peso" min="0" step="0.01" required
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                               placeholder="Peso do Pet">
                    </div>
                    <div class="md:col-span-2">
                        <label for="pelagem" class="block text-gray-700 mb-1">Pelagem</label>
                        <input type="text" name="pelagem" id="pelagem"
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                               placeholder="Tipo de pelagem">
                    </div>
                    <div class="md:col-span-2">
                        <label for="observacoes" class="block text-gray-700 mb-1">Observações</label>
                        <textarea name="observacoes" id="observacoes" rows="3"
                                  class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                                  placeholder="Alguma informação importante sobre o pet"></textarea>
                    </div>
                </div>
            </div>

            <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white py-3 rounded-lg shadow-md transition duration-300">
                <i class="fas fa-check-circle mr-2"></i> Cadastrar Pet e Tutor
            </button>
        </form>
    </main>

    <footer class="w-full py-3 bg-white/80 text-center text-gray-400 text-xs mt-8">
        &copy; 2025 HVTPETSHOP. Todos os direitos reservados.
    </footer>

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(30px);}
            to { opacity: 1; transform: translateY(0);}
        }
        .animate-fade-in {
            animation: fade-in 0.8s ease;
        }
    </style>

    <script>
function mascaraTelefone(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length > 11) v = v.slice(0, 11);
    if (v.length > 0) v = '(' + v;
    if (v.length > 3) v = v.slice(0, 3) + ') ' + v.slice(3);
    if (v.length > 10) v = v.slice(0, 10) + '-' + v.slice(10);
    input.value = v;
}
</script>
</body>
</html>