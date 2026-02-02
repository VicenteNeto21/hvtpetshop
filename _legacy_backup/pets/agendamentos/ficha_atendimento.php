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
                 f.id as ficha_id, f.altura_pelos, f.doenca_pre_existente, f.doenca_ouvido, f.doenca_pele, f.observacoes,
                 f.comportamento_pet, f.recomendacoes_tutor
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

// Buscar TODOS os serviços para a seleção
$todosServicos = $pdo->query("SELECT id, nome FROM servicos WHERE id != 99 ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);


// Processar o formulário da ficha se for enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $altura_pelos = $_POST['altura_pelos'];
        $doenca_pre_existente = $_POST['doenca_pre_existente'];
        $doenca_ouvido = $_POST['doenca_ouvido'];
        $doenca_pele = $_POST['doenca_pele'];
        $observacoes = $_POST['observacoes'];
        $comportamento_pet = $_POST['comportamento_pet'];
        $recomendacoes_tutor = $_POST['recomendacoes_tutor'];
        $nova_data = $_POST['data'];
        $novo_horario = $_POST['horario'];
        $nova_data_hora = $nova_data . ' ' . $novo_horario;
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
                            observacoes = :observacoes,
                            comportamento_pet = :comportamento_pet,
                            recomendacoes_tutor = :recomendacoes_tutor
                            WHERE id = :id";
            
            $stmtUpdate = $pdo->prepare($queryUpdate);
            $stmtUpdate->bindValue(':altura_pelos', $altura_pelos);
            $stmtUpdate->bindValue(':doenca_pre_existente', $doenca_pre_existente);
            $stmtUpdate->bindValue(':doenca_ouvido', $doenca_ouvido);
            $stmtUpdate->bindValue(':doenca_pele', $doenca_pele);
            $stmtUpdate->bindValue(':observacoes', $observacoes);
            $stmtUpdate->bindValue(':comportamento_pet', $comportamento_pet);
            $stmtUpdate->bindValue(':recomendacoes_tutor', $recomendacoes_tutor);
            $stmtUpdate->bindValue(':id', $fichaId);
            $stmtUpdate->execute();
            
            // Remove observações e serviços antigos
            $pdo->prepare("DELETE FROM ficha_observacoes WHERE ficha_id = ?")->execute([$fichaId]);
            $pdo->prepare("DELETE FROM ficha_servicos_realizados WHERE ficha_id = ?")->execute([$fichaId]);
        } else {
            // Cria nova ficha
            $queryInsert = "INSERT INTO fichas_petshop 
                           (agendamento_id, funcionario_id, altura_pelos, doenca_pre_existente, doenca_ouvido, doenca_pele, observacoes, comportamento_pet, recomendacoes_tutor)
                           VALUES (:agendamento_id, :funcionario_id, :altura_pelos, :doenca_pre_existente, :doenca_ouvido, :doenca_pele, :observacoes, :comportamento_pet, :recomendacoes_tutor)";
            
            $stmtInsert = $pdo->prepare($queryInsert);
            $stmtInsert->bindValue(':agendamento_id', $agendamentoId);
            $stmtInsert->bindValue(':funcionario_id', $usuarioId);
            $stmtInsert->bindValue(':altura_pelos', $altura_pelos);
            $stmtInsert->bindValue(':doenca_pre_existente', $doenca_pre_existente);
            $stmtInsert->bindValue(':doenca_ouvido', $doenca_ouvido);
            $stmtInsert->bindValue(':doenca_pele', $doenca_pele);
            $stmtInsert->bindValue(':observacoes', $observacoes);
            $stmtInsert->bindValue(':comportamento_pet', $comportamento_pet);
            $stmtInsert->bindValue(':recomendacoes_tutor', $recomendacoes_tutor);
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

        // Insere os serviços que foram efetivamente realizados (marcados no form)
        $stmtServ = $pdo->prepare("INSERT INTO ficha_servicos_realizados (ficha_id, servico_id) VALUES (?, ?)");
        if (isset($_POST['servicos_realizados'])) {
            foreach ($_POST['servicos_realizados'] as $servicoId) {
                $stmtServ->execute([$fichaId, $servicoId]);
            }
        }

        // Se o campo "Outros" foi preenchido, insere-o
        if (!empty($_POST['servicos_realizados_outros'])) {
            $outrosDetalhes = trim($_POST['servicos_realizados_outros']);
            if ($outrosDetalhes !== '') {
                $stmtOutros = $pdo->prepare("INSERT INTO ficha_servicos_realizados (ficha_id, servico_id, outros_detalhes) VALUES (?, ?, ?)");
                $stmtOutros->execute([$fichaId, 99, $outrosDetalhes]);
            }
        }

        // Atualiza a data e hora de TODOS os agendamentos desse atendimento se houver mudança
        if ($nova_data_hora !== $agendamento['data_hora']) {
            $stmtUpdateData = $pdo->prepare("UPDATE agendamentos SET data_hora = ? WHERE pet_id = ? AND data_hora = ?");
            $stmtUpdateData->execute([$nova_data_hora, $agendamento['pet_id'], $agendamento['data_hora']]);
            $agendamento['data_hora'] = $nova_data_hora; // Atualiza a variável para a próxima query
        }

        // Atualiza status de TODOS os agendamentos desse atendimento (mesmo pet, mesma data/hora)
        $stmtUpdateStatus = $pdo->prepare("
            UPDATE agendamentos 
            SET status = :status 
            WHERE pet_id = :pet_id AND data_hora = :data_hora
        ");
        $stmtUpdateStatus->bindValue(':status', $status);
        $stmtUpdateStatus->bindValue(':pet_id', $agendamento['pet_id']);
        $stmtUpdateStatus->bindValue(':data_hora', $agendamento['data_hora']);
        $stmtUpdateStatus->execute();

        $pdo->commit();

        $_SESSION['mensagem'] = "Ficha do petshop atualizada com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
        header("Location: ../../dashboard.php");
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
    <title>Ficha de Atendimento - CereniaPet</title>
    <link rel="icon" type="image/x-icon" href="../../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <?php
    $path_prefix = '../../';
    include '../../components/navbar.php';
    ?>
    <?php include '../../components/toast.php'; ?>

    <main class="flex-1 w-full p-4 md:p-6 lg:p-8">
        <!-- Cabeçalho -->
        <div class="mb-8 animate-fade-in">
            <h1 class="text-3xl font-bold text-slate-800">Ficha de Atendimento</h1>
            <p class="text-slate-500 mt-1">Preencha os detalhes do atendimento do pet.</p>
        </div>

        <div class="bg-white p-6 md:p-8 rounded-lg shadow-sm animate-fade-in">

            <form method="POST" class="space-y-8">
                <!-- DADOS DO TUTOR E PET -->
                <div class="border-b border-slate-200 pb-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <!-- Tutor -->
                        <div class="flex items-start gap-4">
                            <div class="bg-amber-100 text-amber-500 w-12 h-12 rounded-full flex items-center justify-center text-xl flex-shrink-0">
                                <i class="fa-solid fa-user"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800"><?= htmlspecialchars($agendamento['tutor_nome']) ?></h3>
                                <p class="text-sm text-slate-500">Tutor</p>
                                <p class="text-sm text-slate-600 mt-1"><?= htmlspecialchars(formatarTelefone($agendamento['tutor_telefone'])) ?></p>
                            </div>
                        </div>
                        <!-- Pet -->
                        <div class="flex items-start gap-4">
                            <div class="bg-violet-100 text-violet-500 w-12 h-12 rounded-full flex items-center justify-center text-xl flex-shrink-0">
                                <i class="fa-solid fa-dog"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800"><?= htmlspecialchars($agendamento['pet_nome']) ?></h3>
                                <p class="text-sm text-slate-500">Pet</p>
                                <div class="text-sm text-slate-600 mt-1">
                                    <span><?= htmlspecialchars($agendamento['especie']) ?></span>,
                                    <span><?= htmlspecialchars($agendamento['raca']) ?></span>,
                                    <span><?= htmlspecialchars($agendamento['sexo']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data e Hora do Agendamento -->
                <div>
                    <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mb-4 flex items-center gap-3">
                        <i class="fa-solid fa-calendar-alt text-sky-500"></i> Data e Hora do Agendamento
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="data" class="block text-sm font-medium text-slate-600 mb-1">Data</label>
                            <input type="date" name="data" id="data" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= date('Y-m-d', strtotime($agendamento['data_hora'])) ?>" required>
                        </div>
                        <div>
                            <label for="horario" class="block text-sm font-medium text-slate-600 mb-1">Horário</label>
                            <select name="horario" id="horario" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="<?= date('H:i', strtotime($agendamento['data_hora'])) ?>"><?= date('H:i', strtotime($agendamento['data_hora'])) ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Observação Visual -->
                <div>
                    <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mb-4 flex items-center gap-3">
                        <i class="fa-solid fa-eye text-sky-500"></i> Avaliação Visual
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <?php foreach ($observacoesVisuais as $obs): ?>
                            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="observacao_visual[<?= $obs['id'] ?>]" value="1"
                                    <?= isset($observacoesMarcadas[$obs['id']]) ? 'checked' : '' ?>
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-slate-700"><?= htmlspecialchars($obs['descricao']) ?></span>
                                <?php if ($obs['id'] == 7): ?>
                                    <input type="text" name="observacao_visual_outros"
                                        value="<?= isset($observacoesMarcadas[7]) ? htmlspecialchars($observacoesMarcadas[7]) : '' ?>"
                                        class="ml-2 w-full p-1 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                        placeholder="Outros detalhes">
                                <?php endif; ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Serviços Realizados -->
                <div>
                    <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mb-4 flex items-center gap-3">
                        <i class="fa-solid fa-scissors text-green-500"></i> Serviços Realizados
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-4">
                        <?php foreach ($todosServicos as $servico): ?>
                            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="servicos_realizados[]" value="<?= $servico['id'] ?>"
                                       <?php
                                            // Verifica se o serviço foi solicitado OU se já foi marcado na ficha
                                            $isSolicitado = in_array($servico['id'], array_column($servicosSolicitados, 'id'));
                                            $isRealizado = isset($servicosRealizados[$servico['id']]);
                                            if ($isSolicitado || $isRealizado) echo 'checked';
                                       ?>
                                       class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-slate-700"><?= htmlspecialchars($servico['nome']) ?></span>
                            </label>
                        <?php endforeach; ?>
                        
                        <!-- Campo para "Outros" -->
                        <div class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg">
                            <span class="text-slate-700 font-medium">Outros:</span>
                            <input type="text" name="servicos_realizados_outros"
                                   value="<?= htmlspecialchars($servicosRealizados[99] ?? '') ?>"
                                   class="w-full p-1 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                                   placeholder="Serviço extra (opcional)">
                        </div>
                    </div>
                </div>

                <!-- Doenças e Observações -->
                <div>
                    <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mb-4 flex items-center gap-3">
                        <i class="fa-solid fa-heart-pulse text-red-500"></i> Saúde e Observações
                    </h3>
                    <div class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1">Doença Pré-Existente</label>
                                <input type="text" name="doenca_pre_existente" value="<?= htmlspecialchars($agendamento['doenca_pre_existente'] ?? '') ?>" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: Cardiopata, diabético">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1">Doença Canal Auditivo/Otite</label>
                                <input type="text" name="doenca_ouvido" value="<?= htmlspecialchars($agendamento['doenca_ouvido'] ?? '') ?>" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: Otite crônica">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-1">Doença de Pele</label>
                                <input type="text" name="doenca_pele" value="<?= htmlspecialchars($agendamento['doenca_pele'] ?? '') ?>" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Ex: Dermatite atópica">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Observações Adicionais</label>
                            <textarea name="observacoes" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Qualquer outra informação relevante sobre a saúde do pet."><?= htmlspecialchars($agendamento['observacoes'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- Cumprimento/altura dos pelos -->
                <div>
                    <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mb-4 flex items-center gap-3">
                        <i class="fa-solid fa-ruler-vertical text-amber-500"></i> Detalhes da Tosa
                    </h3>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Altura dos Pelos</label>
                        <input type="text" name="altura_pelos" value="<?= htmlspecialchars($agendamento['altura_pelos'] ?? '') ?>"
                            class="w-full md:w-1/2 p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Ex: Curto, Médio, Longo ou valor em cm">
                    </div>
                </div>

                <!-- Comportamento e Recomendações -->
                <div>
                    <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mb-4 flex items-center gap-3">
                        <i class="fa-solid fa-comment-dots text-teal-500"></i> Comentários Finais
                    </h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Comportamento do Pet</label>
                            <textarea name="comportamento_pet" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Como o pet se comportou durante o atendimento? (Ex: Calmo, agitado, agressivo com secador)"><?= htmlspecialchars($agendamento['comportamento_pet'] ?? '') ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-1">Recomendações para o Tutor</label>
                            <textarea name="recomendacoes_tutor" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" rows="3" placeholder="Alguma recomendação para o tutor? (Ex: Escovar o pelo diariamente, retornar em 30 dias)"><?= htmlspecialchars($agendamento['recomendacoes_tutor'] ?? '') ?></textarea>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-8 mt-8 border-t border-slate-200">
                    <a href="../visualizar_pet.php?id=<?= $agendamento['pet_id'] ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        <i class="fa fa-arrow-left"></i> Voltar
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Salvar Ficha e Finalizar
                    </button>
                </div>
            </form>
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
        document.addEventListener('DOMContentLoaded', function() {
            const dataInput = document.getElementById('data');
            const horarioSelect = document.getElementById('horario');
            const agendamentoId = <?= $agendamentoId ?>;
            const horarioOriginal = '<?= date('H:i', strtotime($agendamento['data_hora'])) ?>';

            function buscarHorariosDisponiveis() {
                const data = dataInput.value;
                if (!data) {
                    horarioSelect.innerHTML = '<option value="">Selecione uma data</option>';
                    return;
                }

                horarioSelect.innerHTML = '<option value="">Carregando...</option>';
                horarioSelect.disabled = true;

                fetch(`buscar_horarios_disponiveis.php?data=${data}&agendamento_id=${agendamentoId}`)
                    .then(response => response.json())
                    .then(horarios => {
                        horarioSelect.innerHTML = '';
                        
                        // Adiciona o horário original como opção, caso não esteja na lista de disponíveis
                        if (!horarios.includes(horarioOriginal)) {
                             horarios.push(horarioOriginal);
                             horarios.sort();
                        }

                        horarios.forEach(horario => {
                            const selected = (horario === horarioOriginal) ? 'selected' : '';
                            horarioSelect.innerHTML += `<option value="${horario}" ${selected}>${horario}</option>`;
                        });
                        horarioSelect.disabled = false;
                    });
            }

            dataInput.addEventListener('change', buscarHorariosDisponiveis);
            buscarHorariosDisponiveis(); // Busca os horários ao carregar a página
        });
    </script>
</body>
</html>

<?php
// Adiciona o serviço "Outros" com ID 99 se não existir
$pdo->query("INSERT IGNORE INTO servicos (id, nome) VALUES (99, 'Outros')");
?>