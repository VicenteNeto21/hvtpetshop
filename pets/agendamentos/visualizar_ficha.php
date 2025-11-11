<?php
include "../../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit();
}

// Verifica se há um agendamento específico
if (!isset($_GET['id'])) {
    header("Location: ../../dashboard.php");
    exit();
}

$agendamentoId = $_GET['id'];

// Consulta as informações completas da ficha
$query = "SELECT 
            a.id as agendamento_id, a.data_hora, a.status, a.observacoes as obs_agendamento,
            p.id as pet_id, p.nome as pet_nome, p.especie, p.raca, p.idade, p.sexo, p.peso, p.pelagem,
            t.id as tutor_id, t.nome as tutor_nome, t.email, t.telefone, t.cep, t.rua, t.numero, t.bairro, t.cidade, t.uf,
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
    $_SESSION['mensagem'] = "Ficha não encontrada";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../../dashboard.php"); // Redireciona para o dashboard se a ficha não for encontrada
    exit();
}

// Função para montar endereço completo do tutor
function montarEndereco($ficha) {
    $partes = [];
    if (!empty($ficha['rua']))      $partes[] = $ficha['rua'];
    if (!empty($ficha['numero']))   $partes[] = 'Nº ' . $ficha['numero'];
    if (!empty($ficha['bairro']))   $partes[] = $ficha['bairro'];
    if (!empty($ficha['cidade']))   $partes[] = $ficha['cidade'];
    if (!empty($ficha['uf']))       $partes[] = $ficha['uf'];
    if (!empty($ficha['cep']))      $partes[] = 'CEP: ' . $ficha['cep'];
    return implode(', ', $partes);
}

