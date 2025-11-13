<?php
include "../config/config.php";
session_start();

// Proteção básica
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    exit('Acesso negado.');
}

header('Content-Type: application/json');

// Parâmetros
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$petsPorPagina = 10;
$offset = ($pagina - 1) * $petsPorPagina;

$params = [];
$whereClause = '';
if ($search) {
    $whereClause = " WHERE pets.id LIKE :search OR pets.nome LIKE :search OR tutores.nome LIKE :search";
    $params[':search'] = "%$search%";
}

// --- Contagem Total para Paginação ---
$sqlCount = "SELECT COUNT(pets.id) FROM pets INNER JOIN tutores ON pets.tutor_id = tutores.id" . $whereClause;
$stmtCount = $pdo->prepare($sqlCount);
$stmtCount->execute($params);
$totalPets = $stmtCount->fetchColumn();
$totalPaginas = ceil($totalPets / $petsPorPagina);

// --- Busca dos Dados Paginados ---
$sql = "SELECT pets.id, pets.nome, tutores.nome AS tutor 
        FROM pets 
        INNER JOIN tutores ON pets.tutor_id = tutores.id
        $whereClause
        ORDER BY pets.id DESC
        LIMIT :limit OFFSET :offset";

$stmt = $pdo->prepare($sql);
$stmt->bindValue(':limit', $petsPorPagina, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
if ($search) $stmt->bindValue(':search', "%$search%");
$stmt->execute();
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

// --- Geração do HTML ---
ob_start();
if (empty($pets)) {
    echo '<tr><td colspan="4" class="p-4 text-center text-slate-500">Nenhum pet encontrado.</td></tr>';
} else {
    foreach ($pets as $pet) {
        echo '<tr class="hover:bg-slate-50 group">';
        echo '    <td class="px-4 py-4 text-slate-500 font-medium cursor-pointer" onclick="window.location.href=\'pets/visualizar_pet.php?id=' . $pet['id'] . '\'">' . htmlspecialchars($pet['id']) . '</td>';
        echo '    <td class="px-4 py-4 font-semibold text-slate-800 whitespace-nowrap cursor-pointer" onclick="window.location.href=\'pets/visualizar_pet.php?id=' . $pet['id'] . '\'">' . htmlspecialchars($pet['nome']) . '</td>';
        echo '    <td class="px-4 py-4 text-slate-500 whitespace-nowrap cursor-pointer" onclick="window.location.href=\'pets/visualizar_pet.php?id=' . $pet['id'] . '\'">' . htmlspecialchars($pet['tutor']) . '</td>';
        echo '    <td class="px-4 py-4" onclick="event.stopPropagation();">';
        echo '        <div class="flex items-center justify-center gap-3">';
        echo '            <a href="pets/editar_pet.php?id=' . $pet['id'] . '" class="w-8 h-8 flex items-center justify-center rounded-full text-amber-600 hover:bg-amber-100 hover:text-amber-800 transition" title="Editar">';
        echo '                <i class="fas fa-edit"></i>';
        echo '            </a>';
        echo '            <a href="pets/agendamentos/agendar_servico.php?pet_id=' . $pet['id'] . '" class="w-8 h-8 flex items-center justify-center rounded-full text-blue-600 hover:bg-blue-100 hover:text-blue-800 transition" title="Agendar Serviço">';
        echo '                <i class="fas fa-calendar-plus"></i>';
        echo '            </a>';
        echo '            <a href="javascript:void(0);" onclick="openConfirmationModal(\'Excluir Pet\', \'Tem certeza que deseja excluir o pet \\\'' . htmlspecialchars($pet['nome'], ENT_QUOTES) . '\\\'? Todos os registros associados serão removidos.\', \'pets/excluir_pet.php?id=' . $pet['id'] . '\')" class="w-8 h-8 flex items-center justify-center rounded-full text-red-600 hover:bg-red-100 hover:text-red-800 transition" title="Excluir">';
        echo '                <i class="fas fa-trash"></i>';
        echo '            </a>';
        echo '        </div>';
        echo '    </td>';
        echo '</tr>';
    }
}
$tableContent = ob_get_clean();

ob_start();
if ($totalPaginas > 1) {
    echo '<div class="flex items-center gap-1">';
    $prevPage = $pagina > 1 ? $pagina - 1 : 1;
    $disabledPrev = $pagina == 1 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-100';
    echo "<button onclick='buscarPets(undefined, {$prevPage})' class='px-3 py-1 text-sm rounded-md bg-white text-slate-700 border border-slate-300 {$disabledPrev}'>&lt;</button>";

    for ($i = 1; $i <= $totalPaginas; $i++) {
        $activeClass = $i == $pagina ? 'bg-blue-600 text-white shadow-sm' : 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-100';
        echo "<button onclick='buscarPets(undefined, {$i})' class='px-3 py-1 text-sm rounded-md {$activeClass}'>{$i}</button>";
    }

    $nextPage = $pagina < $totalPaginas ? $pagina + 1 : $totalPaginas;
    $disabledNext = $pagina == $totalPaginas ? 'opacity-50 cursor-not-allowed' : 'hover:bg-slate-100';
    echo "<button onclick='buscarPets(undefined, {$nextPage})' class='px-3 py-1 text-sm rounded-md bg-white text-slate-700 border border-slate-300 {$disabledNext}'>&gt;</button>";
    echo '</div>';
}
$paginationContent = ob_get_clean();

echo json_encode([
    'tableContent' => $tableContent,
    'paginationContent' => $paginationContent
]);
?>