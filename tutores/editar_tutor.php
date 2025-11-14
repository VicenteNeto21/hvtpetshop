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
    $telefone_is_whatsapp = isset($_POST['telefone_is_whatsapp']) ? 'Sim' : 'Não';
    $email = !empty($_POST['email']) ? $_POST['email'] : null;
    $cep = $_POST['cep'];
    $rua = $_POST['rua'];
    $numero = $_POST['numero'];
    $bairro = $_POST['bairro'];
    $cidade = $_POST['cidade'];
    $uf = $_POST['uf'];

    try {
        // Se um e-mail foi fornecido, verifica se ele já pertence a outro tutor
        if ($email) {
            $stmtCheck = $pdo->prepare("SELECT id FROM tutores WHERE email = ? AND id != ?");
            $stmtCheck->execute([$email, $tutorId]);
            if ($stmtCheck->fetch()) {
                throw new Exception("Este e-mail já está em uso por outro tutor.");
            }
        }

        $stmt = $pdo->prepare("UPDATE tutores SET nome = ?, telefone = ?, telefone_is_whatsapp = ?, email = ?, cep = ?, rua = ?, numero = ?, bairro = ?, cidade = ?, uf = ? WHERE id = ?");
        $stmt->execute([$nome, $telefone, $telefone_is_whatsapp, $email, $cep, $rua, $numero, $bairro, $cidade, $uf, $tutorId]);

        $_SESSION['mensagem'] = "Tutor atualizado com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
        header("Location: visualizar_tutor.php?id=" . $tutorId);
        exit();
    } catch (Exception $e) {
        $_SESSION['mensagem'] = "Erro ao atualizar tutor: " . $e->getMessage();
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: editar_tutor.php?id=" . $tutorId);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Editar Tutor - CereniaPet</title>
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
            <h1 class="text-3xl font-bold text-slate-800">Editar Tutor</h1>
            <p class="text-slate-500 mt-1">Atualize os dados do tutor.</p>
        </div>

        <div class="bg-white p-6 md:p-8 rounded-lg shadow-sm animate-fade-in">
            <form method="POST" id="form-tutor">
                <div class="grid grid-cols-1 lg:grid-cols-2 lg:gap-12">
                    <!-- Coluna da Esquerda: Dados Pessoais -->
                    <div class="space-y-5">
                        <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 flex items-center gap-3">
                            <i class="fa-solid fa-id-card text-blue-500"></i>
                            Dados Pessoais
                        </h3>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1">Nome Completo *</label>
                                <input type="text" name="nome" value="<?= htmlspecialchars($tutor['nome']) ?>" required 
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" placeholder="Nome do tutor">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1">Telefone *</label>
                                <input type="text" name="telefone" value="<?= htmlspecialchars($tutor['telefone']) ?>" required 
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" placeholder="(00) 00000-0000" oninput="mascaraTelefone(this)" maxlength="15">
                                <div class="mt-2 flex items-center">
                                    <input type="checkbox" id="telefone_is_whatsapp" name="telefone_is_whatsapp" value="Sim" <?= ($tutor['telefone_is_whatsapp'] ?? 'Não') === 'Sim' ? 'checked' : '' ?> class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                    <label for="telefone_is_whatsapp" class="ml-2 text-sm text-slate-600">Este número é WhatsApp</label>
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1">E-mail (Opcional)</label>
                                <input type="email" name="email" value="<?= htmlspecialchars($tutor['email'] ?? '') ?>"
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" placeholder="email@exemplo.com">
                            </div>
                        </div>
                    </div>

                    <!-- Coluna da Direita: Endereço -->
                    <div class="space-y-5 mt-8 lg:mt-0">
                        <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 flex items-center gap-3">
                            <i class="fa-solid fa-map-marker-alt text-violet-500"></i>
                            Endereço (Opcional)
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-slate-600 mb-1">CEP</label>
                                <input type="text" name="cep" id="cep" maxlength="9" 
                                       value="<?= isset($tutor['cep']) ? htmlspecialchars($tutor['cep']) : '' ?>"
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" oninput="formatarCep(this)" onblur="buscarCep()" placeholder="00000-000">
                            </div>
                            <div class="sm:col-span-1"></div> <!-- Espaço em branco -->
                            <div class="sm:col-span-2">
                                <label class="block text-sm font-medium text-slate-600 mb-1">Rua</label>
                                <input type="text" name="rua" id="rua" 
                                       value="<?= isset($tutor['rua']) ? htmlspecialchars($tutor['rua']) : '' ?>"
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-slate-600 mb-1">Número</label>
                                <input type="text" name="numero" id="numero" 
                                       value="<?= isset($tutor['numero']) ? htmlspecialchars($tutor['numero']) : '' ?>"
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-slate-600 mb-1">Bairro</label>
                                <input type="text" name="bairro" id="bairro" 
                                       value="<?= isset($tutor['bairro']) ? htmlspecialchars($tutor['bairro']) : '' ?>"
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-slate-600 mb-1">Cidade</label>
                                <input type="text" name="cidade" id="cidade" 
                                       value="<?= isset($tutor['cidade']) ? htmlspecialchars($tutor['cidade']) : '' ?>"
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                            </div>
                            <div class="sm:col-span-1">
                                <label class="block text-sm font-medium text-slate-600 mb-1">UF</label>
                                <input type="text" name="uf" id="uf" maxlength="2" 
                                       value="<?= isset($tutor['uf']) ? htmlspecialchars($tutor['uf']) : '' ?>"
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-8 mt-8 border-t border-slate-200">
                    <a href="visualizar_tutor.php?id=<?= $tutorId ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        <i class="fa-solid fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Salvar Alterações
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