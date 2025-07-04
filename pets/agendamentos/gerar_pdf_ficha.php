<?php
require '../../vendor/autoload.php'; // DomPDF via Composer
use Dompdf\Dompdf;

include "../../config/config.php";
session_start();

$agendamentoId = $_POST['agendamento_id'] ?? 0;

// Consulta os dados da ficha (copie a mesma consulta do visualizar_ficha.php)
$query = "SELECT 
            a.id as agendamento_id, a.data_hora, a.status, a.observacoes as obs_agendamento,
            p.id as pet_id, p.nome as pet_nome, p.especie, p.raca, p.idade, p.sexo, p.peso, p.pelagem,
            t.id as tutor_id, t.nome as tutor_nome, t.email, t.telefone,
            s.nome as servico_nome,
            f.id as ficha_id, f.altura_pelos, f.doenca_pre_existente, f.doenca_ouvido, f.doenca_pele, f.observacoes,
            u.nome as funcionario_nome, f.data_preenchimento
          FROM agendamentos a
          JOIN pets p ON a.pet_id = p.id
          JOIN tutores t ON p.tutor_id = t.id
          JOIN servicos s ON a.servico_id = s.id
          LEFT JOIN fichas_petshop f ON f.agendamento_id = a.id
          LEFT JOIN usuarios u ON f.funcionario_id = u.id
          WHERE a.id = :id";

$stmt = $pdo->prepare($query);
$stmt->bindValue(':id', $agendamentoId);
$stmt->execute();
$ficha = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$ficha) {
    die("Ficha não encontrada.");
}

