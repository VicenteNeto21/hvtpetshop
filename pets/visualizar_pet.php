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
$query = "SELECT p.*, t.nome as tutor_nome, t.email, t.telefone 
          FROM pets p 
          JOIN tutores t ON p.tutor_id = t.id 
          WHERE p.id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':id', $petId);
$stmt->execute();
$pet = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se o pet existe
if (!$pet) {
    echo "Pet não encontrado.";
    exit();
}

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

// Filtros de status e data
$statusFilter = isset($_GET['status']) ? $_GET['status'] : '';
$dataFilter = isset($_GET['data']) ? $_GET['data'] : '';

// Paginação
$agendamentosPorPagina = 5;
$paginaAtual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($paginaAtual - 1) * $agendamentosPorPagina;

// Consulta agendamentos agrupando por data_hora e usuario_id
$queryAgendamentos = "SELECT 
                        MIN(a.id) as id, 
                        a.data_hora, 
                        a.status,
                        GROUP_CONCAT(s.nome ORDER BY s.nome SEPARATOR ', ') AS servicos
                    FROM agendamentos a
                    JOIN servicos s ON a.servico_id = s.id
                    WHERE a.pet_id = :pet_id";
if ($statusFilter) {
    $queryAgendamentos .= " AND a.status = :status";
}
if ($dataFilter) {
    $queryAgendamentos .= " AND DATE(a.data_hora) = :data";
}
$queryAgendamentos .= " GROUP BY a.data_hora, a.status
                        ORDER BY a.data_hora DESC LIMIT :limit OFFSET :offset";

$stmtAgendamentos = $pdo->prepare($queryAgendamentos);
$stmtAgendamentos->bindValue(':pet_id', $petId);
$stmtAgendamentos->bindValue(':limit', $agendamentosPorPagina, PDO::PARAM_INT);
$stmtAgendamentos->bindValue(':offset', $offset, PDO::PARAM_INT);
if ($statusFilter) {
    $stmtAgendamentos->bindValue(':status', $statusFilter);
}
if ($dataFilter) {
    $stmtAgendamentos->bindValue(':data', $dataFilter);
}
$stmtAgendamentos->execute();
$agendamentos = $stmtAgendamentos->fetchAll(PDO::FETCH_ASSOC);

