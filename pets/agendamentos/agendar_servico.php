<?php
include "../../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit();
}

// Inicializa $pet_id e $pet
$pet_id = isset($_GET['pet_id']) ? $_GET['pet_id'] : null;
$pet = null;
$all_pets = [];

if ($pet_id) {
    // Se pet_id foi passado, busca informações do pet e do tutor
    $query_pet = "SELECT p.id, p.nome, t.nome as tutor_nome 
                  FROM pets p 
                  JOIN tutores t ON p.tutor_id = t.id 
                  WHERE p.id = :pet_id";
    $stmt_pet = $pdo->prepare($query_pet);
    $stmt_pet->bindValue(':pet_id', $pet_id, PDO::PARAM_INT);
    $stmt_pet->execute();
    $pet = $stmt_pet->fetch(PDO::FETCH_ASSOC);

    if (!$pet) {
        $_SESSION['mensagem'] = "Pet não encontrado.";
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: ../../dashboard.php");
        exit();
    }
} else {
    // Se pet_id não foi passado, busca todos os pets para seleção
    $query_all_pets = "SELECT p.id, p.nome, t.nome as tutor_nome FROM pets p JOIN tutores t ON p.tutor_id = t.id ORDER BY p.nome";
    $stmt_all_pets = $pdo->query($query_all_pets);
    $all_pets = $stmt_all_pets->fetchAll(PDO::FETCH_ASSOC);
}

if (!$pet && empty($all_pets)) { // Se não há pet específico e nem pets para selecionar
    $_SESSION['mensagem'] = "Pet não encontrado.";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../../dashboard.php");
    exit();
}

// Buscar serviços disponíveis
$query_servicos = "SELECT id, nome FROM servicos WHERE id != 99 ORDER BY nome"; // Exclui 'Outros' da lista principal
$stmt_servicos = $pdo->query($query_servicos);
$servicos = $stmt_servicos->fetchAll(PDO::FETCH_ASSOC);

// Garante que o serviço "Outros" com ID 99 exista
$pdo->query("INSERT IGNORE INTO servicos (id, nome) VALUES (99, 'Outros')");

