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
$usuarioId = $_SESSION['usuario_id'];

// Consulta as informações do agendamento, pet e tutor
$query = "SELECT a.*, p.nome as pet_nome, p.especie, p.raca, p.idade, p.sexo, p.peso, p.pelagem, 
                 t.nome as tutor_nome, t.telefone as tutor_telefone, s.nome as servico_nome,
                 f.id as ficha_id, f.altura_pelos, f.doenca_pre_existente, f.doenca_ouvido, f.doenca_pele, f.observacoes
          FROM agendamentos a
          JOIN pets p ON a.pet_id = p.id
          JOIN tutores t ON p.tutor_id = t.id
          JOIN servicos s ON a.servico_id = s.id
          LEFT JOIN fichas_petshop f ON f.agendamento_id = a.id
          WHERE a.id = :id";
$stmt = $pdo->prepare($query);
$stmt->bindValue(':id', $agendamentoId);
$stmt->execute();
$agendamento = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$agendamento) {
    $_SESSION['mensagem'] = "Agendamento não encontrado";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../../visualizar_pet.php?id=".$agendamento['pet_id']);
    exit();
}

// Consulta observações visuais cadastradas
$observacoesVisuais = $pdo->query("SELECT * FROM observacoes_visuais")->fetchAll(PDO::FETCH_ASSOC);