// Observações visuais
$observacoes = [];
if ($ficha['ficha_id']) {
    $stmtObs = $pdo->prepare("SELECT ov.descricao, fo.outros_detalhes 
                             FROM ficha_observacoes fo
                             JOIN observacoes_visuais ov ON fo.observacao_id = ov.id
                             WHERE fo.ficha_id = ?");
    $stmtObs->execute([$ficha['ficha_id']]);
    $observacoes = $stmtObs->fetchAll(PDO::FETCH_ASSOC);
}

// Serviços realizados
$servicos = [];
if ($ficha['ficha_id']) {
    $stmtServ = $pdo->prepare("
        SELECT 
            CASE 
                WHEN fsr.servico_id = 0 THEN 'Outros'
                ELSE s.nome
            END as nome,
            fsr.outros_detalhes
        FROM ficha_servicos_realizados fsr
        LEFT JOIN servicos s ON fsr.servico_id = s.id
        WHERE fsr.ficha_id = ?
    ");
    $stmtServ->execute([$ficha['ficha_id']]);
    $servicos = $stmtServ->fetchAll(PDO::FETCH_ASSOC);
}

// Funções auxiliares
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    if (strlen($telefone) === 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } elseif (strlen($telefone) === 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }
    return $telefone;
}
function formatarData($data, $formato = 'd/m/Y H:i') {
    if (empty($data)) return '';
    $date = new DateTime($data);
    return $date->format($formato);
}

// Monta o HTML do PDF
ob_start();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Ficha do Petshop - PDF</title>
    <style>
        body { font-family: DejaVu Sans, Arial, Helvetica, sans-serif; font-size: 12px; color: #222; }
        .titulo { text-align: center; font-size: 20px; font-weight: bold; color: #2563eb; margin-bottom: 5px; }
        .subtitulo { text-align: center; font-size: 13px; color: #666; margin-bottom: 15px; }
        .bloco { border: 1px solid #e0e7ef; border-radius: 10px; padding: 10px 15px; margin-bottom: 12px; }
        .bloco-titulo { font-weight: bold; color: #2563eb; margin-bottom: 4px; }
        .linha { margin-bottom: 4px; }
        .label { color: #666; font-size: 11px; }
        .valor { font-weight: bold; color: #222; }
        ul { margin: 0 0 0 18px; }
        li { margin-bottom: 2px; }
        .separador { border-bottom: 1px solid #e0e7ef; margin: 12px 0; }
        .footer { text-align: center; color: #888; font-size: 10px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="titulo">Ficha do Salão de Beleza</div>
    <div class="subtitulo">Hospital Veterinário Lourival Rodrigues</div>

    <div class="bloco">
        <div class="bloco-titulo">Tutor <span style="color:#888;font-weight:normal;">(ID: <?= htmlspecialchars($ficha['tutor_id']) ?>)</span></div>
        <div class="linha"><span class="label">Nome:</span> <span class="valor"><?= htmlspecialchars($ficha['tutor_nome']) ?></span></div>
        <div class="linha"><span class="label">Telefone:</span> <span class="valor"><?= htmlspecialchars(formatarTelefone($ficha['telefone'])) ?></span></div>
        <div class="linha"><span class="label">E-mail:</span> <span class="valor"><?= htmlspecialchars($ficha['email']) ?></span></div>
    </div>

    <div class="bloco">
        <div class="bloco-titulo">Pet</div>
        <div class="linha"><span class="label">Nome:</span> <span class="valor"><?= htmlspecialchars($ficha['pet_nome']) ?></span></div>
        <div class="linha"><span class="label">Espécie:</span> <span class="valor"><?= htmlspecialchars($ficha['especie']) ?></span></div>
        <div class="linha"><span class="label">Raça:</span> <span class="valor"><?= htmlspecialchars($ficha['raca']) ?></span></div>
        <div class="linha"><span class="label">Sexo:</span> <span class="valor"><?= htmlspecialchars($ficha['sexo']) ?></span></div>
        <div class="linha"><span class="label">Idade:</span> <span class="valor"><?= htmlspecialchars($ficha['idade']) ?></span></div>
        <div class="linha"><span class="label">Peso:</span> <span class="valor"><?= htmlspecialchars($ficha['peso']) ?> kg</span></div>
        <div class="linha"><span class="label">Pelagem:</span> <span class="valor"><?= htmlspecialchars($ficha['pelagem']) ?></span></div>
    </div>

    <div class="bloco">
        <div class="bloco-titulo">Atendimento</div>
        <div class="linha"><span class="label">Data/Hora:</span> <span class="valor"><?= formatarData($ficha['data_hora']) ?></span></div>
        <div class="linha"><span class="label">Status:</span> <span class="valor"><?= htmlspecialchars($ficha['status']) ?></span></div>
        <div class="linha"><span class="label">Atendido por:</span> <span class="valor"><?= htmlspecialchars($ficha['funcionario_nome'] ?? 'N/A') ?></span></div>
        <div class="linha"><span class="label">Data do atendimento:</span> <span class="valor"><?= formatarData($ficha['data_preenchimento'] ?? '') ?></span></div>
    </div>

    <div class="bloco">
        <div class="bloco-titulo">Serviços Solicitados</div>
        <div class="linha">
            <?php
            $stmtServicosAgendamento = $pdo->prepare("
                SELECT s.nome
                FROM agendamentos a
                JOIN servicos s ON a.servico_id = s.id
                WHERE a.data_hora = :data_hora AND a.pet_id = :pet_id
                GROUP BY s.nome
            ");
            $stmtServicosAgendamento->execute([
                ':data_hora' => $ficha['data_hora'],
                ':pet_id' => $ficha['pet_id']
            ]);
            $servicosAgendamento = $stmtServicosAgendamento->fetchAll(PDO::FETCH_COLUMN);

            if (!empty($servicosAgendamento)) {
                echo htmlspecialchars(implode(', ', $servicosAgendamento));
            } else {
                echo 'Nenhum serviço';
            }
            ?>
        </div>
    </div>

    <div class="bloco">
        <div class="bloco-titulo">Observações Visuais</div>
        <?php if (!empty($observacoes)): ?>
            <ul>
                <?php foreach ($observacoes as $obs): ?>
                    <li>
                        <?= htmlspecialchars($obs['descricao']) ?>
                        <?php if (!empty($obs['outros_detalhes'])): ?>
                            - <?= htmlspecialchars($obs['outros_detalhes']) ?>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <div class="linha">Nenhuma observação visual registrada.</div>
        <?php endif; ?>
    </div>

    <div class="bloco">
        <div class="bloco-titulo">Serviços Realizados</div>
        <?php if (!empty($servicos)): ?>
            <ul>
                <?php
                $servicosAgrupados = [];
                foreach ($servicos as $serv) {
                    $nome = $serv['nome'];
                    $detalhe = trim($serv['outros_detalhes']);
                    if (!isset($servicosAgrupados[$nome])) {
                        $servicosAgrupados[$nome] = [];
                    }
                    if ($detalhe !== '') {
                        $servicosAgrupados[$nome][] = $detalhe;
                    }
                }
                foreach ($servicosAgrupados as $nome => $detalhes) {
                    echo '<li>';
                    echo htmlspecialchars($nome);
                    if (!empty($detalhes)) {
                        echo ' - ' . htmlspecialchars(implode(', ', $detalhes));
                    }
                    echo '</li>';
                }
                ?>
            </ul>
            <?php if (!empty($ficha['altura_pelos'])): ?>
                <div class="linha"><strong>Cumprimento/altura dos pelos:</strong> <?= htmlspecialchars($ficha['altura_pelos']) ?></div>
            <?php endif; ?>
        <?php else: ?>
            <div class="linha">Nenhum serviço registrado.</div>
        <?php endif; ?>
    </div>

    <div class="bloco">
        <div class="bloco-titulo">Saúde e Observações</div>
        <div class="linha"><span class="label">Doença Pré-Existente:</span> <span class="valor"><?= !empty($ficha['doenca_pre_existente']) ? htmlspecialchars($ficha['doenca_pre_existente']) : 'Nenhuma' ?></span></div>
        <div class="linha"><span class="label">Doença Canal Auditivo/Otite:</span> <span class="valor"><?= !empty($ficha['doenca_ouvido']) ? htmlspecialchars($ficha['doenca_ouvido']) : 'Nenhuma' ?></span></div>
        <div class="linha"><span class="label">Doença de Pele:</span> <span class="valor"><?= !empty($ficha['doenca_pele']) ? htmlspecialchars($ficha['doenca_pele']) : 'Nenhuma' ?></span></div>
        <div class="linha"><span class="label">Observações Adicionais:</span> <span class="valor"><?= !empty($ficha['observacoes']) ? nl2br(htmlspecialchars($ficha['observacoes'])) : 'Nenhuma' ?></span></div>
    </div>

    <div class="footer">
        Av. Dr. Edilberto Frota, 1103 - Fatima II – Crateús/CE – CEP: 63702-030<br>
        Celular/WhatsApp: (88) 9.9673-1101<br>
        &copy; 2025 HVTPETSHOP. Todos os direitos reservados.
    </div>
</body>
</html>
<?php
$html = ob_get_clean();

$dompdf = new Dompdf(['isHtml5ParserEnabled' => true, 'isRemoteEnabled' => true]);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("ficha_petshop_{$ficha['pet_nome']}_{$ficha['agendamento_id']}.pdf", ["Attachment" => true]);
exit;