<?php
include "../../config/config.php";
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado.']);
    exit();
}

header('Content-Type: application/json');

$dataSelecionada = $_GET['data'] ?? null;
$agendamentoIdAtual = isset($_GET['agendamento_id']) ? (int)$_GET['agendamento_id'] : null;

if (!$dataSelecionada || !strtotime($dataSelecionada)) {
    echo json_encode([]);
    exit();
}

try {
    // 1. Gerar todos os horários possíveis
    $horariosPossiveis = [];
    $inicio = new DateTime('08:00');
    $fim = new DateTime('18:00');
    $intervalo = new DateInterval('PT30M');
    $periodo = new DatePeriod($inicio, $intervalo, $fim->modify('+1 second'));
    foreach ($periodo as $hora) {
        $horariosPossiveis[] = $hora->format('H:i');
    }

    // 2. Buscar horários já agendados para a data
    $query = "SELECT DISTINCT DATE_FORMAT(data_hora, '%H:%i') as horario FROM agendamentos WHERE DATE(data_hora) = :data";
    $params = [':data' => $dataSelecionada];

    // Se estiver editando um agendamento, precisamos excluir o horário do próprio agendamento da verificação
    // para permitir que o usuário o mantenha.
    if ($agendamentoIdAtual) {
        $stmtInfo = $pdo->prepare("SELECT pet_id, data_hora FROM agendamentos WHERE id = :id");
        $stmtInfo->execute([':id' => $agendamentoIdAtual]);
        $agendamentoInfo = $stmtInfo->fetch();

        if ($agendamentoInfo && date('Y-m-d', strtotime($agendamentoInfo['data_hora'])) == $dataSelecionada) {
             $query .= " AND data_hora != :data_hora_atual";
             $params[':data_hora_atual'] = $agendamentoInfo['data_hora'];
        }
    }

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $horariosOcupados = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // 3. Retornar apenas os horários disponíveis
    echo json_encode(array_values(array_diff($horariosPossiveis, $horariosOcupados)));

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}