// Consulta observações visuais já marcadas para esta ficha (se existir)
$observacoesMarcadas = [];
if ($agendamento['ficha_id']) {
    $stmtObs = $pdo->prepare("SELECT observacao_id, outros_detalhes FROM ficha_observacoes WHERE ficha_id = ?");
    $stmtObs->execute([$agendamento['ficha_id']]);
    $observacoesMarcadas = $stmtObs->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Consulta serviços realizados para esta ficha (se existir)
$servicosRealizados = [];
if ($agendamento['ficha_id']) {
    $stmtServ = $pdo->prepare("SELECT servico_id, outros_detalhes FROM ficha_servicos_realizados WHERE ficha_id = ?");
    $stmtServ->execute([$agendamento['ficha_id']]);
    $servicosRealizados = $stmtServ->fetchAll(PDO::FETCH_KEY_PAIR);
}

// Buscar todos os serviços solicitados neste agendamento (grupo pelo data_hora e pet_id)
$stmtServicosSolicitados = $pdo->prepare("
    SELECT s.id, s.nome
    FROM agendamentos a
    JOIN servicos s ON a.servico_id = s.id
    WHERE a.data_hora = :data_hora AND a.pet_id = :pet_id
    GROUP BY s.id, s.nome
");
$stmtServicosSolicitados->execute([
    ':data_hora' => $agendamento['data_hora'],
    ':pet_id' => $agendamento['pet_id']
]);
$servicosSolicitados = $stmtServicosSolicitados->fetchAll(PDO::FETCH_ASSOC);

// Processar o formulário da ficha se for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $altura_pelos = $_POST['altura_pelos'];
        $doenca_pre_existente = $_POST['doenca_pre_existente'];
        $doenca_ouvido = $_POST['doenca_ouvido'];
        $doenca_pele = $_POST['doenca_pele'];
        $observacoes = $_POST['observacoes'];
        $status = 'Finalizado';

        // Verifica se já existe ficha para este agendamento
        if ($agendamento['ficha_id']) {
            $fichaId = $agendamento['ficha_id'];
            // Atualiza ficha existente
            $queryUpdate = "UPDATE fichas_petshop SET 
                            altura_pelos = :altura_pelos,
                            doenca_pre_existente = :doenca_pre_existente,
                            doenca_ouvido = :doenca_ouvido,
                            doenca_pele = :doenca_pele,
                            observacoes = :observacoes
                            WHERE id = :id";
            
            $stmtUpdate = $pdo->prepare($queryUpdate);
            $stmtUpdate->bindValue(':altura_pelos', $altura_pelos);
            $stmtUpdate->bindValue(':doenca_pre_existente', $doenca_pre_existente);
            $stmtUpdate->bindValue(':doenca_ouvido', $doenca_ouvido);
            $stmtUpdate->bindValue(':doenca_pele', $doenca_pele);
            $stmtUpdate->bindValue(':observacoes', $observacoes);
            $stmtUpdate->bindValue(':id', $fichaId);
            $stmtUpdate->execute();
            
            // Remove observações e serviços antigos
            $pdo->prepare("DELETE FROM ficha_observacoes WHERE ficha_id = ?")->execute([$fichaId]);
            $pdo->prepare("DELETE FROM ficha_servicos_realizados WHERE ficha_id = ?")->execute([$fichaId]);
        } else {
            // Cria nova ficha
            $queryInsert = "INSERT INTO fichas_petshop 
                           (agendamento_id, funcionario_id, altura_pelos, doenca_pre_existente, doenca_ouvido, doenca_pele, observacoes)
                           VALUES (:agendamento_id, :funcionario_id, :altura_pelos, :doenca_pre_existente, :doenca_ouvido, :doenca_pele, :observacoes)";
            
            $stmtInsert = $pdo->prepare($queryInsert);
            $stmtInsert->bindValue(':agendamento_id', $agendamentoId);
            $stmtInsert->bindValue(':funcionario_id', $usuarioId);
            $stmtInsert->bindValue(':altura_pelos', $altura_pelos);
            $stmtInsert->bindValue(':doenca_pre_existente', $doenca_pre_existente);
            $stmtInsert->bindValue(':doenca_ouvido', $doenca_ouvido);
            $stmtInsert->bindValue(':doenca_pele', $doenca_pele);
            $stmtInsert->bindValue(':observacoes', $observacoes);
            $stmtInsert->execute();
            
            $fichaId = $pdo->lastInsertId();
        }

        // Insere observações visuais
        if (isset($_POST['observacao_visual'])) {
            $stmtObs = $pdo->prepare("INSERT INTO ficha_observacoes (ficha_id, observacao_id, outros_detalhes) VALUES (?, ?, ?)");
            foreach ($_POST['observacao_visual'] as $obsId => $checked) {
                $outrosDetalhes = ($obsId == 7 && !empty($_POST['observacao_visual_outros'])) ? $_POST['observacao_visual_outros'] : null;
                $stmtObs->execute([$fichaId, $obsId, $outrosDetalhes]);
            }
        }

        // Apaga serviços antigos da ficha (se existir)
        $pdo->prepare("DELETE FROM ficha_servicos_realizados WHERE ficha_id = ?")->execute([$fichaId]);

        // Insere todos os serviços solicitados no agendamento, pois todos foram realizados
        $stmtServ = $pdo->prepare("INSERT INTO ficha_servicos_realizados (ficha_id, servico_id, outros_detalhes) VALUES (?, ?, NULL)");
        foreach ($servicosSolicitados as $servico) {
            $stmtServ->execute([$fichaId, $servico['id']]);
        }

        // Se tiver "Outros" preenchido, insere também
        if (!empty($_POST['servicos_realizados_outros'])) {
            $outrosDetalhes = trim($_POST['servicos_realizados_outros']);
            if ($outrosDetalhes !== '') {
                $stmtOutros = $pdo->prepare("INSERT INTO ficha_servicos_realizados (ficha_id, servico_id, outros_detalhes) VALUES (?, ?, ?)");
                $stmtOutros->execute([$fichaId, 99, $outrosDetalhes]);
            }
        }

        // Atualiza status do agendamento
        $pdo->prepare("UPDATE agendamentos SET status = ? WHERE id = ?")->execute([$status, $agendamentoId]);

        // Atualiza status de TODOS os agendamentos desse atendimento (mesmo pet, mesma data/hora)
        $pdo->prepare("UPDATE agendamentos SET status = ? WHERE pet_id = ? AND data_hora = ?")->execute([$status, $agendamento['pet_id'], $agendamento['data_hora']]);

        $pdo->commit();

        $_SESSION['mensagem'] = "Ficha do petshop atualizada com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
        header("Location: ../visualizar_pet.php?id=" . $agendamento['pet_id']);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mensagem'] = "Erro ao atualizar ficha: " . $e->getMessage();
        $_SESSION['tipo_mensagem'] = "error";
    }
}

