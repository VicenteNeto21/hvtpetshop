<?php
include "../config/config.php";
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado.']);
    exit();
}

header('Content-Type: application/json');

// Parâmetros
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$tutoresPorPagina = 5;
$offset = ($pagina - 1) * $tutoresPorPagina;

$params = [];
$whereClause = '';
if ($busca !== '') {
    $whereClause = " WHERE t.nome LIKE :busca OR t.email LIKE :busca OR t.telefone LIKE :busca ";
    $params[':busca'] = "%$busca%";
}

// --- Contagem Total para Paginação ---
$sqlCount = "SELECT COUNT(*) FROM tutores t" . $whereClause;
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalTutores = $stmtCount->fetchColumn();
$totalPaginas = ceil($totalTutores / $tutoresPorPagina);

// --- Busca dos Dados Paginados ---
$sql = "
    SELECT t.id, t.nome, t.email, t.telefone, COUNT(p.id) as total_pets
    FROM tutores t
    LEFT JOIN pets p ON p.tutor_id = t.id
    $whereClause
    GROUP BY t.id, t.nome, t.email, t.telefone 
    ORDER BY t.nome
    LIMIT :limit OFFSET :offset
";

$stmt = $pdo->prepare($sql);
if ($busca !== '') {
    $stmt->bindValue(':busca', "%$busca%");
}
$stmt->bindValue(':limit', $tutoresPorPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$tutores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Função para formatar telefone
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    if (strlen($telefone) === 11) return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    if (strlen($telefone) === 10) return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    return $telefone;
}

// --- Geração do HTML ---
ob_start();

// Conteúdo da Tabela
if (empty($tutores)) {
    echo '<tr><td colspan="4" class="p-4 text-center text-slate-500">Nenhum tutor encontrado.</td></tr>';
} else {
    foreach ($tutores as $tutor) {
        echo "<tr class='hover:bg-slate-50 group cursor-pointer' onclick=\"window.location='visualizar_tutor.php?id={$tutor['id']}'\">";
        echo "<td class='px-4 py-4 font-semibold text-slate-800 whitespace-nowrap'>" . htmlspecialchars($tutor['nome']) . "</td>";
        echo "<td class='px-4 py-4' onclick='event.stopPropagation();'>";
        echo "<div class='flex flex-col'>";
        echo "<span class='text-slate-600'>" . htmlspecialchars($tutor['email'] ?? 'N/A') . "</span>";
        echo "<span class='text-slate-500 text-xs'>" . htmlspecialchars(formatarTelefone($tutor['telefone'])) . "</span>";
        echo "</div></td>";
        echo "<td class='px-4 py-4 text-center text-slate-500'>{$tutor['total_pets']}</td>";
        echo "<td class='px-4 py-4' onclick='event.stopPropagation();'>";
        echo "<div class='flex items-center justify-center gap-4'>";
        echo "<a href='editar_tutor.php?id={$tutor['id']}' class='w-8 h-8 flex items-center justify-center rounded-full text-amber-600 hover:bg-amber-100 hover:text-amber-800 transition' title='Editar Tutor'><i class='fas fa-edit'></i></a>";
        echo "<a href='javascript:void(0);' onclick=\"openConfirmationModal('Excluir Tutor', 'Tem certeza que deseja excluir o tutor \\'" . htmlspecialchars($tutor['nome'], ENT_QUOTES) . "\\'? Todos os pets e agendamentos associados também serão removidos.', 'excluir_tutor.php?id={$tutor['id']}')\" class='w-8 h-8 flex items-center justify-center rounded-full text-red-600 hover:bg-red-100 hover:text-red-800 transition' title='Excluir Tutor'><i class='fas fa-trash'></i></a>";
        echo "</div></td>";
        echo "</tr>";
    }
}
$tableContent = ob_get_clean();

ob_start();
// Conteúdo da Paginação
if ($totalPaginas > 1) {
    echo '<div class="flex items-center gap-1">';
    // Seta para voltar
    $prevPage = $pagina > 1 ? $pagina - 1 : 1;
    $disabledPrev = $pagina == 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-100';
    echo "<button onclick='buscarTutores(\"\", {$prevPage})' class='px-3 py-1 text-sm rounded-md bg-white text-slate-700 border border-slate-300 {$disabledPrev}'>&lt;</button>";

    // Links das páginas
    for ($i = 1; $i <= $totalPaginas; $i++) {
        $activeClass = $i == $pagina ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-100';
        echo "<button onclick='buscarTutores(\"\", {$i})' class='px-3 py-1 text-sm rounded-md {$activeClass}'>{$i}</button>";
    }

    // Seta para avançar
    $nextPage = $pagina < $totalPaginas ? $pagina + 1 : $totalPaginas;
    $disabledNext = $pagina == $totalPaginas ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-100';
    echo "<button onclick='buscarTutores(\"\", {$nextPage})' class='px-3 py-1 text-sm rounded-md bg-white text-slate-700 border border-slate-300 {$disabledNext}'>&gt;</button>";
    echo '</div>';
}
$paginationContent = ob_get_clean();

echo json_encode([
    'tableContent' => $tableContent,
    'paginationContent' => $paginationContent
]);
?>