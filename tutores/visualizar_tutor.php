<?php
// filepath: c:\xampp\htdocs\dashboard\hvt_petshop\tutores\visualizar_tutor.php
include "../config/config.php";
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

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

// Busca os pets do tutor
$stmt = $pdo->prepare("SELECT * FROM pets WHERE tutor_id = ?");
$stmt->execute([$tutorId]);
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Busca os últimos agendamentos dos pets do tutor (últimos 10)
$stmt = $pdo->prepare("
    SELECT a.*, p.nome AS nome_pet 
    FROM agendamentos a
    INNER JOIN pets p ON a.pet_id = p.id
    WHERE p.tutor_id = ?
    ORDER BY a.data_hora DESC
    LIMIT 10
");
$stmt->execute([$tutorId]);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Perfil do Tutor - HVTPETSHOP</title>
    <link rel="icon" type="image/x-icon" href="../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-100 via-blue-50 to-blue-200 flex flex-col">

    <!-- Navbar -->
    <nav class="w-full bg-white/90 shadow flex items-center justify-between px-6 py-3">
        <div class="flex items-center gap-3">
            <img src="../icons/pet.jpg" alt="Logo Petshop" class="w-10 h-10 rounded-full shadow">
            <span class="text-2xl font-bold text-blue-700 tracking-tight">HVTPETSHOP</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="../dashboard.php" class="text-blue-600 hover:text-blue-800 font-semibold transition"><i class="fa fa-home mr-1"></i>Dashboard</a>
            <a href="listar_tutores.php" class="text-blue-500 hover:text-blue-700 font-semibold transition"><i class="fa fa-users mr-1"></i>Tutores</a>
            <a href="../auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition"><i class="fa fa-sign-out-alt mr-1"></i>Sair</a>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-5xl mx-auto mt-10 p-4">
        <div class="bg-white/95 p-0 md:p-8 rounded-3xl shadow-2xl animate-fade-in flex flex-col md:flex-row gap-8">
            <!-- Perfil do Tutor -->
            <aside class="md:w-1/3 w-full bg-gradient-to-br from-blue-200 to-blue-50 rounded-t-3xl md:rounded-l-3xl md:rounded-tr-none p-8 flex flex-col items-center justify-center shadow-inner">
                <div class="bg-blue-100 border-4 border-white rounded-full w-32 h-32 flex items-center justify-center shadow-lg mb-4">
                    <i class="fa fa-user text-blue-400 text-6xl"></i>
                </div>
                <div class="text-center">
                    <h1 class="text-2xl font-bold text-blue-700 mb-1"><?= htmlspecialchars($tutor['nome']) ?></h1>
                    <div class="text-gray-600 text-sm flex items-center gap-2 justify-center mb-1">
                        <i class="fa fa-envelope text-blue-300"></i> <?= htmlspecialchars($tutor['email']) ?>
                    </div>
                    <div class="text-gray-600 text-sm flex items-center gap-2 justify-center mb-1">
                        <i class="fa fa-phone text-blue-300"></i> <?= htmlspecialchars($tutor['telefone']) ?>
                    </div>
                    <div class="flex flex-col items-center gap-1 mb-2">
                        <div class="text-gray-600 text-xs flex items-center gap-2 justify-center">
                            <i class="fa fa-map-marker-alt text-blue-300"></i>
                            <span>
                                <?= htmlspecialchars($tutor['rua'] ?? '') ?>
                                <?= $tutor['numero'] ? ', ' . htmlspecialchars($tutor['numero']) : '' ?>
                                <?= $tutor['bairro'] ? ' - ' . htmlspecialchars($tutor['bairro']) : '' ?>
                            </span>
                        </div>
                        <div class="text-gray-600 text-xs flex items-center gap-2 justify-center">
                            <?= htmlspecialchars($tutor['cidade'] ?? '') ?>
                            <?= $tutor['uf'] ? ' - ' . htmlspecialchars($tutor['uf']) : '' ?>
                            <?= !empty($tutor['cep']) ? '<span class="ml-2 bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full">CEP: ' . htmlspecialchars($tutor['cep']) . '</span>' : '' ?>
                        </div>
                    </div>
                    <a href="editar_tutor.php?id=<?= $tutor['id'] ?>" class="inline-flex items-center gap-2 bg-yellow-500 hover:bg-yellow-600 text-white px-6 py-2 rounded-lg font-semibold shadow transition mt-4">
                        <i class="fa fa-edit"></i> Editar Dados
                    </a>
                </div>
            </aside>

            <!-- Conteúdo principal -->
            <section class="md:w-2/3 w-full flex flex-col gap-10 p-6">
                <!-- Pets do Tutor -->
                <div>
                    <div class="font-bold text-blue-700 text-lg mb-3 flex items-center gap-2">
                        <i class="fa fa-paw"></i> Pets cadastrados
                        <a href="../pets/adicionar_pet.php?tutor_id=<?= $tutor['id'] ?>"
                           class="ml-auto bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg text-xs font-semibold shadow flex items-center gap-2 transition"
                           title="Adicionar novo pet">
                            <i class="fa fa-plus"></i> Novo Pet
                        </a>
                    </div>
                    <?php if (empty($pets)): ?>
                        <div class="text-gray-400 text-sm">Nenhum pet cadastrado para este tutor.</div>
                    <?php else: ?>
                        <div class="flex flex-wrap gap-2">
                            <?php foreach ($pets as $pet): ?>
                                <a href="../pets/visualizar_pet.php?id=<?= $pet['id'] ?>"
                                   class="bg-blue-100 hover:bg-blue-200 text-blue-800 font-semibold px-4 py-2 rounded-full shadow transition text-sm flex items-center gap-2">
                                    <i class="fa fa-paw"></i> <?= htmlspecialchars($pet['nome']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Últimos Agendamentos -->
                <div>
                    <div class="font-bold text-blue-700 text-lg mb-3 flex items-center gap-2">
                        <i class="fa fa-calendar-alt"></i> Últimos Agendamentos
                    </div>
                    <?php if (empty($agendamentos)): ?>
                        <div class="text-gray-400 text-sm">Nenhum agendamento encontrado para os pets deste tutor.</div>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-blue-100 bg-white rounded-xl shadow">
                                <thead class="bg-blue-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-blue-700 uppercase">Pet</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-blue-700 uppercase">Data/Hora</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-blue-700 uppercase">Status</th>
                                        <th class="px-4 py-2 text-left text-xs font-bold text-blue-700 uppercase">Serviços</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-blue-50">
                                    <?php foreach ($agendamentos as $ag): ?>
                                        <tr>
                                            <td class="px-4 py-2 text-blue-800 font-semibold"><?= htmlspecialchars($ag['nome_pet']) ?></td>
                                            <td class="px-4 py-2"><?= date('d/m/Y H:i', strtotime($ag['data_hora'])) ?></td>
                                            <td class="px-4 py-2">
                                                <span class="px-2 py-1 rounded-full text-xs font-semibold
                                                    <?= $ag['status'] === 'Cancelado' ? 'bg-red-100 text-red-700' : ($ag['status'] === 'Concluído' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700') ?>">
                                                    <?= htmlspecialchars($ag['status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-2"><?= htmlspecialchars($ag['servicos'] ?? '-') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>

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