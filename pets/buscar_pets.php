<?php
include "../config/config.php";
session_start();

// Proteção básica
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    exit('Acesso negado.');
}

$search = isset($_GET['search']) ? trim($_GET['search']) : '';

$query = "SELECT pets.id, pets.nome, tutores.nome AS tutor 
          FROM pets 
          INNER JOIN tutores ON pets.tutor_id = tutores.id";

if ($search) {
    $query .= " WHERE pets.id LIKE :search OR pets.nome LIKE :search OR tutores.nome LIKE :search";
}
$query .= " ORDER BY pets.id DESC";

$stmt = $pdo->prepare($query);
if ($search) {
    $stmt->bindValue(':search', "%$search%");
}
$stmt->execute();
$pets = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($pets)) {
    echo '<tr><td colspan="4" class="p-4 text-center text-slate-500">Nenhum pet encontrado.</td></tr>';
} else {
    foreach ($pets as $pet) {
        echo '<tr class="hover:bg-slate-50 group">';
        echo '    <td class="px-4 py-4 text-slate-500 font-medium cursor-pointer" onclick="window.location=\'pets/visualizar_pet.php?id=' . $pet['id'] . '\'">' . htmlspecialchars($pet['id']) . '</td>';
        echo '    <td class="px-4 py-4 font-semibold text-slate-800 whitespace-nowrap cursor-pointer" onclick="window.location=\'pets/visualizar_pet.php?id=' . $pet['id'] . '\'">' . htmlspecialchars($pet['nome']) . '</td>';
        echo '    <td class="px-4 py-4 text-slate-500 whitespace-nowrap cursor-pointer" onclick="window.location=\'pets/visualizar_pet.php?id=' . $pet['id'] . '\'">' . htmlspecialchars($pet['tutor']) . '</td>';
        echo '    <td class="px-4 py-4">';
        echo '        <div class="flex items-center justify-center gap-3">';
        echo '            <a href="pets/editar_pet.php?id=' . $pet['id'] . '" class="text-amber-600 hover:text-amber-800" title="Editar">';
        echo '                <i class="fas fa-edit"></i>';
        echo '            </a>';
        echo '            <a href="pets/agendamentos/agendar_servico.php?pet_id=' . $pet['id'] . '" class="text-blue-600 hover:text-blue-800" title="Agendar Serviço">';
        echo '                <i class="fas fa-calendar-plus"></i>';
        echo '            </a>';
        echo '            <a href="javascript:void(0);" onclick="openConfirmationModal(\'Excluir Pet\', \'Tem certeza que deseja excluir o pet \\\'' . htmlspecialchars($pet['nome'], ENT_QUOTES) . '\\\'? Todos os registros associados serão removidos.\', \'pets/excluir_pet.php?id=' . $pet['id'] . '\', \'Excluir\', \'bg-red-500\', \'hover:bg-red-600\')" class="text-red-600 hover:text-red-800" title="Excluir">';
        echo '                <i class="fas fa-trash"></i>';
        echo '            </a>';
        echo '        </div>';
        echo '    </td>';
        echo '</tr>';
    }
}
?>