<?php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Verifica se há um pet específico
if (!isset($_GET['id'])) {
    header("Location: ../dashboard.php");
    exit();
}

$petId = $_GET['id'];

// Consulta as informações do pet e do tutor
$query = "SELECT p.*, t.nome as tutor_nome, t.email, t.telefone, t.id as tutor_id
          FROM pets p
          JOIN tutores t ON p.tutor_id = t.id
          WHERE p.id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':id', $petId);
$stmt->execute();
$pet = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$pet) {
    $_SESSION['mensagem'] = "Pet não encontrado.";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../dashboard.php");
    exit();
}

// Atualização do pet e tutor
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome_tutor = $_POST['nome_tutor'];
    $telefone_tutor = $_POST['telefone_tutor'];

    $nome_pet = $_POST['nome'];
    $especie = $_POST['especie'];
    $raca = $_POST['raca'];
    $idade = $_POST['idade'];
    $sexo = $_POST['sexo'];
    $peso = $_POST['peso'];
    $pelagem = $_POST['pelagem'];
    $observacoes = isset($_POST['observacoes']) ? $_POST['observacoes'] : null;

    try {
        $pdo->beginTransaction();

        // Atualiza tutor
        $sql_tutor = "UPDATE tutores SET nome = ?, telefone = ? WHERE id = ?";
        $stmt_tutor = $pdo->prepare($sql_tutor);
        $stmt_tutor->execute([$nome_tutor, $telefone_tutor, $pet['tutor_id']]);

        // Atualiza pet
        $sql_pet = "UPDATE pets SET nome = ?, especie = ?, raca = ?, idade = ?, sexo = ?, peso = ?, pelagem = ?, observacoes = ? WHERE id = ?";
        $stmt_pet = $pdo->prepare($sql_pet);
        $stmt_pet->execute([$nome_pet, $especie, $raca, $idade, $sexo, $peso, $pelagem, $observacoes, $petId]);

        $pdo->commit();

        $_SESSION['mensagem'] = "Dados do pet e tutor atualizados com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
        header("Location: visualizar_pet.php?id=$petId");
        exit();
    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['mensagem'] = "Erro ao atualizar: " . $e->getMessage();
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: editar_pet.php?id=$petId");
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
    <title>Editar Pet - CereniaPet</title>
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
            <h1 class="text-3xl font-bold text-slate-800">Editar Pet</h1>
            <p class="text-slate-500 mt-1">Atualize as informações do pet e de seu tutor.</p>
        </div>

        <div class="bg-white p-6 md:p-8 rounded-lg shadow-sm animate-fade-in">
            <form action="" method="POST">
                <div class="grid grid-cols-1 lg:grid-cols-2 lg:gap-12">
                    <!-- Coluna da Esquerda: Informações do Tutor -->
                    <div class="space-y-5">
                        <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 flex items-center gap-3">
                            <i class="fa fa-user text-amber-500"></i> Informações do Tutor
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label for="nome_tutor" class="block text-sm font-medium text-slate-600 mb-1">Nome do Tutor *</label>
                                <input type="text" name="nome_tutor" id="nome_tutor" required 
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" 
                                       value="<?= htmlspecialchars($pet['tutor_nome']); ?>">
                            </div>
                            <div>
                                <label for="telefone_tutor" class="block text-sm font-medium text-slate-600 mb-1">Telefone do Tutor *</label>
                                <input type="tel" name="telefone_tutor" id="telefone_tutor" required
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" 
                                       value="<?= htmlspecialchars(formatarTelefone($pet['telefone'])); ?>" maxlength="15" oninput="mascaraTelefone(this)">
                            </div>
                        </div>
                    </div>

                    <!-- Coluna da Direita: Informações do Pet -->
                    <div class="space-y-5 mt-8 lg:mt-0">
                        <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 flex items-center gap-3">
                            <i class="fa-solid fa-dog text-violet-500"></i> Informações do Pet
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="nome" class="block text-sm font-medium text-slate-600 mb-1">Nome do Pet *</label>
                                <input type="text" name="nome" id="nome" required 
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" 
                                       value="<?= htmlspecialchars($pet['nome']); ?>">
                            </div>
                            <div>
                                <label for="sexo" class="block text-sm font-medium text-slate-600 mb-1">Sexo *</label>
                                <select name="sexo" id="sexo" required class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                                    <option value="Macho" <?= $pet['sexo'] == 'Macho' ? 'selected' : '' ?>>Macho</option>
                                    <option value="Fêmea" <?= $pet['sexo'] == 'Fêmea' ? 'selected' : '' ?>>Fêmea</option>
                                </select>
                            </div>
                            <div>
                                <label for="especie" class="block text-sm font-medium text-slate-600 mb-1">Espécie *</label>
                                <input type="text" name="especie" id="especie" required
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"
                                       value="<?= htmlspecialchars($pet['especie']); ?>">
                            </div>
                            <div>
                                <label for="raca" class="block text-sm font-medium text-slate-600 mb-1">Raça *</label>
                                <input type="text" name="raca" id="raca" required
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"
                                       value="<?= htmlspecialchars($pet['raca']); ?>">
                            </div>
                            <div>
                                <label for="idade" class="block text-sm font-medium text-slate-600 mb-1">Idade *</label>
                                <input type="number" name="idade" id="idade" min="0" required
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"
                                       value="<?= htmlspecialchars($pet['idade']); ?>">
                            </div>
                            <div>
                                <label for="peso" class="block text-sm font-medium text-slate-600 mb-1">Peso (kg) *</label>
                                <input type="number" name="peso" id="peso" min="0" step="0.01" required
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"
                                       value="<?= htmlspecialchars($pet['peso']); ?>">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="pelagem" class="block text-sm font-medium text-slate-600 mb-1">Pelagem</label>
                                <input type="text" name="pelagem" id="pelagem"
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"
                                       value="<?= htmlspecialchars($pet['pelagem']); ?>">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="observacoes" class="block text-sm font-medium text-slate-600 mb-1">Observações</label>
                                <textarea name="observacoes" id="observacoes" rows="3"
                                          class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"
                                          placeholder="Alguma informação importante sobre o pet"><?= htmlspecialchars($pet['observacoes']); ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-8 mt-8 border-t border-slate-200">
                    <a href="visualizar_pet.php?id=<?= $petId ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </main>

    <footer class="w-full py-4 text-center text-slate-400 text-xs mt-8">
        &copy; 2025 HVTPETSHOP. Todos os direitos reservados.<br>
        <span class="text-[11px]">Versão do sistema: <strong>AMPN 1.0.6</strong></span>
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