<?php
include "../../config/config.php";
session_start();

// Proteção da página
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../../login.html");
    exit();
}

if (!isset($_GET['id'])) {
    header("Location: ../../dashboard.php");
    exit();
}

$agendamentoId = $_GET['id'];

// --- BUSCA DE DADOS ---

// Busca o agendamento principal para obter o grupo (pet_id, data_hora)
$stmtAgendamento = $pdo->prepare("SELECT pet_id, data_hora, status FROM agendamentos WHERE id = :id");
$stmtAgendamento->execute([':id' => $agendamentoId]);
$agendamentoGrupo = $stmtAgendamento->fetch(PDO::FETCH_ASSOC);

if (!$agendamentoGrupo) {
    $_SESSION['mensagem'] = "Agendamento não encontrado.";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../../dashboard.php");
    exit();
}

$petId = $agendamentoGrupo['pet_id'];
$dataHoraOriginal = $agendamentoGrupo['data_hora'];
$statusOriginal = $agendamentoGrupo['status'];

// Busca dados do pet e tutor
$stmtPet = $pdo->prepare("SELECT p.nome as pet_nome, t.nome as tutor_nome FROM pets p JOIN tutores t ON p.tutor_id = t.id WHERE p.id = :pet_id");
$stmtPet->execute([':pet_id' => $petId]);
$petInfo = $stmtPet->fetch(PDO::FETCH_ASSOC);

// Busca todos os serviços solicitados para este grupo de agendamento
$stmtServicos = $pdo->prepare("SELECT servico_id, observacoes FROM agendamentos WHERE pet_id = :pet_id AND data_hora = :data_hora");
$stmtServicos->execute([':pet_id' => $petId, ':data_hora' => $dataHoraOriginal]);
$servicosAtuais = $stmtServicos->fetchAll(PDO::FETCH_ASSOC);

$servicosAtuaisIds = array_column($servicosAtuais, 'servico_id');
$outrosDetalhesAtual = '';
foreach ($servicosAtuais as $serv) {
    if ($serv['servico_id'] == 99 && !empty($serv['observacoes'])) {
        // Extrai o detalhe do serviço "Outros" da observação geral
        if (preg_match('/Serviço Extra: (.*)/', $serv['observacoes'], $matches)) {
            $outrosDetalhesAtual = $matches[1];
        }
    }
}

