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

    // Tratamento da data de nascimento
    $nascimento_str = !empty($_POST['nascimento']) ? $_POST['nascimento'] : null;
    $nascimento_db = null;
    $idade = $pet['idade'] ?? 0;

    if ($nascimento_str) {
        $date_obj = DateTime::createFromFormat('d/m/Y', $nascimento_str);
        if ($date_obj) {
            $nascimento_db = $date_obj->format('Y-m-d');
            $idade = $date_obj->diff(new DateTime())->y;
        }
    }

    $nome_pet = $_POST['nome'];
    $especie = $_POST['especie'];
    $raca = $_POST['raca'];
    $sexo = $_POST['sexo'];
    $peso = !empty($_POST['peso']) ? $_POST['peso'] : null;
    $pelagem = $_POST['pelagem'];
    $observacoes = isset($_POST['observacoes']) ? $_POST['observacoes'] : null;

    try {
        $pdo->beginTransaction();

        // Atualiza tutor
        $sql_tutor = "UPDATE tutores SET nome = ?, telefone = ? WHERE id = ?";
        $stmt_tutor = $pdo->prepare($sql_tutor);
        $stmt_tutor->execute([$nome_tutor, $telefone_tutor, $pet['tutor_id']]);

        // Atualiza pet
        $sql_pet = "UPDATE pets SET nome = ?, especie = ?, raca = ?, nascimento = ?, idade = ?, sexo = ?, peso = ?, pelagem = ?, observacoes = ? WHERE id = ?";        
        $stmt_pet = $pdo->prepare($sql_pet);
        $stmt_pet->execute([$nome_pet, $especie, $raca, $nascimento_db, $idade, $sexo, $peso, $pelagem, $observacoes, $petId]);

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

// Função para formatar data
function formatarData($data) {
    if (empty($data)) {
        return '';
    }
    $date_obj = DateTime::createFromFormat('Y-m-d', $data);
    return $date_obj ? $date_obj->format('d/m/Y') : '';
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
            <h1 class="text-3xl font-bold text-slate-800">Editar Pet</h1>
            <p class="text-slate-500 mt-1">Atualize as informações do pet e de seu tutor.</p>
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
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
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
                                <select name="especie" id="especie" required class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                                    <option value="Canino" <?= ($pet['especie'] ?? '') == 'Canino' ? 'selected' : '' ?>>Canino</option>
                                    <option value="Felina" <?= ($pet['especie'] ?? '') == 'Felina' ? 'selected' : '' ?>>Felina</option>
                                </select>
                            </div>
                            <div>
                                <label for="raca" class="block text-sm font-medium text-slate-600 mb-1">Raça</label>
                                <select name="raca" id="raca" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                                    <option value="">Selecione a raça</option>
                                </select>
                            </div>
                            <div>
                                <label for="nascimento" class="block text-sm font-medium text-slate-600 mb-1">Data de Nascimento</label>
                                <input type="text" name="nascimento" id="nascimento" autocomplete="off"
                                       class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700"
                                       placeholder="dd/mm/aaaa" oninput="mascaraData(this)" maxlength="10"
                                       value="<?= htmlspecialchars(formatarData($pet['nascimento'] ?? '')); ?>">
                                <div id="erro-nascimento" class="text-red-500 text-sm mt-1"></div>
                            </div>
                            <div>
                                <label for="peso" class="block text-sm font-medium text-slate-600 mb-1">Peso (kg)</label>
                                <input type="number" name="peso" id="peso" min="0" step="0.01"
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

    <?php include '../components/footer.php'; ?>
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
        .select_2-container--default.select2-container--open .select2-selection--single {
            border-color: #3b82f6; /* blue-500 */
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.5); /* ring-2 ring-blue-500 */
        }
        .select2-dropdown {
            border: 1px solid #cbd5e1; /* slate-300 */
            border-radius: 0.375rem; /* rounded-md */
        }
    </style>
    <!-- jQuery (necessário para Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://npmcdn.com/flatpickr/dist/l10n/pt.js"></script>
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

    $(document).ready(function() {
        const especieSelect = $('#especie');
        const racaSelect = $('#raca');
        const racaAtual = "<?= htmlspecialchars($pet['raca'], ENT_QUOTES) ?>";

        racaSelect.select2({
            placeholder: "Selecione a raça"
        });

        let racasData = {};

        function carregarRacas(especieSelecionada) {
            racaSelect.empty().append('<option value="">Selecione a raça</option>');

            if (especieSelecionada && racasData[especieSelecionada]) {
                racasData[especieSelecionada].forEach(raca => {
                    const newOption = new Option(raca, raca, false, raca === racaAtual);
                    racaSelect.append(newOption);
                });
                racaSelect.prop('disabled', false);
            } else {
                racaSelect.prop('disabled', true);
            }
            racaSelect.trigger('change');
        }

        // Carrega o JSON e inicializa os campos
        $.getJSON('../data/racas.json', function(data) {
            racasData = data;
            carregarRacas(especieSelect.val());
        });

        // Adiciona o listener para quando o usuário mudar a espécie
        especieSelect.on('change', function() {
            carregarRacas($(this).val());
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