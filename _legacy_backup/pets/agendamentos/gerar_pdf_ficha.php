<?php
require '../../vendor/autoload.php';
use Dompdf\Dompdf;

include "../../config/config.php";
session_start();

// Proteção: verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    die("Acesso negado. Por favor, faça o login.");
}

$agendamentoId = $_POST['agendamento_id'] ?? 0;

if ($agendamentoId == 0) {
    die("ID do agendamento não fornecido.");
}

// --- 1. BUSCA DE DADOS ---

// Consulta principal para obter todos os dados do agendamento e da ficha
$queryFicha = "SELECT 
    a.id as agendamento_id, a.data_hora, a.status, a.observacoes as obs_agendamento,
    p.id as pet_id, p.nome as pet_nome, p.especie, p.raca, p.idade, p.sexo, p.peso, p.pelagem,
    t.id as tutor_id, t.nome as tutor_nome, t.email, t.telefone,
    t.cep, t.rua, t.numero, t.bairro, t.cidade, t.uf,
    f.id as ficha_id, f.altura_pelos, f.doenca_pre_existente, f.doenca_ouvido, f.doenca_pele, f.observacoes,
    f.comportamento_pet, f.recomendacoes_tutor,
    u.nome as funcionario_nome, f.data_preenchimento
  FROM agendamentos a 
  JOIN pets p ON a.pet_id = p.id
  JOIN tutores t ON p.tutor_id = t.id
  LEFT JOIN fichas_petshop f ON f.agendamento_id = a.id
  LEFT JOIN usuarios u ON f.funcionario_id = u.id
  WHERE a.id = :id";

$stmtFicha = $pdo->prepare($queryFicha);
$stmtFicha->bindValue(':id', $agendamentoId, PDO::PARAM_INT);
$stmtFicha->execute();
$ficha = $stmtFicha->fetch(PDO::FETCH_ASSOC);

if (!$ficha) {
    die("Ficha de atendimento não encontrada.");
}

