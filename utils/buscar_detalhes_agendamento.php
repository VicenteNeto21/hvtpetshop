<?php
include "../config/config.php";
session_start();

if (!isset($_SESSION['usuario_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado.']);
    exit();
}

header('Content-Type: application/json');

if (!isset($_GET['id'])) {
    echo json_encode(['error' => 'ID do agendamento não informado.']);
    exit();
}

$agendamentoId = (int)$_GET['id'];

// Busca o agendamento principal para obter o grupo (pet_id, data_hora)
$stmtAgendamento = $pdo->prepare("
    SELECT a.pet_id, a.data_hora, a.status, a.transporte, a.observacoes, a.criado_em,
           p.nome as pet_nome, p.especie, p.raca, p.nascimento,
           t.nome as tutor_nome, t.telefone as tutor_telefone,
           t.rua, t.numero, t.bairro, t.cidade, t.uf, t.cep,
           u.nome as usuario_nome
    FROM agendamentos a
    JOIN pets p ON a.pet_id = p.id
    JOIN tutores t ON p.tutor_id = t.id
    LEFT JOIN usuarios u ON a.usuario_id = u.id
    WHERE a.id = :id
");
$stmtAgendamento->execute([':id' => $agendamentoId]);
$agendamento = $stmtAgendamento->fetch(PDO::FETCH_ASSOC);

if (!$agendamento) {
    echo json_encode(['error' => 'Agendamento não encontrado.']);
    exit();
}

// Busca todos os serviços do grupo
$stmtServicos = $pdo->prepare("
    SELECT s.nome 
    FROM agendamentos a
    JOIN servicos s ON a.servico_id = s.id
    WHERE a.pet_id = :pet_id AND a.data_hora = :data_hora
    ORDER BY s.nome
");
$stmtServicos->execute([':pet_id' => $agendamento['pet_id'], ':data_hora' => $agendamento['data_hora']]);
$servicos = $stmtServicos->fetchAll(PDO::FETCH_COLUMN);

// Formata a idade do pet
$idade = '';
if ($agendamento['nascimento']) {
    $nascimento = new DateTime($agendamento['nascimento']);
    $hoje = new DateTime();
    $diff = $nascimento->diff($hoje);
    if ($diff->y > 0) {
        $idade = $diff->y . ' ano' . ($diff->y > 1 ? 's' : '');
        if ($diff->m > 0) {
            $idade .= ' e ' . $diff->m . ' ' . ($diff->m > 1 ? 'meses' : 'mês');
        }
    } else {
        $idade = $diff->m . ' ' . ($diff->m > 1 ? 'meses' : 'mês');
    }
}

// Formata telefone
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    if (strlen($telefone) === 11) return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    if (strlen($telefone) === 10) return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    return $telefone;
}

// Monta o endereço completo
$endereco = '';
if ($agendamento['rua']) {
    $partes = [];
    $partes[] = $agendamento['rua'];
    if ($agendamento['numero']) $partes[0] .= ', ' . $agendamento['numero'];
    if ($agendamento['bairro']) $partes[] = $agendamento['bairro'];
    if ($agendamento['cidade']) {
        $cidadeUf = $agendamento['cidade'];
        if ($agendamento['uf']) $cidadeUf .= ' - ' . $agendamento['uf'];
        $partes[] = $cidadeUf;
    }
    if ($agendamento['cep']) $partes[] = 'CEP: ' . $agendamento['cep'];
    $endereco = implode(' • ', $partes);
}

// Verifica se o transporte envolve o Petshop (buscar ou entregar)
$mostrarEndereco = strpos($agendamento['transporte'] ?? '', 'Petshop') !== false;

echo json_encode([
    'success' => true,
    'agendamento' => [
        'pet_nome' => $agendamento['pet_nome'],
        'pet_especie' => $agendamento['especie'],
        'pet_raca' => $agendamento['raca'],
        'pet_idade' => $idade,
        'tutor_nome' => $agendamento['tutor_nome'],
        'tutor_telefone' => formatarTelefone($agendamento['tutor_telefone']),
        'tutor_endereco' => $endereco,
        'mostrar_endereco' => $mostrarEndereco,
        'data_hora' => date('d/m/Y H:i', strtotime($agendamento['data_hora'])),
        'status' => $agendamento['status'],
        'transporte' => $agendamento['transporte'],
        'servicos' => implode(', ', $servicos),
        'observacoes' => $agendamento['observacoes'] ?: 'Nenhuma observação',
        'criado_por' => $agendamento['usuario_nome'] ?: 'Sistema',
        'criado_em' => date('d/m/Y H:i', strtotime($agendamento['criado_em']))
    ]
]);
?>