// Função para formatar telefone
function formatarTelefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    if (strlen($telefone) === 11) {
        return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $telefone);
    } elseif (strlen($telefone) === 10) {
        return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $telefone);
    }
    return $telefone;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha do Petshop - HVTPETSHOP</title>
    <link rel="icon" type="image/x-icon" href="../../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <!-- Navbar padrão -->
    <nav class="w-full bg-white/90 shadow flex items-center justify-between px-6 py-3">
        <div class="flex items-center gap-3">
            <img src="../../icons/pet.jpg" alt="Logo Petshop" class="w-10 h-10 rounded-full shadow">
            <span class="text-2xl font-bold text-blue-700 tracking-tight">HVTPETSHOP</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="../../dashboard.php" class="text-blue-600 hover:text-blue-800 font-semibold transition"><i class="fa fa-home mr-1"></i>Dashboard</a>
            <a href="../cadastrar_pet.php" class="text-green-600 hover:text-green-800 font-semibold transition"><i class="fa fa-plus mr-1"></i>Novo Pet</a>
            <a href="../../tutores/listar_tutores.php" class="text-blue-500 hover:text-blue-700 font-semibold transition"><i class="fa fa-users mr-1"></i>Tutores</a>
            <a href="../../auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition"><i class="fa fa-sign-out-alt mr-1"></i>Sair</a>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-3xl mx-auto mt-10 p-4">
        <!-- Mensagens de feedback -->
        <?php if (isset($_SESSION['mensagem'])): ?>
            <div class="mb-6 p-4 rounded-lg <?= $_SESSION['tipo_mensagem'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <div class="flex items-center">
                    <i class="fas <?= $_SESSION['tipo_mensagem'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-3"></i>
                    <span><?= htmlspecialchars($_SESSION['mensagem']) ?></span>
                </div>
                <?php unset($_SESSION['mensagem']); unset($_SESSION['tipo_mensagem']); ?>
            </div>
        <?php endif; ?>

        <div class="bg-white/90 p-8 rounded-2xl shadow-xl animate-fade-in">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-blue-700 mb-1 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-file-medical"></i> Ficha do Salão de Beleza
                </h1>
                <div class="text-gray-500 text-sm">Hospital Veterinário Lourival Rodrigues</div>
            </div>

            <form method="POST" class="space-y-8">
                <!-- DADOS DO TUTOR E PET -->
                <div class="mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tutor -->
                        <div class="bg-blue-50/70 rounded-xl p-4 flex flex-col gap-2 border border-blue-100">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fa fa-user text-blue-400"></i>
                                <span class="font-semibold text-blue-700">Tutor</span>
                            </div>
                            <div class="flex flex-col gap-1">
                                <div>
                                    <span class="text-gray-500 text-xs">Nome</span>
                                    <div class="font-semibold text-gray-700"><?= htmlspecialchars($agendamento['tutor_nome']) ?></div>
                                </div>
                                <div>
                                    <span class="text-gray-500 text-xs">Telefone</span>
                                    <div class="font-semibold text-gray-700"><?= htmlspecialchars(formatarTelefone($agendamento['tutor_telefone'])) ?></div>
                                </div>
                            </div>
                        </div>
                        <!-- Pet -->
                        <div class="bg-blue-50/70 rounded-xl p-4 flex flex-col gap-2 border border-blue-100">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fa-solid fa-dog text-blue-400"></i>
                                <span class="font-semibold text-blue-700">Pet</span>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <span class="text-gray-500 text-xs">Nome</span>
                                    <div class="font-semibold text-gray-700"><?= htmlspecialchars($agendamento['pet_nome']) ?></div>
                                </div>
                                <div>
                                    <span class="text-gray-500 text-xs">Sexo</span>
                                    <div class="font-semibold text-gray-700"><?= htmlspecialchars($agendamento['sexo']) ?></div>
                                </div>
                                <div>
                                    <span class="text-gray-500 text-xs">Espécie</span>
                                    <div class="font-semibold text-gray-700"><?= htmlspecialchars($agendamento['especie']) ?></div>
                                </div>
                                <div>
                                    <span class="text-gray-500 text-xs">Raça</span>
                                    <div class="font-semibold text-gray-700"><?= htmlspecialchars($agendamento['raca']) ?></div>
                                </div>
                                <div>
                                    <span class="text-gray-500 text-xs">Idade</span>
                                    <div class="font-semibold text-gray-700"><?= htmlspecialchars($agendamento['idade']) ?></div>
                                </div>
                                <div>
                                    <span class="text-gray-500 text-xs">Peso</span>
                                    <div class="font-semibold text-gray-700"><?= htmlspecialchars($agendamento['peso']) ?> kg</div>
                                </div>
                                <div>
                                    <span class="text-gray-500 text-xs">Pelagem</span>
                                    <div class="font-semibold text-gray-700"><?= htmlspecialchars($agendamento['pelagem']) ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SEPARADOR -->
                <div class="flex items-center gap-3 my-6">
                    <div class="flex-1 h-px bg-blue-200"></div>
                    <span class="flex items-center gap-2 text-blue-400 font-bold text-sm uppercase tracking-widest">
                        <i class="fa-solid fa-eye"></i> Avaliação Visual
                    </span>
                    <div class="flex-1 h-px bg-blue-200"></div>
                </div>

                <!-- Observação Visual -->
                <div>
                    <div class="font-semibold text-blue-700 mb-2 flex items-center gap-2">
                        Observação Visual do Animal
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach ($observacoesVisuais as $obs): ?>
                            <label class="inline-flex items-center px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg shadow-sm cursor-pointer transition hover:bg-blue-100">
                                <input type="checkbox" name="observacao_visual[<?= $obs['id'] ?>]" value="1"
                                    <?= isset($observacoesMarcadas[$obs['id']]) ? 'checked' : '' ?>
                                    class="accent-blue-600 w-5 h-5 rounded border-gray-300 focus:ring-blue-500">
                                <span class="ml-2 text-gray-700"><?= htmlspecialchars($obs['descricao']) ?></span>
                                <?php if ($obs['id'] == 7): ?>
                                    <input type="text" name="observacao_visual_outros"
                                        value="<?= isset($observacoesMarcadas[7]) ? htmlspecialchars($observacoesMarcadas[7]) : '' ?>"
                                        class="ml-2 border rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-400"
                                        placeholder="Outros detalhes">
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- SEPARADOR -->
                <div class="flex items-center gap-3 my-6">
                    <div class="flex-1 h-px bg-blue-200"></div>
                    <span class="flex items-center gap-2 text-blue-400 font-bold text-sm uppercase tracking-widest">
                        <i class="fa-solid fa-scissors"></i> Serviços Realizados
                    </span>
                    <div class="flex-1 h-px bg-blue-200"></div>
                </div>

                <!-- Serviços Realizados -->
                <div>
                    <div class="font-semibold text-blue-700 mb-2 flex items-center gap-2">
                        Serviços Solicitados
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <?php foreach ($servicosSolicitados as $servico): ?>
                            <!-- Envia automaticamente -->
                            <input type="hidden" name="servicos_solicitados[]" value="<?= $servico['id'] ?>">
                            <!-- Mostra visualmente ao usuário -->
                            <span class="inline-flex items-center px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg shadow-sm text-gray-700">
                                <i class="fa fa-check text-green-600 mr-2"></i> <?= htmlspecialchars($servico['nome']) ?>
                            </span>
                        <?php endforeach; ?>
                        
                        <!-- Campo para "Outros" -->
                        <label class="inline-flex items-center px-3 py-2 bg-blue-50 border border-blue-200 rounded-lg shadow-sm cursor-pointer transition hover:bg-blue-100">
                            <span class="text-gray-700 mr-2">Outros:</span>
                            <input type="text" name="servicos_realizados_outros"
                                   value="<?= isset($servicosRealizados[99]) ? htmlspecialchars($servicosRealizados[99]) : '' ?>"
                                   class="ml-2 border rounded px-2 py-1 text-sm focus:ring-2 focus:ring-blue-400"
                                   placeholder="Outros detalhes (opcional)">
                        </label>
                    </div>
                </div>

                <!-- SEPARADOR -->
                <div class="flex items-center gap-3 my-6">
                    <div class="flex-1 h-px bg-blue-200"></div>
                    <span class="flex items-center gap-2 text-blue-400 font-bold text-sm uppercase tracking-widest">
                        <i class="fa-solid fa-heart-pulse"></i> Saúde e Observações
                    </span>
                    <div class="flex-1 h-px bg-blue-200"></div>
                </div>

                <!-- Doenças e Observações -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm mb-1">Doença Pré-Existente</label>
                        <input type="text" name="doenca_pre_existente" value="<?= htmlspecialchars($agendamento['doenca_pre_existente']) ?>" class="border rounded px-2 py-1 w-full">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm mb-1">Doença Canal Auditivo/Otite</label>
                        <input type="text" name="doenca_ouvido" value="<?= htmlspecialchars($agendamento['doenca_ouvido']) ?>" class="border rounded px-2 py-1 w-full">
                    </div>
                    <div>
                        <label class="block text-gray-700 text-sm mb-1">Doença de Pele</label>
                        <input type="text" name="doenca_pele" value="<?= htmlspecialchars($agendamento['doenca_pele']) ?>" class="border rounded px-2 py-1 w-full">
                    </div>
                </div>
                <div class="mt-4">
                    <label class="block text-gray-700 text-sm mb-1">Observações Adicionais</label>
                    <textarea name="observacoes" class="border rounded px-2 py-1 w-full" rows="3"><?= htmlspecialchars($agendamento['observacoes']) ?></textarea>
                </div>

                <!-- SEPARADOR -->
                <div class="flex items-center gap-3 my-6">
                    <div class="flex-1 h-px bg-blue-200"></div>
                    <span class="flex items-center gap-2 text-blue-400 font-bold text-sm uppercase tracking-widest">
                        <i class="fa-solid fa-ruler-vertical"></i> Cumprimento/altura dos pelos
                    </span>
                    <div class="flex-1 h-px bg-blue-200"></div>
                </div>

                <!-- Cumprimento/altura dos pelos -->
                <div>
                    <div class="font-semibold text-blue-700 mb-2 flex items-center gap-2">
                        <i class="fa-solid fa-ruler-vertical"></i> Cumprimento/altura dos pelos
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <input type="text" name="altura_pelos" value="<?= htmlspecialchars($agendamento['altura_pelos']) ?>"
                            class="border rounded px-3 py-2 w-full md:w-1/2 focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm"
                            placeholder="Ex: Curto, Médio, Longo ou valor em cm">
                    </div>
                </div>

                <div class="mt-8 flex justify-center">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white py-2 px-6 rounded-lg flex items-center gap-2 font-semibold shadow">
                        <i class="fas fa-save"></i> Salvar Ficha
                    </button>
                </div>
            </form>

            <div class="mt-8 text-center text-xs text-gray-400">
                Av. Dr. Edilberto Frota, 1103 - Fatima II – Crateús/CE – CEP: 63702-030<br>
                Celular/WhatsApp: (88) 9.9673-1101
            </div>
        </div>
    </main>

    <footer class="w-full py-3 bg-white/80 text-center text-gray-400 text-xs mt-8">
        &copy; 2025 HVTPETSHOP. Todos os direitos reservados.
    </footer>

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

<?php
// Adiciona o serviço "Outros" com ID 99 se não existir
$pdo->query("INSERT IGNORE INTO servicos (id, nome) VALUES (99, 'Outros')");
?>