// Busca observações visuais, se a ficha existir
$observacoesVisuais = [];
if ($ficha['ficha_id']) {
    $stmtObs = $pdo->prepare("SELECT ov.descricao, fo.outros_detalhes 
                             FROM ficha_observacoes fo
                             JOIN observacoes_visuais ov ON fo.observacao_id = ov.id
                             WHERE fo.ficha_id = ?");
    $stmtObs->execute([$ficha['ficha_id']]);
    $observacoesVisuais = $stmtObs->fetchAll(PDO::FETCH_ASSOC);
}

// Busca serviços realizados, se a ficha existir
$servicosRealizados = [];
if ($ficha['ficha_id']) {
    $stmtServ = $pdo->prepare("
        SELECT 
            CASE WHEN fsr.servico_id = 99 THEN 'Outros' ELSE s.nome END as nome,
            fsr.outros_detalhes
        FROM ficha_servicos_realizados fsr
        LEFT JOIN servicos s ON fsr.servico_id = s.id
        WHERE fsr.ficha_id = ?
    ");
    $stmtServ->execute([$ficha['ficha_id']]);
    $servicosRealizados = $stmtServ->fetchAll(PDO::FETCH_ASSOC);
}

// Busca todos os serviços que foram solicitados no agendamento original
$stmtServicosAgendamento = $pdo->prepare("
    SELECT s.nome FROM agendamentos a
    JOIN servicos s ON a.servico_id = s.id
    WHERE a.data_hora = :data_hora AND a.pet_id = :pet_id
    GROUP BY s.nome
");
$stmtServicosAgendamento->execute([
    ':data_hora' => $ficha['data_hora'],
    ':pet_id' => $ficha['pet_id']
]);
$servicosAgendamento = $stmtServicosAgendamento->fetchAll(PDO::FETCH_COLUMN);

// --- 2. FUNÇÕES AUXILIARES ---

function formatarTelefone($telefone) {
    $limpo = preg_replace('/[^0-9]/', '', $telefone);
    if (strlen($limpo) == 11) return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $limpo);
    if (strlen($limpo) == 10) return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $limpo);
    return $telefone;
}

function formatarData($data, $formato = 'd/m/Y H:i') {
    if (empty($data) || $data === '0000-00-00 00:00:00') return 'N/A';
    return (new DateTime($data))->format($formato);
}

function montarEndereco($ficha) {
    $partes = array_filter([
        $ficha['rua'] ?? null,
        isset($ficha['numero']) ? 'Nº ' . $ficha['numero'] : null,
        $ficha['bairro'] ?? null,
        isset($ficha['cidade']) && isset($ficha['uf']) ? "{$ficha['cidade']}/{$ficha['uf']}" : null,
        isset($ficha['cep']) ? 'CEP: ' . $ficha['cep'] : null
    ]);
    return !empty($partes) ? implode(', ', $partes) : 'Não informado';
}

// --- 3. MONTAGEM DO HTML PARA O PDF ---

ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ficha de Atendimento - <?= htmlspecialchars($ficha['pet_nome']) ?></title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #334155; line-height: 1.4; margin: 0; }
        @page { margin: 10mm; }
        .container { width: 100%; }
        .header { text-align: center; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0; }
        .header h1 { font-size: 20px; font-weight: bold; color: #1e293b; margin: 0 0 5px 0; }
        .header p { font-size: 12px; color: #64748b; margin: 0; }
        .section-title { font-size: 14px; font-weight: bold; color: #1e293b; margin-top: 15px; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; }
        .card { background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px; padding: 12px; margin-bottom: 12px; page-break-inside: avoid; }
        .grid-table { width: 100%; border-collapse: collapse; }
        .grid-table td { vertical-align: top; padding: 0 5px; }
        .grid-table td:first-child { padding-left: 0; }
        .grid-table td:last-child { padding-right: 0; }
        dl { margin: 0; padding: 0; }
        dt { font-weight: bold; color: #64748b; font-size: 10px; margin-bottom: 2px; text-transform: uppercase; }
        dd { color: #1e293b; font-size: 11px; margin: 0 0 8px 0; min-height: 12px; }
        dd.pre-wrap { white-space: pre-wrap; }
        ul { list-style: disc; margin: 0 0 0 15px; padding: 0; }
        li { margin-bottom: 3px; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 9px; font-weight: bold; }
        .badge-yellow { background-color: #fef3c7; color: #92400e; }
        .badge-blue { background-color: #dbeafe; color: #1e40af; }
        .badge-green { background-color: #dcfce7; color: #166534; }
        .badge-red { background-color: #fee2e2; color: #991b1b; }
        .badge-slate { background-color: #f1f5f9; color: #475569; }
        .footer { text-align: center; color: #94a3b8; font-size: 9px; position: fixed; bottom: -40px; left: 0; right: 0; }
        .no-data { color: #64748b; text-align: center; padding: 10px; border: 1px dashed #e2e8f0; border-radius: 6px; font-size: 10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>CereniaPet - Ficha de Atendimento</h1>
            <p>Relatório de Serviço e Avaliação</p>
        </div>

        <table class="grid-table" style="margin-bottom: 12px;">
            <tr>
                <td style="width: 50%;">
                    <div class="card">
                        <div class="section-title">Dados do Pet</div>
                        <dl>
                            <dt>Nome</dt><dd><?= htmlspecialchars($ficha['pet_nome']) ?></dd>
                            <dt>Espécie / Raça</dt><dd><?= htmlspecialchars($ficha['especie']) ?> / <?= htmlspecialchars($ficha['raca']) ?></dd>
                            <dt>Idade / Sexo</dt><dd><?= htmlspecialchars($ficha['idade']) ?> ano(s) / <?= htmlspecialchars($ficha['sexo']) ?></dd>
                            <dt>Peso / Pelagem</dt><dd><?= htmlspecialchars($ficha['peso']) ?> kg / <?= htmlspecialchars($ficha['pelagem'] ?: 'N/A') ?></dd>
                        </dl>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="card">
                        <div class="section-title">Dados do Tutor</div>
                        <dl>
                            <dt>Nome</dt><dd><?= htmlspecialchars($ficha['tutor_nome']) ?></dd>
                            <dt>Contato</dt><dd><?= htmlspecialchars(formatarTelefone($ficha['telefone'])) ?> | <?= htmlspecialchars($ficha['email']) ?></dd>
                            <dt>Endereço</dt><dd><?= htmlspecialchars(montarEndereco($ficha)) ?></dd>
                        </dl>
                    </div>
                </td>
            </tr>
        </table>

        <div class="card">
            <div class="section-title">Detalhes do Atendimento</div>
            <table class="grid-table">
                <tr>
                    <td style="width: 33%;"><dt>Data/Hora</dt><dd><?= formatarData($ficha['data_hora']) ?></dd></td>
                    <td style="width: 33%;"><dt>Atendido por</dt><dd><?= htmlspecialchars($ficha['funcionario_nome'] ?? 'N/A') ?></dd></td>
                    <td style="width: 33%;"><dt>Status</dt><dd>
                        <span class="badge <?php
                            if ($ficha['status'] == 'Pendente') echo 'badge-yellow';
                            elseif ($ficha['status'] == 'Em Atendimento') echo 'badge-blue';
                            elseif ($ficha['status'] == 'Finalizado') echo 'badge-green';
                            elseif ($ficha['status'] == 'Cancelado') echo 'badge-red';
                            else echo 'badge-slate';
                        ?>"><?= htmlspecialchars($ficha['status']) ?></span>
                    </dd></td>
                </tr>
                <tr>
                    <td colspan="3"><dt>Serviços Solicitados</dt><dd><?= htmlspecialchars(implode(', ', $servicosAgendamento) ?: 'N/A') ?></dd></td>
                </tr>
            </table>
        </div>

        <table class="grid-table">
            <tr>
                <td style="width: 50%;">
                    <div class="card">
                        <div class="section-title">Avaliação Visual</div>
                        <?php if (!empty($observacoesVisuais)): ?>
                            <ul>
                                <?php foreach ($observacoesVisuais as $obs): ?>
                                    <li><?= htmlspecialchars($obs['descricao']) ?><?= !empty($obs['outros_detalhes']) ? ': ' . htmlspecialchars($obs['outros_detalhes']) : '' ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <div class="no-data">Nenhuma observação visual registrada.</div>
                        <?php endif; ?>
                    </div>
                </td>
                <td style="width: 50%;">
                    <div class="card">
                        <div class="section-title">Serviços Realizados</div>
                        <?php if (!empty($servicosRealizados)): ?>
                            <ul>
                                <?php foreach ($servicosRealizados as $serv): ?>
                                    <li><?= htmlspecialchars($serv['nome']) ?><?= !empty($serv['outros_detalhes']) ? ': ' . htmlspecialchars($serv['outros_detalhes']) : '' ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php if (!empty($ficha['altura_pelos'])): ?>
                                <p style="margin-top: 8px;"><dt>Altura dos Pelos:</dt> <dd style="margin-bottom: 0;"><?= htmlspecialchars($ficha['altura_pelos']) ?></dd></p>
                            <?php endif; ?>
                        <?php else: ?>
                            <div class="no-data">Nenhum serviço efetivamente realizado.</div>
                        <?php endif; ?>
                    </div>
                </td>
            </tr>
        </table>

        <div class="card">
            <div class="section-title">Saúde e Observações Gerais</div>
            <table class="grid-table">
                <tr>
                    <td style="width: 33%;"><dt>Doença Pré-Existente</dt><dd><?= htmlspecialchars($ficha['doenca_pre_existente'] ?: 'Nenhuma') ?></dd></td>
                    <td style="width: 33%;"><dt>Doença Canal Auditivo/Otite</dt><dd><?= htmlspecialchars($ficha['doenca_ouvido'] ?: 'Nenhuma') ?></dd></td>
                    <td style="width: 33%;"><dt>Doença de Pele</dt><dd><?= htmlspecialchars($ficha['doenca_pele'] ?: 'Nenhuma') ?></dd></td>
                </tr>
                <tr>
                    <td colspan="3"><dt>Observações Adicionais</dt><dd class="pre-wrap"><?= nl2br(htmlspecialchars($ficha['observacoes'] ?: 'Nenhuma')) ?></dd></td>
                </tr>
            </table>
        </div>

        <div class="card">
            <div class="section-title">Comentários Finais</div>
            <table class="grid-table">
                <tr>
                    <td style="width: 50%;"><dt>Comportamento do Pet</dt><dd class="pre-wrap"><?= nl2br(htmlspecialchars($ficha['comportamento_pet'] ?: 'Nenhum registro.')) ?></dd></td>
                    <td style="width: 50%;"><dt>Recomendações para o Tutor</dt><dd class="pre-wrap"><?= nl2br(htmlspecialchars($ficha['recomendacoes_tutor'] ?: 'Nenhuma recomendação.')) ?></dd></td>
                </tr>
            </table>
        </div>

        <div class="footer">
            CereniaPet &copy; <?= date("Y") ?>. Todos os direitos reservados.
        </div>
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

// --- 4. GERAÇÃO DO PDF ---

$options = new \Dompdf\Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true); // Permite carregar imagens externas, se houver

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$nomeArquivo = "ficha_atendimento_" . str_replace(' ', '_', $ficha['pet_nome']) . "_" . $ficha['agendamento_id'] . ".pdf";
$dompdf->stream($nomeArquivo, ["Attachment" => false]); // false para abrir no navegador, true para baixar

exit;
?>