// Consulta observações visuais marcadas
$observacoes = [];
if ($ficha['ficha_id']) {
    $stmtObs = $pdo->prepare("SELECT ov.descricao, fo.outros_detalhes 
                             FROM ficha_observacoes fo
                             JOIN observacoes_visuais ov ON fo.observacao_id = ov.id
                             WHERE fo.ficha_id = ?");
    $stmtObs->execute([$ficha['ficha_id']]);
    $observacoes = $stmtObs->fetchAll(PDO::FETCH_ASSOC);
}

// Consulta serviços realizados
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

// Função para formatar data
function formatarData($data, $formato = 'd/m/Y H:i') {
    if (empty($data)) return '';
    $date = new DateTime($data);
    return $date->format($formato);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha do Petshop - CereniaPet</title>
    <link rel="icon" type="image/x-icon" href="../../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <?php
    $path_prefix = '../../';
    include '../../components/navbar.php';
    ?>
    <?php include '../../components/toast.php'; ?>

    <main class="flex-1 w-full p-4 md:p-6 lg:p-8">
        <!-- Cabeçalho da Página -->
        <div class="mb-8 animate-fade-in">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-slate-800 flex items-center gap-3">
                        <i class="fa-solid fa-file-medical text-sky-500"></i>
                        Ficha de Atendimento
                    </h1>
                    <p class="text-slate-500 mt-1">Detalhes completos do atendimento do pet.</p>
                </div>
                <div class="flex items-center gap-2">
                    <form action="gerar_pdf_ficha.php" method="POST" target="_blank">
                        <input type="hidden" name="agendamento_id" value="<?= $ficha['agendamento_id'] ?>">
                        <button type="submit" class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg shadow-sm transition flex items-center gap-2">
                            <i class="fas fa-file-pdf"></i> Gerar PDF
                        </button>
                    </form>
                    <a href="../visualizar_pet.php?id=<?= $ficha['pet_id'] ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold py-2 px-4 rounded-lg shadow-sm transition flex items-center gap-2">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                </div>
            </div>
        </div>

        <div id="fichaContent" class="bg-white p-6 md:p-8 rounded-lg shadow-sm animate-fade-in">
            <!-- DADOS DO TUTOR E PET -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Informações do Pet -->
                <div class="bg-slate-50 p-5 rounded-lg border border-slate-200">
                    <h2 class="text-xl font-bold text-slate-800 mb-3 flex items-center gap-3">
                        <i class="fa-solid fa-dog text-violet-500"></i> Pet: <?= htmlspecialchars($ficha['pet_nome']); ?>
                    </h2>
                    <dl class="grid grid-cols-2 gap-x-6 gap-y-3 text-sm">
                        <div>
                            <dt class="font-medium text-slate-500">Espécie</dt>
                            <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($ficha['especie']); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Raça</dt>
                            <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($ficha['raca']); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Sexo</dt>
                            <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($ficha['sexo']); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Idade</dt>
                            <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($ficha['idade']); ?> ano(s)</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Peso</dt>
                            <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($ficha['peso']); ?> kg</dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Pelagem</dt>
                            <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($ficha['pelagem'] ?: 'N/A'); ?></dd>
                        </div>
                    </dl>
                </div>
                <!-- Informações do Tutor -->
                <div class="bg-slate-50 p-5 rounded-lg border border-slate-200">
                    <h2 class="text-xl font-bold text-slate-800 mb-3 flex items-center gap-3">
                        <i class="fa-solid fa-user text-amber-500"></i> Tutor: <?= htmlspecialchars($ficha['tutor_nome']); ?>
                    </h2>
                    <dl class="space-y-3 text-sm">
                        <div>
                            <dt class="font-medium text-slate-500">Telefone</dt>
                            <dd class="text-slate-800 font-semibold"><?= htmlspecialchars(formatarTelefone($ficha['telefone'])); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">E-mail</dt>
                            <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($ficha['email']); ?></dd>
                        </div>
                        <div>
                            <dt class="font-medium text-slate-500">Endereço</dt>
                            <dd class="text-slate-800 font-semibold"><?= montarEndereco($ficha); ?></dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- INFORMAÇÕES DO ATENDIMENTO -->
            <div class="bg-slate-50 p-5 rounded-lg border border-slate-200 mb-8">
                <h2 class="text-xl font-bold text-slate-800 mb-3 flex items-center gap-3">
                    <i class="fa-solid fa-calendar-check text-sky-500"></i> Detalhes do Agendamento
                </h2>
                <dl class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-6 gap-y-3 text-sm">
                    <div>
                        <dt class="font-medium text-slate-500">Data/Hora</dt>
                        <dd class="text-slate-800 font-semibold"><?= formatarData($ficha['data_hora']) ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Status</dt>
                        <dd>
                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold
                                <?= $ficha['status'] == 'Pendente' ? 'bg-yellow-100 text-yellow-800' : 
                                   ($ficha['status'] == 'Em Atendimento' ? 'bg-blue-100 text-blue-800' : 
                                   ($ficha['status'] == 'Finalizado' ? 'bg-green-100 text-green-800' : 
                                   ($ficha['status'] == 'Cancelado' ? 'bg-red-100 text-red-800' : 'bg-slate-100 text-slate-800'))); ?>">
                                <?= htmlspecialchars($ficha['status']) ?>
                            </span>
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Serviços Solicitados</dt>
                        <dd class="text-slate-800 font-semibold">
                        <?php
                        // Buscar todos os serviços do agendamento (usando o grupo do banco)
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
                        </dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Atendido por</dt>
                        <dd class="text-slate-800 font-semibold"><?= htmlspecialchars($ficha['funcionario_nome'] ?? 'N/A') ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Data do atendimento</dt>
                        <dd class="text-slate-800 font-semibold"><?= formatarData($ficha['data_preenchimento'] ?? '') ?></dd>
                    </div>
                    <div class="col-span-full">
                        <dt class="font-medium text-slate-500">Observações do Agendamento</dt>
                        <dd class="text-slate-800 font-semibold whitespace-pre-wrap"><?= htmlspecialchars($ficha['obs_agendamento'] ?: 'Nenhuma observação.') ?></dd>
                    </div>
                </dl>
            </div>

            <!-- Observações Visuais -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in mb-8">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-eye text-sky-500"></i> Avaliação Visual
                </h2>
                <?php if (!empty($observacoes)): ?>
                    <ul class="list-disc list-inside pl-2 text-slate-700">
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
                    <div class="text-slate-500 text-center py-4 border-2 border-dashed rounded-lg">Nenhuma observação visual registrada.</div>
                <?php endif; ?>
            </div>

            <!-- SERVIÇOS REALIZADOS agrupados -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in mb-8">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-scissors text-green-500"></i> Serviços Realizados
                </h2>
                <?php if (!empty($servicos)): ?>
                    <ul class="list-disc list-inside pl-2 text-slate-700">
                        <?php
                        // Agrupar serviços iguais e juntar detalhes extras
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
                        <p class="mt-4 text-slate-700 font-semibold">Cumprimento/altura dos pelos: <span class="font-normal"><?= htmlspecialchars($ficha['altura_pelos']) ?></span></p>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="text-slate-500 text-center py-4 border-2 border-dashed rounded-lg">Nenhum serviço registrado.</div>
                <?php endif; ?>
            </div>

            <!-- Saúde e Observações -->
            <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in mb-8">
                <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                    <i class="fa-solid fa-heart-pulse text-red-500"></i> Saúde e Observações
                </h2>
                <dl class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-4 text-sm">
                    <div>
                        <dt class="font-medium text-slate-500">Doença Pré-Existente</dt>
                        <dd class="text-slate-800 font-semibold"><?= !empty($ficha['doenca_pre_existente']) ? htmlspecialchars($ficha['doenca_pre_existente']) : 'Nenhuma' ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Doença Canal Auditivo/Otite</dt>
                        <dd class="text-slate-800 font-semibold"><?= !empty($ficha['doenca_ouvido']) ? htmlspecialchars($ficha['doenca_ouvido']) : 'Nenhuma' ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-slate-500">Doença de Pele</dt>
                        <dd class="text-slate-800 font-semibold"><?= !empty($ficha['doenca_pele']) ? htmlspecialchars($ficha['doenca_pele']) : 'Nenhuma' ?></dd>
                    </div>
                    <div class="col-span-full">
                        <dt class="font-medium text-slate-500">Observações Adicionais</dt>
                        <dd class="text-slate-800 font-semibold whitespace-pre-wrap"><?= !empty($ficha['observacoes']) ? nl2br(htmlspecialchars($ficha['observacoes'])) : 'Nenhuma' ?></dd>
                    </div>
                </dl>
            </div>
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

    <script>
    </script>
</body>
</html>