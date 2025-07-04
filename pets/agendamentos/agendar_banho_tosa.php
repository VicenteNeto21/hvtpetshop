<?php
include "../../config/config.php";
session_start();

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

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit();
}

// Verifica se o pet_id foi passado
if (!isset($_GET['pet_id'])) {
    $_SESSION['mensagem'] = "Pet não especificado para agendamento";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../dashboard.php");
    exit();
}

$pet_id = $_GET['pet_id'];

// Buscar informações do pet e do tutor
$query_pet = "SELECT p.*, t.nome as tutor_nome, t.telefone as tutor_telefone 
              FROM pets p 
              JOIN tutores t ON p.tutor_id = t.id 
              WHERE p.id = :pet_id";
$stmt_pet = $pdo->prepare($query_pet);
$stmt_pet->bindValue(':pet_id', $pet_id, PDO::PARAM_INT);
$stmt_pet->execute();
$pet = $stmt_pet->fetch(PDO::FETCH_ASSOC);

if (!$pet) {
    $_SESSION['mensagem'] = "Pet não encontrado";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../dashboard.php");
    exit();
}

// Buscar serviços disponíveis
$query_servicos = "SELECT id, nome FROM servicos ORDER BY nome";
$stmt_servicos = $pdo->query($query_servicos);
$servicos = $stmt_servicos->fetchAll(PDO::FETCH_ASSOC);

