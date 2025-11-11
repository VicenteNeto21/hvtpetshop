<?php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Verifica se o ID do tutor foi passado na URL
if (!isset($_GET['id'])) {
    $_SESSION['mensagem'] = "Tutor não especificado para exclusão.";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: listar_tutores.php");
    exit();
}

$tutorId = $_GET['id'];

try {
    $pdo->beginTransaction();

    // 1. Obter IDs dos pets associados a este tutor
    $stmtPets = $pdo->prepare("SELECT id FROM pets WHERE tutor_id = :tutor_id");
    $stmtPets->bindValue(':tutor_id', $tutorId, PDO::PARAM_INT);
    $stmtPets->execute();
    $petIds = $stmtPets->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($petIds)) {
        $placeholders = implode(',', array_fill(0, count($petIds), '?'));

        // 2. Excluir fichas_petshop e suas dependências (observacoes e servicos)
        // Obter ficha_ids dos agendamentos dos pets
        $stmtFichaIds = $pdo->prepare("SELECT f.id FROM fichas_petshop f JOIN agendamentos a ON f.agendamento_id = a.id WHERE a.pet_id IN ($placeholders)");
        $stmtFichaIds->execute($petIds);
        $fichaIds = $stmtFichaIds->fetchAll(PDO::FETCH_COLUMN);

        if (!empty($fichaIds)) {
            $fichaPlaceholders = implode(',', array_fill(0, count($fichaIds), '?'));
            $pdo->prepare("DELETE FROM ficha_observacoes WHERE ficha_id IN ($fichaPlaceholders)")->execute($fichaIds);
            $pdo->prepare("DELETE FROM ficha_servicos_realizados WHERE ficha_id IN ($fichaPlaceholders)")->execute($fichaIds);
            $pdo->prepare("DELETE FROM fichas_petshop WHERE id IN ($fichaPlaceholders)")->execute($fichaIds);
        }

        // 3. Excluir agendamentos dos pets
        $pdo->prepare("DELETE FROM agendamentos WHERE pet_id IN ($placeholders)")->execute($petIds);

        // 4. Excluir pets
        $pdo->prepare("DELETE FROM pets WHERE id IN ($placeholders)")->execute($petIds);
    }

    // 5. Excluir o tutor
    $pdo->prepare("DELETE FROM tutores WHERE id = :tutor_id")->execute([':tutor_id' => $tutorId]);

    $pdo->commit();
    $_SESSION['mensagem'] = "Tutor e todos os seus pets e registros foram excluídos com sucesso.";
    $_SESSION['tipo_mensagem'] = "success";
} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['mensagem'] = "Erro ao excluir o tutor: " . $e->getMessage();
    $_SESSION['tipo_mensagem'] = "error";
}

header("Location: listar_tutores.php");
exit();
?>