// Busca todos os serviços disponíveis (exceto 'Outros')
$todosServicos = $pdo->query("SELECT id, nome FROM servicos WHERE id != 99 ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

// --- PROCESSAMENTO DO FORMULÁRIO ---

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo->beginTransaction();

        $novaData = $_POST['data'];
        $novoHorario = $_POST['horario'];
        $novaDataHora = $novaData . ' ' . $novoHorario;

        $servicosSelecionados = isset($_POST['servico_id']) ? (array)$_POST['servico_id'] : [];
        $servicoOutrosSelecionado = isset($_POST['servico_outros']);
        $servicoOutrosDetalhes = trim($_POST['servico_outros_detalhes'] ?? '');

        if ($servicoOutrosSelecionado) {
            $servicosSelecionados[] = 99; // Adiciona ID 'Outros'
        }

        if (empty($servicosSelecionados)) {
            throw new Exception("Selecione pelo menos um serviço.");
        }

        // 1. Serviços a serem adicionados
        $servicosParaAdicionar = array_diff($servicosSelecionados, $servicosAtuaisIds);

        // 2. Serviços a serem removidos
        $servicosParaRemover = array_diff($servicosAtuaisIds, $servicosSelecionados);

        // 3. Atualiza data/hora para todos os serviços que permanecerão, se a data/hora mudou
        if ($novaDataHora !== $dataHoraOriginal) {
            $servicosParaManter = array_intersect($servicosAtuaisIds, $servicosSelecionados);
            if (!empty($servicosParaManter)) {
                $placeholders = implode(',', array_fill(0, count($servicosParaManter), '?'));
                $stmtUpdate = $pdo->prepare("UPDATE agendamentos SET data_hora = ? WHERE pet_id = ? AND data_hora = ? AND servico_id IN ($placeholders)");
                $params = array_merge([$novaDataHora, $petId, $dataHoraOriginal], $servicosParaManter);
                $stmtUpdate->execute($params);
            }
        }

        // 4. Remove os serviços desmarcados
        if (!empty($servicosParaRemover)) {
            $placeholders = implode(',', array_fill(0, count($servicosParaRemover), '?'));
            // Cuidado: Exclui a ficha associada se o serviço for removido.
            $stmtDelete = $pdo->prepare("DELETE FROM agendamentos WHERE pet_id = ? AND data_hora = ? AND servico_id IN ($placeholders)");
            $params = array_merge([$petId, $dataHoraOriginal], $servicosParaRemover);
            $stmtDelete->execute($params);
        }

        // 5. Adiciona os novos serviços marcados
        if (!empty($servicosParaAdicionar)) {
            $stmtInsert = $pdo->prepare(
                "INSERT INTO agendamentos (pet_id, usuario_id, data_hora, servico_id, transporte, status, observacoes) 
                 VALUES (:pet_id, :usuario_id, :data_hora, :servico_id, 'Não', :status, :observacoes)"
            );
            foreach ($servicosParaAdicionar as $servicoId) {
                $obsFinal = null;
                if ($servicoId == 99 && !empty($servicoOutrosDetalhes)) {
                    $obsFinal = "Serviço Extra: " . $servicoOutrosDetalhes;
                }
                $stmtInsert->execute([
                    ':pet_id' => $petId,
                    ':usuario_id' => $_SESSION['usuario_id'],
                    ':data_hora' => $novaDataHora,
                    ':servico_id' => $servicoId,
                    ':status' => $statusOriginal,
                    ':observacoes' => $obsFinal
                ]);
            }
        }

        // 6. Atualiza o campo de observação do serviço "Outros" se ele já existia e foi modificado
        if (in_array(99, $servicosAtuaisIds) && in_array(99, $servicosSelecionados)) {
             $obsFinal = "Serviço Extra: " . $servicoOutrosDetalhes;
             $stmtUpdateOutros = $pdo->prepare("UPDATE agendamentos SET observacoes = ? WHERE pet_id = ? AND data_hora = ? AND servico_id = 99");
             $stmtUpdateOutros->execute([$obsFinal, $petId, $novaDataHora]);
        }

        $pdo->commit();

        $_SESSION['mensagem'] = "Agendamento atualizado com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
        header("Location: ../visualizar_pet.php?id=" . $petId);
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mensagem'] = "Erro ao atualizar agendamento: " . $e->getMessage();
        $_SESSION['tipo_mensagem'] = "error";
        header("Location: reiditar_agendamento.php?id=" . $agendamentoId);
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reeditar Agendamento - CereniaPet</title>
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
        <div class="mb-8 animate-fade-in">
            <h1 class="text-3xl font-bold text-slate-800">Reeditar Agendamento</h1>
            <p class="text-slate-500 mt-1">Altere os serviços, data ou horário do atendimento.</p>
        </div>

        <div class="bg-white p-6 md:p-8 rounded-lg shadow-sm animate-fade-in">
            <form method="POST" class="space-y-6">
                <!-- Informações do Pet e Tutor -->
                <div class="bg-slate-50 p-5 rounded-lg border border-slate-200 mb-6">
                    <h2 class="text-xl font-bold text-slate-800 mb-3 flex items-center gap-3">
                        <i class="fa-solid fa-dog text-violet-500"></i> Pet: <?= htmlspecialchars($petInfo['pet_nome']); ?>
                    </h2>
                    <p class="text-sm text-slate-600">Tutor: <span class="font-semibold"><?= htmlspecialchars($petInfo['tutor_nome']); ?></span></p>
                </div>

                <!-- Data e Hora -->
                <div>
                    <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mb-4 flex items-center gap-3">
                        <i class="fa-solid fa-calendar-alt text-sky-500"></i> Data e Hora <span class="text-red-500">*</span>
                    </h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label for="data" class="block text-sm font-medium text-slate-600 mb-1">Data</label>
                            <input type="date" name="data" id="data" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" value="<?= date('Y-m-d', strtotime($dataHoraOriginal)) ?>" min="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div>
                            <label for="horario" class="block text-sm font-medium text-slate-600 mb-1">Horário</label>
                            <select name="horario" id="horario" class="w-full p-2 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                                <option value="">Carregando...</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Seleção de Serviços -->
                <div>
                    <h3 class="text-lg font-semibold text-slate-700 border-b border-slate-200 pb-2 mb-4 flex items-center gap-3">
                        <i class="fa-solid fa-scissors text-green-500"></i> Seleção de Serviços <span class="text-red-500">*</span>
                    </h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                        <?php foreach ($todosServicos as $servico): ?>
                            <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 cursor-pointer">
                                <input type="checkbox" name="servico_id[]" value="<?= $servico['id'] ?>" 
                                    <?= in_array($servico['id'], $servicosAtuaisIds) ? 'checked' : '' ?>
                                    class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <span class="text-slate-700"><?= htmlspecialchars($servico['nome']) ?></span>
                            </label>
                        <?php endforeach; ?>
                        <!-- Campo "Outros" com input de texto -->
                        <div class="flex items-center gap-2 p-3 border border-slate-200 rounded-lg hover:bg-slate-50 col-span-2 sm:col-span-1">
                            <input type="checkbox" id="servico_outros_check" name="servico_outros" value="99" 
                                <?= in_array(99, $servicosAtuaisIds) ? 'checked' : '' ?>
                                class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            <label for="servico_outros_check" class="text-slate-700 cursor-pointer">Outros:</label>
                            <input type="text" name="servico_outros_detalhes" id="servico_outros_detalhes" 
                                   value="<?= htmlspecialchars($outrosDetalhesAtual) ?>"
                                   class="flex-1 p-1 border border-slate-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm disabled:bg-slate-100 disabled:cursor-not-allowed" 
                                   placeholder="Especifique o serviço" <?= !in_array(99, $servicosAtuaisIds) ? 'disabled' : '' ?>>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-4 pt-8 mt-8 border-t border-slate-200">
                    <a href="../visualizar_pet.php?id=<?= $petId ?>" class="bg-slate-200 hover:bg-slate-300 text-slate-700 py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        <i class="fa fa-arrow-left"></i> Cancelar
                    </a>
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                        <i class="fa fa-save"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>
    </main>

    <?php include $path_prefix . 'components/footer.php'; ?>
    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in { animation: fade-in 0.8s ease; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Lógica para habilitar/desabilitar campo "Outros"
            const outrosCheck = document.getElementById('servico_outros_check');
            const outrosDetalhesInput = document.getElementById('servico_outros_detalhes');

            outrosCheck.addEventListener('change', function() {
                outrosDetalhesInput.disabled = !this.checked;
                if (!this.checked) {
                    outrosDetalhesInput.value = '';
                }
            });

            // Lógica para buscar horários disponíveis
            const dataInput = document.getElementById('data');
            const horarioSelect = document.getElementById('horario');
            const agendamentoId = <?= $agendamentoId ?>;
            const horarioOriginal = '<?= date('H:i', strtotime($dataHoraOriginal)) ?>';

            function buscarHorariosDisponiveis() {
                const data = dataInput.value;
                if (!data) {
                    horarioSelect.innerHTML = '<option value="">Selecione uma data</option>';
                    return;
                }

                horarioSelect.innerHTML = '<option value="">Carregando...</option>';
                horarioSelect.disabled = true;

                // Passa o ID do agendamento para que o script de busca ignore o próprio horário do agendamento na verificação
                fetch(`buscar_horarios_disponiveis.php?data=${data}&agendamento_id=${agendamentoId}`)
                    .then(response => response.json())
                    .then(horarios => {
                        horarioSelect.innerHTML = '';
                        
                        // Garante que o horário original sempre apareça na lista
                        if (!horarios.includes(horarioOriginal)) {
                             horarios.push(horarioOriginal);
                             horarios.sort(); // Reordena
                        }

                        horarios.forEach(horario => {
                            const selected = (horario === horarioOriginal) ? 'selected' : '';
                            horarioSelect.innerHTML += `<option value="${horario}" ${selected}>${horario}</option>`;
                        });
                        horarioSelect.disabled = false;
                    });
            }

            dataInput.addEventListener('change', buscarHorariosDisponiveis);
            
            // Busca os horários ao carregar a página
            buscarHorariosDisponiveis(); 
        });
    </script>
</body>
</html>