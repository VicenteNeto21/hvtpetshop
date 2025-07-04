<?php
include "./config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.html");
    exit();
}

// Controle do aviso de funcionalidades
$mostrarAviso = !isset($_SESSION['aviso_funcionalidades_visto']);
if (isset($_GET['fechar_aviso'])) {
    $_SESSION['aviso_funcionalidades_visto'] = true;
    header("Location: dashboard.php");
    exit();
}

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

// Consulta agendamentos do dia atual, mostrando apenas o próximo horário e apenas serviços NÃO finalizados
$dataHoje = date('Y-m-d');
$stmtAgendamentos = $pdo->prepare("
    SELECT 
        MIN(agendamento_id) as agendamento_id,
        pet_nome,
        tutor_nome,
        DATE_FORMAT(data_hora, '%H:%i') AS horario,
        GROUP_CONCAT(DISTINCT servico_nome ORDER BY servico_nome SEPARATOR ', ') AS servicos,
        MIN(status_agendamento) AS status
    FROM view_agendamentos_completos
    WHERE DATE(data_hora) = :hoje
      AND status_agendamento != 'Finalizado'
    GROUP BY pet_nome, horario, tutor_nome
    ORDER BY horario ASC
");
$stmtAgendamentos->execute([':hoje' => $dataHoje]);
$agendamentosHoje = $stmtAgendamentos->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - HVTPETSHOP</title>
    <link rel="icon" type="image/x-icon" href="./icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <!-- Navbar responsiva -->
    <nav class="w-full bg-white/90 shadow flex items-center justify-between px-4 md:px-6 py-3">
        <div class="flex items-center gap-3">
            <img src="./icons/pet.jpg" alt="Logo Petshop" class="w-10 h-10 rounded-full shadow">
            <span class="text-xl md:text-2xl font-bold text-blue-700 tracking-tight">HVTPETSHOP</span>
        </div>
        <!-- Botão hamburguer mobile -->
        <button id="menu-toggle" class="md:hidden text-blue-700 text-2xl focus:outline-none">
            <i class="fa fa-bars"></i>
        </button>
        <!-- Links da navbar -->
        <div id="navbar-links" class="hidden md:flex flex-col md:flex-row md:items-center gap-4 absolute md:static top-16 left-0 w-full md:w-auto bg-white md:bg-transparent shadow md:shadow-none z-50 md:z-auto px-4 md:px-0 py-4 md:py-0">
            <a href="dashboard.php" class="text-blue-600 hover:text-blue-800 font-semibold transition flex items-center gap-1"><i class="fa fa-home"></i><span>Dashboard</span></a>
            <a href="pets/cadastrar_pet.php" class="text-green-600 hover:text-green-800 font-semibold transition flex items-center gap-1"><i class="fa fa-plus"></i><span>Novo Pet</span></a>
            <a href="tutores/listar_tutores.php" class="text-blue-500 hover:text-blue-700 font-semibold transition flex items-center gap-1"><i class="fa fa-users"></i><span>Tutores</span></a>
            <a href="dashboard/indicadores.php" class="bg-blue-500 hover:bg-blue-700 text-white px-3 py-2 rounded-lg font-semibold shadow flex items-center gap-2 transition">
                <i class="fa fa-chart-bar"></i> <span>Indicadores</span>
            </a>
            <a href="#" class="bg-green-500 hover:bg-green-600 text-white px-3 py-2 rounded-lg font-semibold shadow flex items-center gap-2 transition">
                <i class="fa fa-cash-register"></i> <span>PDV (em breve)</span>
            </a>
            <a href="auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition flex items-center gap-1"><i class="fa fa-sign-out-alt"></i><span>Sair</span></a>
        </div>
    </nav>
    <script>
        // Navbar responsiva
        const menuToggle = document.getElementById('menu-toggle');
        const navbarLinks = document.getElementById('navbar-links');
        menuToggle.addEventListener('click', () => {
            navbarLinks.classList.toggle('hidden');
        });
        // Fecha menu ao clicar em link (mobile)
        document.querySelectorAll('#navbar-links a').forEach(link => {
            link.addEventListener('click', () => {
                if(window.innerWidth < 768) navbarLinks.classList.add('hidden');
            });
        });
    </script>

    <?php if ($mostrarAviso): ?>
    <!-- Aviso de funcionalidades em desenvolvimento -->
    <div id="aviso-funcionalidades" class="fixed inset-0 bg-black/30 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl shadow-xl p-6 max-w-md w-full text-center relative animate-fade-in">
            <a href="?fechar_aviso=1" class="absolute top-3 right-3 text-gray-400 hover:text-red-500 text-xl font-bold" title="Fechar">&times;</a>
            <div class="flex flex-col items-center gap-2">
                <i class="fa fa-info-circle text-blue-500 text-4xl mb-2"></i>
                <h2 class="text-xl font-bold text-blue-700 mb-1">Atenção!</h2>
                <p class="text-gray-700 mb-2">
                    Novas funcionalidades estão sendo adicionadas ao sistema.<br>
                    Algumas áreas podem ser atualizadas ou melhoradas nos próximos dias.<br>
                    Se encontrar algum problema, entre em contato com o suporte.
                </p>
                <span class="text-xs text-gray-400 mt-2">Versão do sistema: <strong>AMPN 1.0.5</strong></span>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <main class="flex-1 w-full max-w-6xl mx-auto mt-6 md:mt-10 p-2 md:p-4">
        <div class="bg-white/90 p-4 md:p-6 rounded-2xl shadow mb-6 text-center animate-fade-in">
            <h2 class="text-xl md:text-2xl font-bold text-blue-600">Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome']); ?>!</h2>
        </div>

        <!-- Barra de Pesquisa -->
        <div class="flex justify-center mb-6">
            <form action="dashboard.php" method="GET" class="relative w-full max-w-md">
                <input type="text" name="search" value="<?= htmlspecialchars($search); ?>" placeholder="Pesquise por pet ou tutor..." 
                       class="w-full p-3 pl-10 border border-gray-200 rounded-lg shadow focus:outline-none focus:ring-2 focus:ring-blue-400 bg-white text-sm md:text-base">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </form>
        </div>

        <!-- Estatísticas -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 md:gap-6 mb-8">
            <div class="bg-gradient-to-tr from-blue-500 to-blue-400 text-white p-4 md:p-6 rounded-2xl shadow-lg flex flex-col items-center justify-center relative overflow-hidden">
                <div class="absolute right-4 top-4 opacity-20 text-5xl md:text-6xl pointer-events-none">
                    <i class="fa-solid fa-paw"></i>
                </div>
                <h3 class="text-base md:text-lg font-semibold z-10">Total de Pets</h3>
                <p class="text-3xl md:text-4xl font-extrabold mt-2 z-10"><?= $totalPets; ?></p>
            </div>
            <a href="tutores/listar_tutores.php" class="bg-gradient-to-tr from-blue-700 to-blue-500 text-white p-4 md:p-6 rounded-2xl shadow-lg flex flex-col items-center justify-center relative overflow-hidden hover:scale-105 transition-transform">
                <div class="absolute right-4 top-4 opacity-20 text-5xl md:text-6xl pointer-events-none">
                    <i class="fa-solid fa-users"></i>
                </div>
                <h3 class="text-base md:text-lg font-semibold z-10">Total de Tutores</h3>
                <p class="text-3xl md:text-4xl font-extrabold mt-2 z-10"><?= $totalTutores; ?></p>
            </a>
        </div>

        <!-- Listagem de Pets -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6">
            <?php if (empty($pets)) : ?>
                <div class="col-span-3 text-gray-600 text-center bg-white/80 rounded-lg p-6 shadow">
                    Nenhum pet encontrado.
                </div>
            <?php else : ?>
                <?php foreach ($pets as $pet): ?>
                    <div class="bg-white rounded-2xl shadow-lg p-4 md:p-6 flex flex-col gap-3 hover:shadow-2xl transition animate-fade-in">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="bg-blue-100 text-blue-500 rounded-full p-3">
                                <i class="fa-solid fa-dog text-xl md:text-2xl"></i>
                            </div>
                            <h3 class="text-lg md:text-xl font-bold text-blue-700"><?= htmlspecialchars($pet['nome']); ?></h3>
                        </div>
                        <p class="text-gray-600 text-sm md:text-base"><i class="fa fa-user mr-1 text-blue-400"></i>Tutor: <span class="font-medium"><?= htmlspecialchars($pet['tutor']); ?></span></p>
                        <div class="mt-2 flex justify-between items-center gap-2">
                            <a href="pets/visualizar_pet.php?id=<?= $pet['id']; ?>" class="text-blue-500 hover:text-blue-700 flex items-center gap-1 font-semibold text-sm md:text-base">
                                <i class="fas fa-eye"></i> <span class="hidden sm:inline">Visualizar</span>
                            </a>
                            <a href="pets/atualizar_pet.php?id=<?= $pet['id']; ?>" class="text-yellow-500 hover:text-yellow-700 flex items-center gap-1 font-semibold text-sm md:text-base">
                                <i class="fas fa-edit"></i> <span class="hidden sm:inline">Editar</span>
                            </a>
                            <a href="pets/scripts/deletar_pet.php?id=<?= $pet['id']; ?>" class="text-red-500 hover:text-red-700 flex items-center gap-1 font-semibold text-sm md:text-base" onclick="return confirm('Tem certeza que deseja excluir este pet?');">
                                <i class="fas fa-trash"></i> <span class="hidden sm:inline">Excluir</span>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Agendamentos do dia (apenas não finalizados e um horário por pet) -->
        <div class="bg-white/90 p-4 md:p-6 rounded-2xl shadow mb-8 mt-8 animate-fade-in">
            <h2 class="text-lg md:text-xl font-bold text-blue-700 mb-4 flex items-center gap-2">
                <i class="fa fa-calendar-day"></i> Agendamentos pendentes de hoje (<?= date('d/m/Y') ?>)
            </h2>
            <?php if (empty($agendamentosHoje)): ?>
                <div class="text-gray-500 text-center">Nenhum agendamento pendente para hoje.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-blue-100 bg-white rounded-xl shadow text-xs md:text-sm">
                        <thead class="bg-blue-50">
                            <tr>
                                <th class="px-2 md:px-4 py-2 text-left font-bold text-blue-700 uppercase">Horário</th>
                                <th class="px-2 md:px-4 py-2 text-left font-bold text-blue-700 uppercase">Pet</th>
                                <th class="px-2 md:px-4 py-2 text-left font-bold text-blue-700 uppercase">Tutor</th>
                                <th class="px-2 md:px-4 py-2 text-left font-bold text-blue-700 uppercase">Status</th>
                                <th class="px-2 md:px-4 py-2 text-left font-bold text-blue-700 uppercase">Serviços</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-50">
                            <?php foreach ($agendamentosHoje as $ag): ?>
                                <tr>
                                    <td class="px-2 md:px-4 py-2"><?= htmlspecialchars($ag['horario']) ?></td>
                                    <td class="px-2 md:px-4 py-2">
                                        <a href="./pets/agendamentos/editar_agendamento.php?id=<?= $ag['agendamento_id'] ?>" class="text-blue-700 font-bold hover:underline">
                                            <?= htmlspecialchars($ag['pet_nome']) ?>
                                        </a>
                                    </td>
                                    <td class="px-2 md:px-4 py-2"><?= htmlspecialchars($ag['tutor_nome']) ?></td>
                                    <td class="px-2 md:px-4 py-2"><?= htmlspecialchars($ag['status']) ?></td>
                                    <td class="px-2 md:px-4 py-2"><?= htmlspecialchars($ag['servicos']) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <footer class="w-full py-3 bg-white/80 text-center text-gray-400 text-xs mt-8">
        &copy; 2025 HVTPETSHOP. Todos os direitos reservados.<br>
        <span class="text-[11px] text-gray-400">Versão do sistema: <strong>AMPN 1.0.5</strong></span>
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
</body>
</html>