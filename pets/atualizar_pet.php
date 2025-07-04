<?php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Verifica se o ID do pet foi passado
if (!isset($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit();
}

$id = $_GET['id'];

// Recupera os dados do pet e do tutor
$stmt = $pdo->prepare("SELECT p.*, t.nome as tutor_nome, t.email, t.telefone, t.id as tutor_id 
                      FROM pets p 
                      JOIN tutores t ON p.tutor_id = t.id 
                      WHERE p.id = ?");
$stmt->execute([$id]);
$pet = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se o pet existe
if (!$pet) {
    $_SESSION['mensagem'] = "Pet não encontrado!";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../dashboard.php");
    exit();
}

// Processa o formulário de edição
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $pdo->beginTransaction();

        // Dados do pet
        $nome = $_POST['nome'];
        $especie = $_POST['especie'];
        $raca = $_POST['raca'];
        $idade = $_POST['idade'];
        $sexo = $_POST['sexo'];
        $peso = $_POST['peso'];
        $pelagem = $_POST['pelagem'];
        $observacoes = $_POST['observacoes'];

        // Dados do tutor
        $tutor_nome = $_POST['tutor_nome'];
        $tutor_email = $_POST['tutor_email'];
        $tutor_telefone = $_POST['tutor_telefone'];

        // Atualiza os dados do tutor
        $sql_tutor = "UPDATE tutores SET nome = ?, email = ?, telefone = ? WHERE id = ?";
        $stmt_tutor = $pdo->prepare($sql_tutor);
        $stmt_tutor->execute([$tutor_nome, $tutor_email, $tutor_telefone, $pet['tutor_id']]);

        // Atualiza os dados do pet
        $sql_pet = "UPDATE pets SET nome = ?, especie = ?, raca = ?, idade = ?, sexo = ?, peso = ?, pelagem = ?, observacoes = ? WHERE id = ?";
        $stmt_pet = $pdo->prepare($sql_pet);
        $stmt_pet->execute([$nome, $especie, $raca, $idade, $sexo, $peso, $pelagem, $observacoes, $id]);

        $pdo->commit();

        $_SESSION['mensagem'] = "Pet e tutor atualizados com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
        header("Location: visualizar_pet.php?id=".$id);
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['mensagem'] = "Erro ao atualizar: " . $e->getMessage();
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: atualizar_pet.php?id=".$id);
        exit();
    }
}

// Função para formatar telefone
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    if (strlen($telefone) === 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } elseif (strlen($telefone) === 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }
    return $telefone;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Pet - HVTPETSHOP</title>
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
            <a href="cadastrar_pet.php" class="text-green-600 hover:text-green-800 font-semibold transition"><i class="fa fa-plus mr-1"></i>Novo Pet</a>
            <a href="../tutores/listar_tutores.php" class="text-blue-500 hover:text-blue-700 font-semibold transition"><i class="fa fa-users mr-1"></i>Tutores</a>
            <a href="../auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition"><i class="fa fa-sign-out-alt mr-1"></i>Sair</a>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-2xl mx-auto mt-10 p-4">
        <div class="bg-white/90 p-8 rounded-2xl shadow-xl animate-fade-in mb-8">
            <h2 class="text-2xl font-bold text-blue-700 mb-2 flex items-center gap-2">
                <i class="fa fa-paw"></i> Editar Pet e Tutor
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
                        <label for="tutor_nome" class="block text-gray-700 mb-1">Nome do Tutor *</label>
                        <input type="text" name="tutor_nome" id="tutor_nome" required 
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                               value="<?= htmlspecialchars($pet['tutor_nome']); ?>">
                    </div>
                    <div>
                        <label for="tutor_telefone" class="block text-gray-700 mb-1">Telefone do Tutor *</label>
                        <input type="tel" name="tutor_telefone" id="tutor_telefone" required
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                               value="<?= htmlspecialchars(formatarTelefone($pet['telefone'])); ?>"
                               maxlength="15" oninput="mascaraTelefone(this)">
                    </div>
                    <div class="md:col-span-2">
                        <label for="tutor_email" class="block text-gray-700 mb-1">Email do Tutor *</label>
                        <input type="email" name="tutor_email" id="tutor_email" required
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                               value="<?= htmlspecialchars($pet['email']); ?>">
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
                               value="<?= htmlspecialchars($pet['nome']); ?>">
                    </div>
                    <div>
                        <label for="sexo" class="block text-gray-700 mb-1">Sexo *</label>
                        <select name="sexo" id="sexo" required class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400">
                            <option value="">Selecione</option>
                            <option value="Macho" <?= $pet['sexo'] == 'Macho' ? 'selected' : '' ?>>Macho</option>
                            <option value="Fêmea" <?= $pet['sexo'] == 'Fêmea' ? 'selected' : '' ?>>Fêmea</option>
                        </select>
                    </div>
                    <div>
                        <label for="especie" class="block text-gray-700 mb-1">Espécie *</label>
                        <input type="text" name="especie" id="especie" required
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                               value="<?= htmlspecialchars($pet['especie']); ?>">
                    </div>
                    <div>
                        <label for="raca" class="block text-gray-700 mb-1">Raça *</label>
                        <input type="text" name="raca" id="raca" required
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                               value="<?= htmlspecialchars($pet['raca']); ?>">
                    </div>
                    <div>
                        <label for="idade" class="block text-gray-700 mb-1">Idade *</label>
                        <input type="number" name="idade" id="idade" min="0" required
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                               value="<?= htmlspecialchars($pet['idade']); ?>">
                    </div>
                    <div>
                        <label for="peso" class="block text-gray-700 mb-1">Peso (kg) *</label>
                        <input type="number" name="peso" id="peso" min="0" step="0.01" required
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                               value="<?= htmlspecialchars($pet['peso']); ?>">
                    </div>
                    <div class="md:col-span-2">
                        <label for="pelagem" class="block text-gray-700 mb-1">Pelagem</label>
                        <input type="text" name="pelagem" id="pelagem"
                               class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                               value="<?= htmlspecialchars($pet['pelagem']); ?>">
                    </div>
                    <div class="md:col-span-2">
                        <label for="observacoes" class="block text-gray-700 mb-1">Observações</label>
                        <textarea name="observacoes" id="observacoes" rows="3"
                                  class="w-full p-3 border rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-400"
                                  placeholder="Alguma informação importante sobre o pet"><?= htmlspecialchars($pet['observacoes']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4">
                <a href="visualizar_pet.php?id=<?= $id ?>" class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-6 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times mr-2"></i> Cancelar
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg flex items-center justify-center">
                    <i class="fas fa-save mr-2"></i> Salvar Alterações
                </button>
            </div>
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