// Consulta o total de agendamentos agrupados
$queryTotalAgendamentos = "SELECT COUNT(DISTINCT CONCAT(data_hora, '-', status)) AS total FROM agendamentos WHERE pet_id = :pet_id";
if ($statusFilter) {
    $queryTotalAgendamentos .= " AND status = :status";
}
if ($dataFilter) {
    $queryTotalAgendamentos .= " AND DATE(data_hora) = :data";
}
$stmtTotalAgendamentos = $pdo->prepare($queryTotalAgendamentos);
$stmtTotalAgendamentos->bindValue(':pet_id', $petId);
if ($statusFilter) {
    $stmtTotalAgendamentos->bindValue(':status', $statusFilter);
}
if ($dataFilter) {
    $stmtTotalAgendamentos->bindValue(':data', $dataFilter);
}
$stmtTotalAgendamentos->execute();
$totalAgendamentos = $stmtTotalAgendamentos->fetch(PDO::FETCH_ASSOC)['total'];
$totalPaginas = ceil($totalAgendamentos / $agendamentosPorPagina);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Visualizar Pet - HVTPETSHOP</title>
    <link rel="icon" type="image/x-icon" href="../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <!-- Navbar padrão -->
    <nav class="w-full bg-white/90 shadow flex items-center justify-between px-6 py-3">
        <div class="flex items-center gap-3">
            <img src="../icons/pet.jpg" alt="Logo Petshop" class="w-10 h-10 rounded-full shadow">
            <span class="text-2xl font-bold text-blue-700 tracking-tight">HVTPETSHOP</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="../dashboard.php" class="text-blue-600 hover:text-blue-800 font-semibold transition"><i class="fa fa-home mr-1"></i>Dashboard</a>
            <a href="cadastrar_pet.php" class="text-green-600 hover:text-green-800 font-semibold transition"><i class="fa fa-plus mr-1"></i>Novo Pet</a>
            <a href="../tutores/listar_tutores.php" class="text-blue-500 hover:text-blue-700 font-semibold transition"><i class="fa fa-users mr-1"></i>Tutores</a>
            <a href="../auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition"><i class="fa fa-sign-out-alt mr-1"></i>Sair</a>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-4xl mx-auto mt-10 p-4">
        <!-- Card de informações do pet e tutor -->
        <div class="bg-white/90 rounded-2xl shadow-xl p-8 mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-6">
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-blue-700 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-dog text-blue-400"></i>
                        <?= htmlspecialchars($pet['nome']); ?>
                    </h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <div class="text-gray-500 text-xs">Tutor</div>
                            <div class="font-semibold text-gray-700"><?= htmlspecialchars($pet['tutor_nome']); ?></div>
                        </div>
                        <div>
                            <div class="text-gray-500 text-xs">Telefone</div>
                            <div class="font-semibold text-gray-700"><?= htmlspecialchars(formatarTelefone($pet['telefone'])); ?></div>
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-gray-500 text-xs">E-mail</div>
                            <div class="font-semibold text-gray-700"><?= htmlspecialchars($pet['email']); ?></div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6">
                        <div>
                            <div class="text-gray-500 text-xs">Espécie</div>
                            <div class="font-semibold text-gray-700"><?= htmlspecialchars($pet['especie']); ?></div>
                        </div>
                        <div>
                            <div class="text-gray-500 text-xs">Raça</div>
                            <div class="font-semibold text-gray-700"><?= htmlspecialchars($pet['raca']); ?></div>
                        </div>
                        <div>
                            <div class="text-gray-500 text-xs">Sexo</div>
                            <div class="font-semibold text-gray-700"><?= htmlspecialchars($pet['sexo']); ?></div>
                        </div>
                        <div>
                            <div class="text-gray-500 text-xs">Idade</div>
                            <div class="font-semibold text-gray-700"><?= htmlspecialchars($pet['idade']); ?> ano(s)</div>
                        </div>
                        <div>
                            <div class="text-gray-500 text-xs">Peso</div>
                            <div class="font-semibold text-gray-700"><?= htmlspecialchars($pet['peso']); ?> kg</div>
                        </div>
                        <div>
                            <div class="text-gray-500 text-xs">Pelagem</div>
                            <div class="font-semibold text-gray-700"><?= htmlspecialchars($pet['pelagem']); ?></div>
                        </div>
                        <div class="md:col-span-2">
                            <div class="text-gray-500 text-xs">Observações</div>
                            <div class="font-semibold text-gray-700"><?= htmlspecialchars($pet['observacoes'] ?: 'Nenhuma observação'); ?></div>
                        </div>
                    </div>
                </div>
                <div class="flex flex-col gap-2 min-w-[180px]">
                    <a href="editar_pet.php?id=<?= $pet['id'] ?>" 
                       class="bg-yellow-500 hover:bg-yellow-600 text-white py-2 px-4 rounded-lg shadow flex items-center justify-center gap-2 transition">
                        <i class="fas fa-edit"></i> Editar Pet
                    </a>
                    <a href="agendamentos/agendar_banho_tosa.php?pet_id=<?= $pet['id'] ?>" 
                       class="bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg shadow flex items-center justify-center gap-2 transition">
                        <i class="fas fa-calendar-plus"></i> Agendar Serviço
                    </a>
                </div>
            </div>
        </div>

        <!-- Card de histórico de agendamentos -->
        <div class="bg-white/90 rounded-2xl shadow-xl p-8 animate-fade-in">
            <div class="flex flex-col md:flex-row md:justify-between md:items-center mb-6 gap-4">
                <h2 class="text-xl font-bold text-blue-700">Histórico de Agendamentos</h2>
                <!-- Filtros -->
                <form method="GET" class="flex flex-col sm:flex-row gap-2">
                    <input type="hidden" name="id" value="<?= $petId ?>">
                    <select name="status" class="p-2 border border-gray-200 rounded-lg text-sm">
                        <option value="">Todos status</option>
                        <option value="Pendente" <?= $statusFilter === 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="Em Atendimento" <?= $statusFilter === 'Em Atendimento' ? 'selected' : '' ?>>Em Atendimento</option>
                        <option value="Finalizado" <?= $statusFilter === 'Finalizado' ? 'selected' : '' ?>>Finalizado</option>
                        <option value="Cancelado" <?= $statusFilter === 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                    <input type="date" name="data" value="<?= $dataFilter ?>" class="p-2 border border-gray-200 rounded-lg text-sm">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center gap-1 transition">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="visualizar_pet.php?id=<?= $petId ?>" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg flex items-center gap-1 transition">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </form>
            </div>

            <!-- Tabela de agendamentos agrupados -->
            <div class="overflow-x-auto">
                <table class="w-full border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="p-3 text-sm font-semibold text-gray-700">Data e Hora</th>
                            <th class="p-3 text-sm font-semibold text-gray-700">Serviços</th>
                            <th class="p-3 text-sm font-semibold text-gray-700">Status</th>
                            <th class="p-3 text-sm font-semibold text-gray-700">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($agendamentos)) : ?>
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-500">Não há agendamentos registrados.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($agendamentos as $agendamento) : ?>
                                <tr class="border-t border-gray-200 hover:bg-gray-50">
                                    <td class="p-3 text-sm text-gray-700"><?= date("d/m/Y H:i", strtotime($agendamento['data_hora'])); ?></td>
                                    <td class="p-3 text-sm text-gray-700"><?= htmlspecialchars($agendamento['servicos']); ?></td>
                                    <td class="p-3">
                                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold text-white
                                            <?= $agendamento['status'] == 'Pendente' ? 'bg-yellow-500' : 
                                               ($agendamento['status'] == 'Em Atendimento' ? 'bg-blue-500' : 
                                               ($agendamento['status'] == 'Finalizado' ? 'bg-green-500' : 
                                               ($agendamento['status'] == 'Cancelado' ? 'bg-red-500' : 'bg-gray-500'))); ?>">
                                            <?= htmlspecialchars($agendamento['status']); ?>
                                        </span>
                                    </td>
                                    <td class="p-3">
                                        <div class="flex gap-2">
                                            <a href="agendamentos/visualizar_ficha.php?id=<?= $agendamento['id'] ?>" 
                                               class="bg-blue-500 hover:bg-blue-600 text-white p-2 rounded-lg flex items-center justify-center transition" title="Visualizar">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="agendamentos/editar_agendamento.php?id=<?= $agendamento['id'] ?>" 
                                               class="bg-yellow-500 hover:bg-yellow-600 text-white p-2 rounded-lg flex items-center justify-center transition" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($agendamento['status'] == 'Pendente') : ?>
                                                <a href="../pets/agendamentos/cancelar_agendamento.php?id=<?= $agendamento['id'] ?>&pet_id=<?= $petId ?>" 
                                                   class="bg-red-500 hover:bg-red-600 text-white p-2 rounded-lg flex items-center justify-center transition"
                                                   onclick="return confirm('Tem certeza que deseja cancelar este agendamento?')" title="Cancelar">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="../pets/agendamentos/excluir_agendamento.php?id=<?= $agendamento['id'] ?>&pet_id=<?= $petId ?>"
                                               class="bg-gray-700 hover:bg-black text-white p-2 rounded-lg flex items-center justify-center transition"
                                               onclick="return confirm('Tem certeza que deseja excluir este agendamento? Esta ação não pode ser desfeita.')" title="Excluir">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <?php if ($totalPaginas > 1) : ?>
                <div class="flex justify-center mt-6">
                    <div class="flex items-center gap-1">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                            <a href="?id=<?= $petId ?>&pagina=<?= $i ?><?= $statusFilter ? '&status='.$statusFilter : '' ?><?= $dataFilter ? '&data='.$dataFilter : '' ?>" 
                               class="px-3 py-1 text-sm rounded-md <?= $i == $paginaAtual ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-300 hover:bg-gray-100' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
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
</body>
</html>