// Processar o formulário de agendamento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $pet_id = $_POST['pet_id'];
        $servicos_selecionados = $_POST['servico_id'] ?? [];
        $novos_servicos = $_POST['novo_servico'] ?? [];
        $data = $_POST['data'];
        $horario = $_POST['horario'];
        $transporte = $_POST['transporte'];
        $observacoes = $_POST['observacoes'];
        $usuario_id = $_SESSION['usuario_id'];

        $data_agendamento = new DateTime($data);
        $hoje = new DateTime('today');
        if ($data_agendamento < $hoje) {
            throw new Exception("Não é possível agendar para datas passadas");
        }

        // Adiciona novos serviços se houver
        foreach ($novos_servicos as $novo_servico) {
            $novo_servico = trim($novo_servico);
            if (!empty($novo_servico)) {
                $query_novo_servico = "INSERT INTO servicos (nome) VALUES (:nome)";
                $stmt_novo_servico = $pdo->prepare($query_novo_servico);
                $stmt_novo_servico->bindValue(':nome', $novo_servico);
                $stmt_novo_servico->execute();
                $servicos_selecionados[] = $pdo->lastInsertId();
            }
        }

        // Cria um agendamento para cada serviço selecionado
        foreach ($servicos_selecionados as $servico_id) {
            if (empty($servico_id) || $servico_id === 'novo') continue;
            $query_agendamento = "INSERT INTO agendamentos 
                                 (pet_id, usuario_id, data_hora, servico_id, transporte, status, observacoes) 
                                 VALUES 
                                 (:pet_id, :usuario_id, :data_hora, :servico_id, :transporte, 'Pendente', :observacoes)";
            $stmt_agendamento = $pdo->prepare($query_agendamento);
            $stmt_agendamento->bindValue(':pet_id', $pet_id, PDO::PARAM_INT);
            $stmt_agendamento->bindValue(':usuario_id', $usuario_id, PDO::PARAM_INT);
            $stmt_agendamento->bindValue(':data_hora', $data . ' ' . $horario);
            $stmt_agendamento->bindValue(':servico_id', $servico_id, PDO::PARAM_INT);
            $stmt_agendamento->bindValue(':transporte', $transporte);
            $stmt_agendamento->bindValue(':observacoes', $observacoes);
            $stmt_agendamento->execute();
        }

        $pdo->commit();

        $_SESSION['mensagem'] = "Agendamento realizado com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
        header("Location: ../visualizar_pet.php?id=".$pet_id);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mensagem'] = "Erro ao agendar: " . $e->getMessage();
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: agendar_banho_tosa.php?pet_id=".$pet_id);
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendar Serviço - HVTPETSHOP</title>
    <link rel="icon" type="image/x-icon" href="../../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <!-- Navbar padrão -->
    <nav class="w-full bg-white/90 shadow flex items-center justify-between px-6 py-3">
        <div class="flex items-center gap-3">
            <img src="../../icons/pet.jpg" alt="Logo Petshop" class="w-10 h-10 rounded-full shadow">
            <span class="text-2xl font-bold text-blue-700 tracking-tight">HVTPETSHOP</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="../../dashboard.php" class="text-blue-600 hover:text-blue-800 font-semibold transition"><i class="fa fa-home mr-1"></i>Dashboard</a>
            <a href="../cadastrar_pet.php" class="text-green-600 hover:text-green-800 font-semibold transition"><i class="fa fa-plus mr-1"></i>Novo Pet</a>
            <a href="../../tutores/listar_tutores.php" class="text-blue-500 hover:text-blue-700 font-semibold transition"><i class="fa fa-users mr-1"></i>Tutores</a>
            <a href="../../auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition"><i class="fa fa-sign-out-alt mr-1"></i>Sair</a>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-2xl mx-auto mt-10 p-4">
        <!-- Mensagens de feedback -->
        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="mb-6 p-4 rounded-lg <?= $_SESSION['tipo_mensagem'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <div class="flex items-center">
                    <i class="fas <?= $_SESSION['tipo_mensagem'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-3"></i>
                    <span><?= htmlspecialchars($_SESSION['mensagem']) ?></span>
                </div>
                <?php unset($_SESSION['mensagem']); unset($_SESSION['tipo_mensagem']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white/90 p-8 rounded-2xl shadow-xl animate-fade-in mb-8">
            <h2 class="text-2xl font-bold text-blue-700 mb-2 flex items-center gap-2">
                <i class="fa fa-calendar-plus"></i> Agendar Serviço para o Pet
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
                <div>
                    <div class="text-gray-500 text-xs">Nome do Pet</div>
                    <div class="font-semibold text-gray-700"><?= htmlspecialchars($pet['nome']) ?></div>
                </div>
                <div>
                    <div class="text-gray-500 text-xs">Tutor</div>
                    <div class="font-semibold text-gray-700"><?= htmlspecialchars($pet['tutor_nome']) ?></div>
                </div>
                <div>
                    <div class="text-gray-500 text-xs">Telefone do Tutor</div>
                    <div class="font-semibold text-gray-700"><?= htmlspecialchars(formatarTelefone($pet['tutor_telefone'])) ?></div>
                </div>
            </div>
        </div>

        <form method="POST" class="bg-white/90 p-8 rounded-2xl shadow-xl space-y-8 animate-fade-in">
            <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">

            <!-- Separador visual para Banho -->
            <div class="mb-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-1 h-px bg-blue-200"></div>
                    <span class="text-blue-500 font-bold text-base uppercase tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-soap"></i> Banho
                    </span>
                    <div class="flex-1 h-px bg-blue-200"></div>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Selecione os serviços de Banho</label>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach ($servicos as $servico): ?>
                            <?php if (stripos($servico['nome'], 'banho') !== false): ?>
                                <label class="flex items-center gap-2 bg-blue-50 px-3 py-1 rounded-lg border border-blue-100">
                                    <input type="checkbox" name="servico_id[]" value="<?= $servico['id'] ?>">
                                    <?= htmlspecialchars($servico['nome']) ?>
                                </label>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Separador visual para Tosa -->
            <div class="mb-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-1 h-px bg-pink-200"></div>
                    <span class="text-pink-500 font-bold text-base uppercase tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-scissors"></i> Tosa
                    </span>
                    <div class="flex-1 h-px bg-pink-200"></div>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Selecione os serviços de Tosa</label>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach ($servicos as $servico): ?>
                            <?php if (stripos($servico['nome'], 'tosa') !== false): ?>
                                <label class="flex items-center gap-2 bg-pink-50 px-3 py-1 rounded-lg border border-pink-100">
                                    <input type="checkbox" name="servico_id[]" value="<?= $servico['id'] ?>">
                                    <?= htmlspecialchars($servico['nome']) ?>
                                </label>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Separador visual para Outros Serviços -->
            <div class="mb-4">
                <div class="flex items-center gap-3 mb-4">
                    <div class="flex-1 h-px bg-green-200"></div>
                    <span class="text-green-600 font-bold text-base uppercase tracking-widest flex items-center gap-2">
                        <i class="fa-solid fa-plus"></i> Outros Serviços
                    </span>
                    <div class="flex-1 h-px bg-green-200"></div>
                </div>
                <div>
                    <label class="block text-gray-700 font-semibold mb-1">Selecione outros serviços</label>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach ($servicos as $servico): ?>
                            <?php if (
                                stripos($servico['nome'], 'banho') === false &&
                                stripos($servico['nome'], 'tosa') === false
                            ): ?>
                                <label class="flex items-center gap-2 bg-green-50 px-3 py-1 rounded-lg border border-green-100">
                                    <input type="checkbox" name="servico_id[]" value="<?= $servico['id'] ?>">
                                    <?= htmlspecialchars($servico['nome']) ?>
                                </label>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        <label class="flex items-center gap-2 bg-green-50 px-3 py-1 rounded-lg border border-green-100">
                            <input type="checkbox" id="add-novo-servico" value="novo">
                            <span>+ Adicionar novo serviço</span>
                        </label>
                    </div>
                    <div id="novo-servicos-container" class="hidden mt-3 space-y-2">
                        <input type="text" name="novo_servico[]" class="w-full p-3 border rounded-lg" placeholder="Nome do novo serviço">
                        <button type="button" id="btnAddNovoServico" class="text-blue-600 hover:underline text-sm">Adicionar outro serviço</button>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Data -->
                <div>
                    <label for="data" class="block text-gray-700 font-semibold mb-1">Data *</label>
                    <input type="date" name="data" id="data"
                           class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           min="<?= date('Y-m-d') ?>" required>
                </div>
                <!-- Horário -->
                <div>
                    <label for="horario" class="block text-gray-700 font-semibold mb-1">Horário *</label>
                    <input type="time" name="horario" id="horario"
                           class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                           min="08:00" max="18:00" step="1800" required>
                </div>
                <!-- Transporte -->
                <div>
                    <label for="transporte" class="block text-gray-700 font-semibold mb-1">Transporte</label>
                    <select name="transporte" id="transporte"
                            class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="Não" selected>Não necessito de transporte</option>
                        <option value="Sim">Sim, necessito de transporte</option>
                    </select>
                </div>
                <!-- Observações -->
                <div class="md:col-span-2">
                    <label for="observacoes" class="block text-gray-700 font-semibold mb-1">Observações</label>
                    <textarea name="observacoes" id="observacoes" rows="3"
                              class="w-full p-3 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                              placeholder="Informações adicionais sobre o serviço ou o pet"></textarea>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4">
                <a href="../visualizar_pet.php?id=<?= $pet['id'] ?>"
                   class="bg-gray-500 hover:bg-gray-600 text-white py-2 px-6 rounded-lg flex items-center justify-center">
                    <i class="fas fa-times mr-2"></i> Cancelar
                </a>
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg flex items-center justify-center">
                    <i class="fas fa-calendar-check mr-2"></i> Confirmar Agendamento
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
        // Mostrar/ocultar campo de novo serviço
        const addNovoServico = document.getElementById('add-novo-servico');
        const novoServicosContainer = document.getElementById('novo-servicos-container');
        const btnAddNovoServico = document.getElementById('btnAddNovoServico');

        addNovoServico.addEventListener('change', function() {
            if (this.checked) {
                novoServicosContainer.classList.remove('hidden');
                novoServicosContainer.querySelector('input').required = true;
            } else {
                novoServicosContainer.classList.add('hidden');
                novoServicosContainer.querySelector('input').required = false;
            }
        });

        btnAddNovoServico.addEventListener('click', function(e) {
            e.preventDefault();
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'novo_servico[]';
            input.className = 'w-full p-3 border rounded-lg mt-2';
            input.placeholder = 'Nome do novo serviço';
            novoServicosContainer.insertBefore(input, btnAddNovoServico);
        });

        // Data mínima hoje
        const dataInput = document.getElementById('data');
        dataInput.min = new Date().toISOString().split('T')[0];
        dataInput.addEventListener('change', function() {
            const selectedDate = new Date(this.value + 'T00:00:00');
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            if (selectedDate < today) {
                alert('Não é possível agendar para datas passadas');
                this.value = '';
            }
        });

        // Horário comercial
        document.getElementById('horario').addEventListener('change', function() {
            const hora = parseInt(this.value.split(':')[0]);
            if (hora < 8 || hora >= 18) {
                alert('Horário de atendimento é das 8h às 18h');
                this.value = '';
            }
        });
    </script>
</body>
</html>