// Processar o formulário de agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $pet_id_post = $_POST['pet_id'];
        $servicos_selecionados = isset($_POST['servico_id']) ? (array)$_POST['servico_id'] : [];
        $servico_outros_selecionado = isset($_POST['servico_outros']);
        $servico_outros_detalhes = trim($_POST['servico_outros_detalhes'] ?? '');
        $data = $_POST['data'];
        $horario = $_POST['horario'];
        $transporte = $_POST['transporte'] ?? 'Não';
        $observacoes = $_POST['observacoes'] ?? null;
        $usuario_id = $_SESSION['usuario_id'];

        if (empty($servicos_selecionados) && !$servico_outros_selecionado) {
            throw new Exception("Selecione pelo menos um serviço.");
        }
        if (empty($pet_id_post)) {
            throw new Exception("Selecione um pet para o agendamento.");
        }

        $data_agendamento = new DateTime($data);
        $hoje = new DateTime('today');
        if ($data_agendamento < $hoje) {
            throw new Exception("Não é possível agendar para datas passadas.");
        }

        // Adiciona o serviço "Outros" à lista se ele foi selecionado
        if ($servico_outros_selecionado) {
            $servicos_selecionados[] = 99; // ID do serviço "Outros"
        }

        // Cria um agendamento para cada serviço selecionado
        foreach ($servicos_selecionados as $servico_id) {
            // Para o serviço "Outros", a observação vai no campo principal do agendamento
            $obs_final = $observacoes;
            if ($servico_id == 99 && !empty($servico_outros_detalhes)) {
                $obs_final = "Serviço Extra: " . $servico_outros_detalhes . "\n" . $observacoes;
            }

            $query_agendamento = "INSERT INTO agendamentos 
                                 (pet_id, usuario_id, data_hora, servico_id, transporte, status, observacoes) 
                                 VALUES 
                                 (:pet_id, :usuario_id, :data_hora, :servico_id, :transporte, 'Pendente', :observacoes)";
            $stmt_agendamento = $pdo->prepare($query_agendamento);
            $stmt_agendamento->bindValue(':pet_id', $pet_id_post, PDO::PARAM_INT);
            $stmt_agendamento->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt_agendamento->bindValue(':data_hora', $data . ' ' . $horario);
            $stmt_agendamento->bindValue(':servico_id', $servico_id, PDO::PARAM_INT);
            $stmt_agendamento->bindValue(':transporte', $transporte);
            $stmt_agendamento->bindValue(':observacoes', $obs_final);
            $stmt_agendamento->execute();
        }

        $pdo->commit();

        $_SESSION['mensagem'] = "Agendamento realizado com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
        header("Location: ../visualizar_pet.php?id=" . $pet_id_post);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mensagem'] = "Erro ao agendar: " . $e->getMessage();
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: agendar_servico.php?pet_id=".$pet_id_post); // Usar pet_id_post para manter o contexto
        exit(); // Adicionado exit() aqui
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Serviço - CereniaPet</title>
    <link rel="icon" type="image/x-icon" href="../../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <?php
    $path_prefix = '../../';
    include '../../components/navbar.php';
    ?>
    <?php include '../../components/toast.php'; ?>

    <main class="flex-1 w-full p-4 md:p-6 lg:p-8">
        <!-- Cabeçalho -->
        <div class="mb-8 animate-fade-in">
            <h1 class="text-3xl font-bold text-slate-800">Agendar Serviço</h1>
            <p class="text-slate-500 mt-1">Selecione os serviços e o horário para o pet.</p>
        </div>

        <div class="bg-white p-6 md:p-8 rounded-lg shadow-sm animate-fade-in">
            <form method="POST" class="space-y-6" id="agendamentoForm">
                <?php if ($pet): ?>
                    <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">

                    <!-- Informações do Pet e Tutor (quando pet_id é fornecido) -->
                    <div class="bg-slate-50 p-5 rounded-lg border border-slate-200 mb-6">
                        <h2 class="text-xl font-bold text-slate-800 mb-3 flex items-center gap-3">
                            <i class="fa-solid fa-dog text-violet-500"></i> Pet: <?= htmlspecialchars($pet['nome']); ?>
                        </h2>
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-3 text-sm">
                            <div>
                                <dt class="font-medium text-slate-500">Tutor</dt>
                                <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($pet['tutor_nome']); ?></dd>
                            </div>
                            <div>
                                <dt class="font-medium text-slate-500">ID do Pet</dt>
                                <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($pet['id']); ?></dd>
                            </div>
                        </dl>
                    </div>
                <?php else: ?>
                    <!-- Seleção de Pet (quando pet_id NÃO é fornecido) -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mb-4 flex items-center gap-3">
                            <i class="fa-solid fa-paw text-violet-500"></i> Selecione o Pet <span class="text-red-500">*</span>
                        </h3>
                        <select name="pet_id" id="pet_id_select" required class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-700">
                            <option value="">Selecione um Pet</option>
                            <?php foreach ($all_pets as $p): ?>
                                <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nome']) ?> (Tutor: <?= htmlspecialchars($p['tutor_nome']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                        <?php if (empty($all_pets)): ?>
                            <p class="text-red-500 text-sm mt-2">Nenhum pet cadastrado. Cadastre um pet antes de agendar um serviço.</p>
                        <?php endif; ?>
                        <div class="mt-4 p-3 bg-blue-50 border border-blue-100 rounded-lg text-blue-800 text-sm">
                            <i class="fas fa-info-circle mr-2"></i> Você pode adicionar um novo pet <a href="../cadastrar_pet.php" class="underline font-semibold hover:text-blue-900">clicando aqui</a>.
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Data e Hora -->
                <div>
                    <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mb-4 flex items-center gap-3">
                        <i class="fa-solid fa-calendar-alt text-sky-500"></i> Data e Hora <span class="text-red-500">*</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="data" class="block text-sm font-medium text-slate-600 mb-1">Data</label>
                            <input type="date" name="data" id="data" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div>
                            <label for="horario" class="block text-sm font-medium text-slate-600 mb-1">Horário</label>
                            <select name="horario" id="horario" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required disabled>
                                <option value="">Selecione uma data primeiro</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Seleção de Serviços -->
                <div>
                    <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mb-4 flex items-center gap-3">
                        <i class="fa-solid fa-scissors text-green-500"></i> Seleção de Serviços <span class="text-red-500">*</span>
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <?php foreach ($servicos as $servico): ?>
                            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="servico_id[]" value="<?= $servico['id'] ?>" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-slate-700"><?= htmlspecialchars($servico['nome']) ?></span>
                            </label>
                        <?php endforeach; ?>
                        <!-- Campo "Outros" com input de texto -->
                        <div class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 col-span-2 sm:col-span-1">
                            <input type="checkbox" id="servico_outros_check" name="servico_outros" value="99" class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="servico_outros_check" class="text-slate-700 cursor-pointer">Outros:</label>
                            <input type="text" name="servico_outros_detalhes" id="servico_outros_detalhes" class="flex-1 p-1 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm disabled:bg-slate-100 disabled:cursor-not-allowed" placeholder="Especifique o serviço" disabled>
                        </div>
                    </div>
                </div>

                <!-- Outras Informações -->
                <div>
                    <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mb-4 flex items-center gap-3">
                        <i class="fa-solid fa-circle-info text-amber-500"></i> Outras Informações
                    </h3>
                    <div class="space-y-4">
                        <div>
                            <label for="transporte" class="block text-sm font-medium text-slate-600 mb-1">Transporte</label>
                            <select name="transporte" id="transporte" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="Não" selected>Não necessito de transporte</option>
                                <option value="Sim">Sim, necessito de transporte</option>
                            </select>
                        </div>
                        <div>
                            <label for="observacoes" class="block text-sm font-medium text-slate-600 mb-1">Observações Gerais</label>
                            <textarea name="observacoes" id="observacoes" rows="3" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Informações adicionais sobre o serviço ou o pet (ex: alergias, comportamento)"></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-8 mt-8 border-t border-slate-200">
                    <a href="<?= $pet ? '../visualizar_pet.php?id='.$pet['id'] : '../../dashboard.php' ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        <i class="fa fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        <i class="fa fa-calendar-check"></i> Confirmar Agendamento
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
            background-color: white;
            border: 1px solid #cbd5e1; /* slate-300 */
            border-radius: 0.375rem; /* rounded-md */
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1); /* shadow-md */
        }
        .select2-container--default .select2-search--dropdown .select2-search__field {
            border: 1px solid #cbd5e1; /* slate-300 */
            border-radius: 0.375rem; /* rounded-md */
        }
        .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
            background-color: #3b82f6; /* blue-500 */
            color: white;
        }
    </style>
    <!-- jQuery (necessário para Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#pet_id_select').select2({
                placeholder: "Selecione um Pet",
                allowClear: true // Permite desmarcar a seleção
            });

            const outrosCheck = document.getElementById('servico_outros_check');
            const outrosDetalhesInput = document.getElementById('servico_outros_detalhes');

            outrosCheck.addEventListener('change', function() {
                if (this.checked) {
                    outrosDetalhesInput.disabled = false;
                } else {
                    outrosDetalhesInput.disabled = true;
                    outrosDetalhesInput.value = '';
                }
            });

            const dataInput = document.getElementById('data');
            const horarioSelect = document.getElementById('horario');

            function buscarHorariosDisponiveis() {
                const data = dataInput.value;
                if (!data) {
                    horarioSelect.innerHTML = '<option value="">Selecione uma data primeiro</option>';
                    horarioSelect.disabled = true;
                    return;
                }

                horarioSelect.innerHTML = '<option value="">Carregando...</option>';
                horarioSelect.disabled = true;

                fetch(`buscar_horarios_disponiveis.php?data=${data}`)
                    .then(response => response.json())
                    .then(horarios => {
                        horarioSelect.innerHTML = '';
                        if (horarios.length > 0) {
                            horarios.forEach(horario => {
                                horarioSelect.innerHTML += `<option value="${horario}">${horario}</option>`;
                            });
                        } else {
                            horarioSelect.innerHTML = '<option value="">Nenhum horário disponível</option>';
                        }
                        horarioSelect.disabled = false;
                    });
            }

            dataInput.addEventListener('change', buscarHorariosDisponiveis);
        });
    </script>
</body>
</html>