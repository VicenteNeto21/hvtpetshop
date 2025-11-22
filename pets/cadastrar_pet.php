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
    $email_tutor = !empty($_POST['email_tutor']) ? $_POST['email_tutor'] : null;
    $telefone_tutor = $_POST['telefone_tutor'];    
    $telefone_is_whatsapp = isset($_POST['telefone_is_whatsapp']) ? 'Sim' : 'Não';

    try {
        // Inicia transação
        $pdo->beginTransaction();

        // Se um e-mail foi fornecido, verifica se ele já existe
        if ($email_tutor) {
            $stmtCheck = $pdo->prepare("SELECT id FROM tutores WHERE email = ?");
            $stmtCheck->execute([$email_tutor]);
            if ($stmtCheck->fetch()) {
                throw new PDOException("Este e-mail já está cadastrado. Tente outro ou deixe o campo em branco.");
            }
        }

        // Insere o tutor no banco de dados
        $sql_tutor = "INSERT INTO tutores (nome, email, telefone, telefone_is_whatsapp) VALUES (?, ?, ?, ?)";
        $stmt_tutor = $pdo->prepare($sql_tutor);
        $stmt_tutor->execute([$nome_tutor, $email_tutor, $telefone_tutor, $telefone_is_whatsapp]);
        $tutor_id = $pdo->lastInsertId();

        // Cadastra o pet associado ao tutor
        $nome_pet = $_POST['nome'];
        $especie = $_POST['especie'];
        $raca = $_POST['raca'];
        $nascimento = !empty($_POST['nascimento']) ? $_POST['nascimento'] : null;
        $sexo = $_POST['sexo'];
        $peso = !empty($_POST['peso']) ? $_POST['peso'] : null;
        $pelagem = $_POST['pelagem'];
        $observacoes = isset($_POST['observacoes']) ? $_POST['observacoes'] : null;

        // Calcula a idade a partir da data de nascimento para salvar no banco
        $idade = $nascimento ? (new DateTime($nascimento))->diff(new DateTime())->y : 0;

        $sql_pet = "INSERT INTO pets (nome, tutor_id, especie, raca, nascimento, idade, sexo, peso, pelagem, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_pet = $pdo->prepare($sql_pet);
        $stmt_pet->execute([$nome_pet, $tutor_id, $especie, $raca, $nascimento, $idade, $sexo, $peso, $pelagem, $observacoes]);

        // Confirma a transação
        $pdo->commit();

        // Redireciona para a lista de pets
        $_SESSION['mensagem'] = "Pet e tutor cadastrados com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
        header("Location: ../tutores/visualizar_tutor.php?id=" . $tutor_id);
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
    <title>Cadastrar Pet - CereniaPet</title>
    <link rel="icon" type="image/x-icon" href="../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
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
            <h1 class="text-3xl font-bold text-slate-800">Cadastrar Novo Pet</h1>
            <p class="text-slate-500 mt-1">Crie um novo registro para o tutor e seu pet de uma só vez.</p>
        </div>

        <div class="bg-white p-6 md:p-8 rounded-lg shadow-sm animate-fade-in">
            <form action="" method="POST" onsubmit="return validarFormulario()">
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
                                       placeholder="Nome completo do Tutor">
                            </div>
                            <div>
                                <label for="email_tutor" class="block text-sm font-medium text-slate-600 mb-1">E-mail do Tutor (Opcional)</label>
                                <input type="email" name="email_tutor" id="email_tutor"
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" 
                                       placeholder="email@exemplo.com">
                            </div>
                            <div>
                                <label for="telefone_tutor" class="block text-sm font-medium text-slate-600 mb-1">Telefone do Tutor *</label>
                                <input type="tel" name="telefone_tutor" id="telefone_tutor" required
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" 
                                       placeholder="(00) 00000-0000" maxlength="15" oninput="mascaraTelefone(this)">
                            </div>
                        </div>
                        <div class="mt-2 flex items-center">
                            <input type="checkbox" id="telefone_is_whatsapp" name="telefone_is_whatsapp" value="Sim" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="telefone_is_whatsapp" class="ml-2 text-sm text-slate-600">Este número é WhatsApp</label>
                        </div>
                    </div>

                    <!-- Coluna da Direita: Informações do Pet -->
                    <div class="space-y-5 mt-8 lg:mt-0">
                        <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 flex items-center gap-3">
                            <i class="fa-solid fa-dog text-violet-500"></i> Informações do Pet
                        </h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label for="nome" class="block text-sm font-medium text-slate-600 mb-1">Nome do Pet *</label>
                                <input type="text" name="nome" id="nome" required 
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" 
                                       placeholder="Nome do Pet">
                            </div>
                            <div>
                                <label for="sexo" class="block text-sm font-medium text-slate-600 mb-1">Sexo *</label>
                                <select name="sexo" id="sexo" required class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                                    <option value="">Selecione</option>
                                    <option value="Macho">Macho</option>
                                    <option value="Fêmea">Fêmea</option>
                                </select>
                            </div>
                            <div>
                                <label for="especie" class="block text-sm font-medium text-slate-600 mb-1">Espécie *</label>
                                <select name="especie" id="especie" required class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                                    <option value="">Selecione a espécie</option>
                                    <option value="Canino">Canino</option>
                                    <option value="Felina">Felina</option>
                                </select>
                            </div>
                            <div>
                                <label for="raca" class="block text-sm font-medium text-slate-600 mb-1">Raça *</label>
                                <select name="raca" id="raca" required class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" disabled>
                                    <option value="">Primeiro, selecione a espécie</option>
                                </select>
                            </div>
                            <div>
                                <label for="nascimento" class="block text-sm font-medium text-slate-600 mb-1">Data de Nascimento</label>
                                <input type="text" name="nascimento" id="nascimento" autocomplete="off" 
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700" placeholder="dd/mm/aaaa"
                                       oninput="mascaraData(this)" maxlength="10">
                                <div id="erro-nascimento" class="text-red-500 text-sm mt-1"></div>
                            </div>
                            <div>
                                <label for="peso" class="block text-sm font-medium text-slate-600 mb-1">Peso (kg)</label>
                                <input type="number" name="peso" id="peso" min="0" step="0.01"
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"
                                       placeholder="Peso do Pet">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="pelagem" class="block text-sm font-medium text-slate-600 mb-1">Pelagem</label>
                                <input type="text" name="pelagem" id="pelagem"
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"
                                       placeholder="Tipo de pelagem">
                            </div>
                            <div class="sm:col-span-2">
                                <label for="observacoes" class="block text-sm font-medium text-slate-600 mb-1">Observações</label>
                                <textarea name="observacoes" id="observacoes" rows="3"
                                          class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"
                                          placeholder="Alguma informação importante sobre o pet"></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-8 mt-8 border-t border-slate-200">
                    <a href="../dashboard.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        Cancelar
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        <i class="fas fa-check-circle"></i> Salvar Cadastro
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
        /* Custom styles for Select2 to match Tailwind theme */
        .select2-container--default .select2-selection--single {
            background-color: #fff;
            border: 1px solid #cbd5e1; /* slate-300 */
            border-radius: 0.375rem; /* rounded-md */
            height: 2.75rem; /* Ajustado para p-2 e font-size */
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #334155; /* slate-700 */
            line-height: 1.5rem;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 2.6rem;
        }
        .select2-container--default.select2-container--open .select2-selection--single {
            border-color: #3b82f6; /* blue-500 */
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5); /* ring-2 ring-blue-500 */
        }
        .select2-dropdown {
            border: 1px solid #cbd5e1; /* slate-300 */
            border-radius: 0.375rem; /* rounded-md */
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

function mascaraData(input) {
    let v = input.value.replace(/\D/g, '');
    if (v.length > 8) v = v.slice(0, 8);
    if (v.length > 2) v = v.slice(0, 2) + '/' + v.slice(2);
    if (v.length > 5) v = v.slice(0, 5) + '/' + v.slice(5);
    input.value = v;
}

function validarFormulario() {
    const dataInput = document.getElementById('nascimento');
    const erroDiv = document.getElementById('erro-nascimento');
    const dataValor = dataInput.value;

    // Limpa erros anteriores
    erroDiv.textContent = '';
    dataInput.classList.remove('border-red-500');

    // A validação só ocorre se o campo não estiver vazio
    if (dataValor.trim() !== '') {
        if (!validarData(dataValor)) {
            erroDiv.textContent = 'Por favor, insira uma data válida (dd/mm/aaaa) e que não seja no futuro.';
            dataInput.classList.add('border-red-500');
            dataInput.focus();
            return false; // Impede o envio do formulário
        }
    }

    return true; // Permite o envio do formulário
}

function validarData(dataStr) {
    if (!/^\d{2}\/\d{2}\/\d{4}$/.test(dataStr)) return false;

    const [dia, mes, ano] = dataStr.split('/').map(Number);
    const data = new Date(ano, mes - 1, dia);
    const hoje = new Date();
    hoje.setHours(0, 0, 0, 0); // Zera a hora para comparar apenas a data

    return data.getFullYear() === ano && data.getMonth() === mes - 1 && data.getDate() === dia && data <= hoje;
}
</script>
<!-- jQuery (necessário para Select2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<!-- Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>
<script>
$(document).ready(function() {
    const especieSelect = $('#especie');
    const racaSelect = $('#raca');

    // Inicializa o Select2 no campo de raça
    racaSelect.select2({
        placeholder: "Primeiro, selecione a espécie"
    });

    let racasData = {};

    // Carrega o JSON com as raças
    $.getJSON('../data/racas.json', function(data) {
        racasData = data;
    });

    especieSelect.on('change', function() {
        const especie = $(this).val();
        racaSelect.empty().append('<option value="">Selecione a raça</option>'); // Limpa e adiciona placeholder

        if (especie && racasData[especie]) {
            racasData[especie].forEach(raca => {
                racaSelect.append(new Option(raca, raca));
            });
            racaSelect.prop('disabled', false);
        } else {
            racaSelect.prop('disabled', true);
        }
        racaSelect.trigger('change'); // Notifica o Select2 da mudança
    });

    // Inicializa o Flatpickr para o campo de data de nascimento
    flatpickr("#nascimento", {
        "locale": "pt",
        allowInput: true,
        dateFormat: "d/m/Y",
        maxDate: "today"
    });
});
</script>
</body>
</html>