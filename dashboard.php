<?php
include "./config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

// Controle do aviso de funcionalidades por versão
$versao_atual_aviso = '1.1.0';

// Garante que a versão vista esteja na sessão, buscando do DB se necessário
if (isset($_SESSION['usuario_id']) && !isset($_SESSION['aviso_visto_versao'])) {
    $stmt = $pdo->prepare("SELECT versao_aviso_visto FROM usuarios WHERE id = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    $versao_vista_db = $stmt->fetchColumn();
    $_SESSION['aviso_visto_versao'] = $versao_vista_db;
}

$mostrarAviso = (!isset($_SESSION['aviso_visto_versao']) || $_SESSION['aviso_visto_versao'] !== $versao_atual_aviso);

// Obtém o termo de busca, se houver
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Consulta os pets baseados na busca, incluindo o nome do tutor
$query = "SELECT pets.id, pets.nome, tutores.nome AS tutor 
          FROM pets 
          INNER JOIN tutores ON pets.tutor_id = tutores.id";

if ($search) {
    $query .= " WHERE pets.nome LIKE :search OR tutores.nome LIKE :search";
}
// Altere a ordenação para mostrar os últimos pets cadastrados primeiro
$query .= " ORDER BY pets.id DESC";

$stmt = $pdo->prepare($query);
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Contagem total de pets cadastrados
$totalPets = $pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();

// Contagem total de tutores cadastrados
$totalTutores = $pdo->query("SELECT COUNT(*) FROM tutores")->fetchColumn();

// Contagem de agendamentos para o dia de hoje
$totalAgendamentosHoje = $pdo->query("SELECT COUNT(DISTINCT pet_id, data_hora) FROM agendamentos WHERE DATE(data_hora) = CURDATE()")->fetchColumn();

// Consulta agendamentos para uma data específica (padrão: hoje)
$dataSelecionada = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');

$stmtAgendamentos = $pdo->prepare("
    SELECT
        MIN(a.id) as agendamento_id,
        p.id as pet_id,
        t.id as tutor_id,
        p.nome as pet_nome,
        t.nome as tutor_nome,
        DATE_FORMAT(a.data_hora, '%H:%i') AS horario,
        GROUP_CONCAT(DISTINCT s.nome ORDER BY s.nome SEPARATOR ', ') AS servicos,
        CASE
            WHEN SUM(CASE WHEN a.status = 'Em Atendimento' THEN 1 ELSE 0 END) > 0 THEN 'Em Atendimento'
            WHEN SUM(CASE WHEN a.status = 'Pendente' THEN 1 ELSE 0 END) > 0 THEN 'Pendente'
            ELSE MIN(a.status)
        END AS status
    FROM agendamentos a
    JOIN pets p ON a.pet_id = p.id
    JOIN tutores t ON p.tutor_id = t.id
    JOIN servicos s ON a.servico_id = s.id
    WHERE DATE(a.data_hora) = :data_selecionada
    GROUP BY p.id, t.id, p.nome, t.nome, horario
    ORDER BY horario ASC
");
$stmtAgendamentos->execute([':data_selecionada' => $dataSelecionada]);
$agendamentosHoje = $stmtAgendamentos->fetchAll(PDO::FETCH_ASSOC);

// Aniversariantes do dia
$stmtAniversariantes = $pdo->prepare("
    SELECT p.nome as pet_nome, t.nome as tutor_nome
    FROM pets p
    JOIN tutores t ON p.tutor_id = t.id
    WHERE MONTH(p.nascimento) = MONTH(:data_selecionada) AND DAY(p.nascimento) = DAY(:data_selecionada)
");
$stmtAniversariantes->execute([':data_selecionada' => $dataSelecionada]);
$aniversariantes = $stmtAniversariantes->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"> 
    <title>Dashboard - CereniaPet</title>
    <link rel="icon" type="image/x-icon" href="./icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Função para fechar o modal de aviso e marcar como visto
        function fecharAvisoNovidades() {
            fetch('./utils/marcar_aviso_visto.php', { method: 'POST' })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        document.getElementById('aviso-funcionalidades').remove();
                    }
                });
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <?php
    // Define o prefixo do caminho para o navbar. Como estamos na raiz, é './'
    $path_prefix = './';
    include './components/navbar.php';
    ?>

    <?php include './components/toast.php'; ?>

    <?php if ($mostrarAviso): ?>
    <!-- Aviso de funcionalidades em desenvolvimento -->
    <div id="aviso-funcionalidades" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl p-8 max-w-lg w-full text-left relative animate-fade-in">
            <button onclick="fecharAvisoNovidades()" class="absolute top-3 right-4 text-slate-400 hover:text-red-500 text-2xl font-bold" title="Fechar">&times;</button>
            <div class="flex flex-col gap-2">
                <div class="flex items-center gap-3 mb-2">
                    <i class="fa-solid fa-star text-amber-500 text-3xl"></i>
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">Bem-vindo à Versão 1.1.0!</h2>
                        <p class="text-slate-500">O sistema CereniaPet está mais completo e estável.</p>
                    </div>
                </div>
                <ul class="space-y-2 text-slate-600 list-disc list-inside pl-2">
                    <li><strong>Dashboard Inteligente:</strong> Navegue pela agenda, veja aniversariantes e tenha acesso rápido a tudo.</li>
                    <li><strong>Gestão Completa:</strong> Cadastre e gerencie tutores e pets com históricos detalhados.</li>
                    <li><strong>Fichas de Atendimento Profissionais:</strong> Registre todos os detalhes do serviço, da saúde ao comportamento do pet.</li>
                    <li><strong>Geração de PDF:</strong> Exporte fichas de atendimento completas com um clique.</li>
                    <li><strong>Nova Identidade:</strong> O sistema agora se chama oficialmente <strong>CereniaPet</strong>.</li>
                </ul>
                <div class="flex flex-col items-center mt-6">
                    <button onclick="fecharAvisoNovidades()" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-6 rounded-lg shadow-sm transition">Ok, entendi!</button>
                    <p class="text-xs text-slate-400 mt-3">Esta mensagem aparecerá apenas uma vez por atualização.</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <main class="flex-1 w-full p-4 md:p-6 lg:p-8">
        <!-- Estatísticas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-10 animate-fade-in">
            <!-- Card Agendamentos Hoje (Clicável) -->
            <a href="pets/agendamentos/agendar_servico.php" class="bg-white border-l-4 border-sky-500 rounded-r-lg p-5 shadow-sm hover:bg-slate-50 transition">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Agendamentos Hoje</p>
                    <i class="fa-solid fa-calendar-day text-sky-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalAgendamentosHoje; ?></p>
            </a>
            <!-- Card Total de Pets -->
            <div class="bg-white border-l-4 border-violet-500 rounded-r-lg p-5 shadow-sm">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Total de Pets</p>
                    <i class="fa-solid fa-paw text-violet-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalPets; ?></p>
            </div>
            <!-- Card Total de Tutores -->
            <a href="tutores/listar_tutores.php" class="bg-white border-l-4 border-amber-500 rounded-r-lg p-5 shadow-sm hover:bg-slate-50 transition">
                <div class="flex justify-between items-center">
                    <p class="text-sm font-medium text-slate-500">Total de Tutores</p>
                    <i class="fa-solid fa-users text-amber-500"></i>
                </div>
                <p class="text-3xl font-bold text-slate-800 mt-2"><?= $totalTutores; ?></p>
            </a>
        </div>

        <!-- Conteúdo Principal (Tabelas) -->
        <div class="space-y-10">
            <!-- Agendamentos do dia -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in"> 
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                        <i class="fa fa-clock text-sky-500"></i> Agenda para <?= date('d/m/Y', strtotime($dataSelecionada)) ?>
                    </h2>
                    <div class="flex items-center gap-2 ml-auto">
                        <?php
                            $dataAnterior = date('Y-m-d', strtotime($dataSelecionada . ' -1 day'));
                            $dataSeguinte = date('Y-m-d', strtotime($dataSelecionada . ' +1 day'));
                            $isToday = ($dataSelecionada == date('Y-m-d'));
                        ?>
                        <a href="?data=<?= $dataAnterior ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-1.5 rounded-lg font-semibold shadow-sm transition text-sm">
                            &lt;
                        </a>
                        <?php if (!$isToday): ?>
                            <a href="dashboard.php" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-1.5 rounded-lg font-semibold shadow-sm transition text-sm">
                                Hoje
                            </a>
                        <?php endif; ?>
                        <a href="?data=<?= $dataSeguinte ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-3 py-1.5 rounded-lg font-semibold shadow-sm transition text-sm">
                            &gt;
                        </a>
                        <a href="pets/agendamentos/agendar_servico.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition flex items-center gap-2 text-sm whitespace-nowrap ml-2">
                            <i class="fa fa-calendar-plus"></i> Novo Agendamento
                        </a>
                    </div>
                </div>
                <?php if (empty($agendamentosHoje)): ?>
                    <div class="text-slate-500 text-center py-8 border-2 border-dashed rounded-lg">Nenhum agendamento pendente para hoje.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="border-b-2 border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Horário</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Pet</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Tutor</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Serviços</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($agendamentosHoje as $ag): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-4 font-medium text-slate-800 whitespace-nowrap"><?= htmlspecialchars($ag['horario']) ?></td>
                                        <td class="px-4 py-4 text-slate-800 font-semibold">
                                            <a href="./pets/visualizar_pet.php?id=<?= $ag['pet_id'] ?>" class="hover:underline"><?= htmlspecialchars($ag['pet_nome']) ?></a>
                                        </td>
                                        <td class="px-4 py-4 text-slate-500 whitespace-nowrap">
                                            <a href="./tutores/visualizar_tutor.php?id=<?= $ag['tutor_id'] ?>" class="hover:underline"><?= htmlspecialchars($ag['tutor_nome']) ?></a>
                                        </td>
                                        <td class="px-4 py-4 text-slate-500"><?= htmlspecialchars($ag['servicos'] ?: 'N/A') ?></td>
                                        <td class="px-4 py-4">
                                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold
                                                <?= $ag['status'] == 'Pendente' ? 'bg-yellow-100 text-yellow-800' : 
                                                   ($ag['status'] == 'Em Atendimento' ? 'bg-blue-100 text-blue-800' : 
                                                   ($ag['status'] == 'Finalizado' ? 'bg-green-100 text-green-800' : 
                                                   ($ag['status'] == 'Cancelado' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-800'))); ?>">
                                                <?= htmlspecialchars($ag['status']) ?>
                                            </span>
                                        </td>
                                        <td class="px-4 py-4 text-center">
                                            <div class="flex items-center justify-center gap-3">
                                                <?php if ($ag['status'] == 'Pendente'): ?>
                                                    <button onclick="updateAgendamentoStatus(<?= $ag['agendamento_id'] ?>, 'Em Atendimento')" class="w-8 h-8 flex items-center justify-center rounded-full text-blue-600 hover:bg-blue-100 hover:text-blue-800 transition" title="Iniciar Atendimento"><i class="fas fa-play-circle fa-lg"></i></button>
                                                    <a href="javascript:void(0);" onclick="openConfirmationModal('Cancelar Agendamento', 'Tem certeza que deseja cancelar este agendamento?', 'pets/agendamentos/cancelar_agendamento_action.php?id=<?= $ag['agendamento_id'] ?>&pet_id=<?= $ag['pet_id'] ?>')" class="w-8 h-8 flex items-center justify-center rounded-full text-red-600 hover:bg-red-100 hover:text-red-800 transition" title="Cancelar"><i class="fas fa-times-circle fa-lg"></i></a>
                                                <?php elseif ($ag['status'] == 'Em Atendimento'): ?>
                                                    <a href="./pets/agendamentos/editar_agendamento.php?id=<?= $ag['agendamento_id'] ?>" class="w-8 h-8 flex items-center justify-center rounded-full text-amber-600 hover:bg-amber-100 hover:text-amber-800 transition" title="Preencher Ficha"><i class="fas fa-file-alt fa-lg"></i></a>
                                                <?php elseif ($ag['status'] == 'Finalizado'): ?>
                                                    <a href="./pets/agendamentos/visualizar_ficha.php?id=<?= $ag['agendamento_id'] ?>" class="w-8 h-8 flex items-center justify-center rounded-full text-green-600 hover:bg-green-100 hover:text-green-800 transition" title="Visualizar Ficha"><i class="fas fa-eye fa-lg"></i></a>
                                                <?php else: ?>
                                                    <span class="w-8 h-8 flex items-center justify-center rounded-full text-slate-400" title="Agendamento Cancelado"><i class="fas fa-ban fa-lg"></i></span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Aniversariantes do Dia -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-cake-candles text-pink-500"></i>
                    Aniversariantes do Dia
                </h2>
                <?php if (empty($aniversariantes)): ?>
                    <div class="text-slate-500 text-center py-8 border-2 border-dashed rounded-lg">Nenhum aniversariante hoje.</div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php foreach ($aniversariantes as $aniversariante): ?>
                            <div class="bg-slate-50 p-4 rounded-lg border border-slate-200 flex items-center gap-3">
                                <i class="fa-solid fa-paw text-violet-500 text-xl"></i>
                                <div>
                                    <p class="font-semibold text-slate-800"><?= htmlspecialchars($aniversariante['pet_nome']) ?></p>
                                    <p class="text-xs text-slate-500">Tutor: <?= htmlspecialchars($aniversariante['tutor_nome']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Listagem de Pets -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                    <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                        <i class="fa-solid fa-paw text-violet-500"></i>
                        Pets Cadastrados
                    </h2>
                    <form action="dashboard.php" method="GET" class="relative w-full md:w-auto md:max-w-xs">
                        <input type="text" id="searchInput" value="<?= htmlspecialchars($search); ?>" placeholder="Pesquisar por ID, pet ou tutor..." 
                               class="w-full p-2 pl-10 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm">
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </form>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b-2 border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider w-16">ID</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Pet</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Tutor</th>
                                <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="pets-tbody" class="divide-y divide-slate-100">
                            <!-- O conteúdo será carregado via AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
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
        @keyframes slide-in-right {
            from { opacity: 0; transform: translateX(100%); }
            to { opacity: 1; transform: translateX(0); }
        }
        .animate-slide-in-right { animation: slide-in-right 0.5s ease-out forwards; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const petsTbody = document.getElementById('pets-tbody');

            function buscarPets(termo = '') {
                // Adiciona um efeito de loading
                petsTbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-slate-500"><i class="fas fa-spinner fa-spin mr-2"></i>Buscando...</td></tr>';

                fetch(`pets/buscar_pets.php?search=${encodeURIComponent(termo)}`)
                    .then(response => response.text())
                    .then(data => {
                        petsTbody.innerHTML = data;
                    })
                    .catch(error => {
                        console.error('Erro ao buscar pets:', error);
                        petsTbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-red-500">Erro ao carregar os dados.</td></tr>';
                    });
            }

            // Busca inicial ao carregar a página
            buscarPets(searchInput.value);

            // Busca ao digitar
            searchInput.addEventListener('keyup', () => buscarPets(searchInput.value));

            // Função para atualizar status do agendamento via AJAX
            window.updateAgendamentoStatus = function(agendamentoId, newStatus) {
                const formData = new FormData();
                formData.append('agendamento_id', agendamentoId);
                formData.append('new_status', newStatus);

                fetch('pets/agendamentos/atualizar_status_agendamento.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Recarrega a página para refletir a mudança
                        location.reload(); 
                    } else {
                        alert('Erro ao atualizar status: ' + data.message);
                    }
                })
                .catch(error => {
                    console.error('Erro na requisição AJAX:', error);
                    alert('Erro de comunicação com o servidor.');
                });
            };
        });
    </script>
</body>
</html>