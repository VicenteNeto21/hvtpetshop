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
// Agrupando por data_hora para consolidar serviços
$stmt = $pdo->prepare("
    SELECT 
        MIN(a.id) as id,
        a.data_hora, 
        a.status,
        p.nome AS nome_pet,
        GROUP_CONCAT(s.nome ORDER BY s.nome SEPARATOR ', ') AS servicos
    FROM agendamentos a
    INNER JOIN pets p ON a.pet_id = p.id
    INNER JOIN servicos s ON a.servico_id = s.id
    WHERE p.tutor_id = ?
    GROUP BY a.data_hora, p.nome, a.status
    ORDER BY a.data_hora DESC, p.nome ASC
    LIMIT 15
");
$stmt->execute([$tutorId]);
$agendamentos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Função para formatar número de telefone
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    if (strlen($telefone) === 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } elseif (strlen($telefone) === 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }
    return $telefone;
}

// Função para montar endereço completo do tutor
function montarEndereco($tutor) {
    $partes = [];
    if (!empty($tutor['rua']))      $partes[] = $tutor['rua'];
    if (!empty($tutor['numero']))   $partes[] = 'Nº ' . $tutor['numero'];
    if (!empty($tutor['bairro']))   $partes[] = $tutor['bairro'];
    if (!empty($tutor['cidade']) && !empty($tutor['uf'])) $partes[] = $tutor['cidade'] . '/' . $tutor['uf'];
    return implode(', ', $partes) ?: 'Endereço não informado';
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil do Tutor - CereniaPet</title>
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
        <!-- Cabeçalho da Página -->
        <div class="mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
                        <i class="fa-solid fa-user text-amber-500"></i>
                        <?= htmlspecialchars($tutor['nome']); ?>
                    </h1>
                    <p class="text-slate-500 mt-1">Perfil detalhado do tutor e seus pets.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="editar_tutor.php?id=<?= $tutor['id'] ?>" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition flex items-center gap-2">
                        <i class="fas fa-edit"></i> Editar Tutor
                    </a>
                    <a href="../pets/adicionar_pet.php?tutor_id=<?= $tutor['id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition flex items-center gap-2">
                        <i class="fas fa-plus"></i> Adicionar Pet
                    </a>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="space-y-10">
            <!-- Informações do Tutor -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 border-b border-slate-200 pb-2">Dados do Tutor</h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="font-medium text-slate-500">Telefone</dt>
                        <dd class="text-slate-800 font-semibold flex items-center gap-3">
                            <span><?= htmlspecialchars(formatarTelefone($tutor['telefone'])); ?></span>
                            <?php if (($tutor['telefone_is_whatsapp'] ?? 'Não') === 'Sim'): ?>
                                <?php
                                    // Remove caracteres não numéricos para criar o link
                                    $whatsappNumber = preg_replace('/[^0-9]/', '', $tutor['telefone']);
                                ?>
                                <a href="https://wa.me/55<?= $whatsappNumber ?>" target="_blank" class="text-green-500 hover:text-green-600" title="Enviar mensagem no WhatsApp">
                                    <i class="fab fa-whatsapp fa-lg"></i>
                                </a>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">E-mail</dt>
                        <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($tutor['email']); ?></dd>
                    </div>
                    <div class="lg:col-span-1">
                        <dt class="font-medium text-slate-500">Endereço</dt>
                        <dd class="text-slate-800 font-semibold">
                            <?= htmlspecialchars(montarEndereco($tutor)) ?>
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Pets do Tutor -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-paw text-violet-500"></i>
                    Pets Cadastrados
                </h2>
                <?php if (empty($pets)): ?>
                    <div class="text-slate-500 text-center py-8 border-2 border-dashed rounded-lg">Nenhum pet cadastrado para este tutor.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="border-b-2 border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Nome</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Espécie</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Raça</th>
                                    <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase tracking-wider">Ações</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($pets as $pet): ?>
                                    <tr class="hover:bg-slate-50 group cursor-pointer" onclick="window.location='../pets/visualizar_pet.php?id=<?= $pet['id'] ?>'">
                                        <td class="px-4 py-4 font-semibold text-slate-800 whitespace-nowrap"><?= htmlspecialchars($pet['nome']) ?></td>
                                        <td class="px-4 py-4 text-slate-600 whitespace-nowrap"><?= htmlspecialchars($pet['especie']) ?></td>
                                        <td class="px-4 py-4 text-slate-600 whitespace-nowrap"><?= htmlspecialchars($pet['raca']) ?></td>
                                        <td class="px-4 py-4 text-center" onclick="event.stopPropagation();">
                                            <div class="flex items-center justify-center gap-4">
                                                <a href="../pets/editar_pet.php?id=<?= $pet['id'] ?>" class="text-amber-600 hover:text-amber-800" title="Editar Pet"><i class="fas fa-edit"></i></a>
                                                <a href="../pets/agendamentos/agendar_servico.php?pet_id=<?= $pet['id'] ?>" class="text-blue-600 hover:text-blue-800" title="Agendar Serviço"><i class="fas fa-calendar-plus"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Histórico de Agendamentos -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa fa-history text-sky-500"></i>
                    Histórico de Atendimentos
                </h2>
                <?php if (empty($agendamentos)): ?>
                    <div class="text-slate-500 text-center py-8 border-2 border-dashed rounded-lg">Nenhum agendamento encontrado para os pets deste tutor.</div>
                <?php else: ?>
                    <div class="overflow-x-auto">
                        <table class="min-w-full text-sm">
                            <thead class="border-b-2 border-slate-200">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Data e Hora</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Pet</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Serviços</th>
                                    <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach ($agendamentos as $ag): ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="px-4 py-4 text-slate-700 whitespace-nowrap"><?= date('d/m/Y H:i', strtotime($ag['data_hora'])) ?></td>
                                        <td class="px-4 py-4 font-semibold text-slate-800"><?= htmlspecialchars($ag['nome_pet']) ?></td>
                                        <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars($ag['servicos'] ?? '-') ?></td>
                                        <td class="px-4 py-4">
                                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold
                                                <?= $ag['status'] == 'Pendente' ? 'bg-yellow-100 text-yellow-800' : 
                                                   ($ag['status'] == 'Em Atendimento' ? 'bg-blue-100 text-blue-800' : 
                                                   ($ag['status'] == 'Finalizado' ? 'bg-green-100 text-green-800' : 
                                                   ($ag['status'] == 'Cancelado' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-800'))); ?>">
                                                <?= htmlspecialchars($ag['status']) ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
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
    </style>
</body>
</html>