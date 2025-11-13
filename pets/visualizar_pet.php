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
$query = "SELECT p.*, t.nome as tutor_nome, t.email, t.telefone, t.telefone_is_whatsapp
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

// Função para calcular e formatar a idade
function calcularIdadeFormatada($nascimento, $idadeSalva) {
    if (!empty($nascimento) && $nascimento !== '0000-00-00') {
        $dataNascimento = new DateTime($nascimento);
        $hoje = new DateTime();
        $diferenca = $hoje->diff($dataNascimento);
        
        $anos = $diferenca->y;
        $meses = $diferenca->m;

        if ($anos > 0) return "{$anos} ano(s)" . ($meses > 0 ? " e {$meses} mes(es)" : "");
        return "{$meses} mes(es)";
    }
    return "{$idadeSalva} ano(s)"; // Fallback para a idade salva
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
    <title>Visualizar Pet - CereniaPet</title>
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
                        <i class="fa-solid fa-dog text-violet-500"></i>
                        <?= htmlspecialchars($pet['nome']); ?>
                    </h1>
                    <p class="text-slate-500 mt-1">Perfil detalhado e histórico de atendimentos.</p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="editar_pet.php?id=<?= $pet['id'] ?>" class="bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition flex items-center gap-2">
                        <i class="fas fa-edit"></i> Editar Pet
                    </a>
                    <a href="agendamentos/agendar_servico.php?pet_id=<?= $pet['id'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition flex items-center gap-2">
                        <i class="fas fa-calendar-plus"></i> Agendar Serviço
                    </a>
                </div>
            </div>
        </div>

        <!-- Informações do Pet e Tutor -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-10">
            <!-- Coluna de Informações do Pet -->
            <div class="lg:col-span-2 bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 border-b border-slate-200 pb-2">Informações do Pet</h2>
                <dl class="grid grid-cols-2 sm:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="font-medium text-slate-500">Espécie</dt>
                        <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($pet['especie']); ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Raça</dt>
                        <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($pet['raca']); ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Sexo</dt>
                        <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($pet['sexo']); ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Idade / Nascimento</dt>
                        <dd class="text-slate-800 font-semibold"><?= calcularIdadeFormatada($pet['nascimento'], $pet['idade']); ?> (<?= !empty($pet['nascimento']) ? date('d/m/Y', strtotime($pet['nascimento'])) : 'N/A' ?>)</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Peso</dt>
                        <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($pet['peso']); ?> kg</dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Pelagem</dt>
                        <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($pet['pelagem'] ?: 'N/A'); ?></dd>
                    </div>
                    <div class="col-span-full">
                        <dt class="font-medium text-slate-500">Observações</dt>
                        <dd class="text-slate-800 font-semibold whitespace-pre-wrap"><?= htmlspecialchars($pet['observacoes'] ?: 'Nenhuma observação.'); ?></dd>
                    </div>
                </dl>
            </div>
            <!-- Coluna de Informações do Tutor -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
                <h2 class="text-xl font-bold text-slate-800 mb-4 border-b border-slate-200 pb-2">Informações do Tutor</h2>
                <dl class="space-y-4 text-sm">
                    <div>
                        <dt class="font-medium text-slate-500">Nome</dt>
                        <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($pet['tutor_nome']); ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Telefone</dt>
                        <dd class="text-slate-800 font-semibold flex items-center gap-3">
                            <span><?= htmlspecialchars(formatarTelefone($pet['telefone'])); ?></span>
                            <?php if (($pet['telefone_is_whatsapp'] ?? 'Não') === 'Sim'): ?>
                                <?php
                                    // Remove caracteres não numéricos para criar o link
                                    $whatsappNumber = preg_replace('/[^0-9]/', '', $pet['telefone']);
                                ?>
                                <a href="https://wa.me/55<?= $whatsappNumber ?>" target="_blank" class="text-green-500 hover:text-green-600" title="Enviar mensagem no WhatsApp">
                                    <i class="fab fa-whatsapp fa-lg"></i>
                                </a>
                            <?php endif; ?>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">E-mail</dt>
                        <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($pet['email']); ?></dd>
                    </div>
                </dl>
            </div>
        </div>

        <!-- Histórico de Agendamentos -->
        <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-4 gap-4">
                <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                    <i class="fa fa-history text-sky-500"></i>
                    Histórico de Atendimentos
                </h2>
                <form method="GET" class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
                    <input type="hidden" name="id" value="<?= $petId ?>">
                    <select name="status" class="p-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">Todos status</option>
                        <option value="Pendente" <?= $statusFilter === 'Pendente' ? 'selected' : '' ?>>Pendente</option>
                        <option value="Em Atendimento" <?= $statusFilter === 'Em Atendimento' ? 'selected' : '' ?>>Em Atendimento</option>
                        <option value="Finalizado" <?= $statusFilter === 'Finalizado' ? 'selected' : '' ?>>Finalizado</option>
                        <option value="Cancelado" <?= $statusFilter === 'Cancelado' ? 'selected' : '' ?>>Cancelado</option>
                    </select>
                    <input type="date" name="data" value="<?= $dataFilter ?>" class="p-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center justify-center gap-1 transition">
                        <i class="fas fa-filter"></i> Filtrar
                    </button>
                    <a href="visualizar_pet.php?id=<?= $petId ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 px-4 py-2 rounded-lg flex items-center justify-center gap-1 transition">
                        <i class="fas fa-times"></i> Limpar
                    </a>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead>
                        <tr class="border-b-2 border-slate-200">
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Data e Hora</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Serviços</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if (empty($agendamentos)) : ?>
                            <tr>
                                <td colspan="4" class="p-4 text-center text-slate-500">Não há agendamentos para os filtros selecionados.</td>
                            </tr>
                        <?php else : ?>
                            <?php foreach ($agendamentos as $agendamento) : ?>
                                <tr class="hover:bg-slate-50">
                                    <td class="px-4 py-4 text-slate-700 whitespace-nowrap"><?= date("d/m/Y H:i", strtotime($agendamento['data_hora'])); ?></td>
                                    <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars($agendamento['servicos']); ?></td>
                                    <td class="px-4 py-4">
                                        <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold
                                            <?= $agendamento['status'] == 'Pendente' ? 'bg-yellow-100 text-yellow-800' : 
                                               ($agendamento['status'] == 'Em Atendimento' ? 'bg-blue-100 text-blue-800' : 
                                               ($agendamento['status'] == 'Finalizado' ? 'bg-green-100 text-green-800' : 
                                               ($agendamento['status'] == 'Cancelado' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-800'))); ?>">
                                            <?= htmlspecialchars($agendamento['status']); ?>
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="agendamentos/visualizar_ficha.php?id=<?= $agendamento['id'] ?>"
                                               class="w-8 h-8 flex items-center justify-center rounded-full text-blue-600 hover:bg-blue-100 hover:text-blue-800 transition" title="Visualizar Ficha">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="agendamentos/editar_agendamento.php?id=<?= $agendamento['id'] ?>"
                                               class="w-8 h-8 flex items-center justify-center rounded-full text-amber-600 hover:bg-amber-100 hover:text-amber-800 transition" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($agendamento['status'] == 'Pendente') : ?>
                                                <a href="javascript:void(0);" onclick="openConfirmationModal('Cancelar Agendamento', 'Tem certeza que deseja cancelar este agendamento?', 'agendamentos/cancelar_agendamento_action.php?id=<?= $agendamento['id'] ?>&pet_id=<?= $petId ?>')" class="w-8 h-8 flex items-center justify-center rounded-full text-red-600 hover:bg-red-100 hover:text-red-800 transition" title="Cancelar">
                                                    <i class="fas fa-times"></i>
                                                </a>
                                            <?php endif; ?>
                                            <a href="javascript:void(0);" onclick="openConfirmationModal('Excluir Agendamento', 'Tem certeza que deseja excluir este agendamento? Esta ação não pode ser desfeita.', 'agendamentos/excluir_agendamento.php?id=<?= $agendamento['id'] ?>&pet_id=<?= $petId ?>')" class="w-8 h-8 flex items-center justify-center rounded-full text-slate-500 hover:bg-red-100 hover:text-red-600 transition" title="Excluir">
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
                <div class="flex justify-center mt-6 pt-4 border-t border-slate-200">
                    <div class="flex items-center gap-1">
                        <?php for ($i = 1; $i <= $totalPaginas; $i++) : ?>
                            <a href="?id=<?= $petId ?>&pagina=<?= $i ?><?= $statusFilter ? '&status='.$statusFilter : '' ?><?= $dataFilter ? '&data='.$dataFilter : '' ?>" 
                               class="px-3 py-1 text-sm rounded-md <?= $i == $paginaAtual ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-100' ?>">
                                <?= $i ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                </div>
            <?php endif; ?>
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