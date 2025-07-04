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
    header("Location: ../visualizar_pet.php?id=".$ficha['pet_id']);
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
    <title>Ficha do Petshop - HVTPETSHOP</title>
    <link rel="icon" type="image/x-icon" href="../../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
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

        <div class="flex justify-end mb-4 gap-2">
            <button id="btnGerarPDF" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 font-semibold shadow">
                <i class="fas fa-file-pdf"></i> Gerar PDF
            </button>
            <a href="../visualizar_pet.php?id=<?= $ficha['pet_id'] ?>" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center gap-2 font-semibold shadow">
                <i class="fas fa-arrow-left"></i> Voltar
            </a>
        </div>

        <div id="fichaContent" class="bg-white/90 p-8 rounded-2xl shadow-xl animate-fade-in">
            <div class="mb-8 text-center">
                <h1 class="text-2xl font-bold text-blue-700 mb-1 flex items-center justify-center gap-2">
                    <i class="fa-solid fa-file-medical"></i> Ficha do Salão de Beleza
                </h1>
                <div class="text-gray-500 text-sm">Hospital Veterinário Lourival Rodrigues</div>
            </div>

            <!-- DADOS DO TUTOR E PET -->
            <div class="mb-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tutor -->
                    <div class="bg-blue-50/70 rounded-xl p-4 flex flex-col gap-2 border border-blue-100">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fa fa-user text-blue-400"></i>
                            <span class="font-semibold text-blue-700">Tutor</span>
                            <span class="ml-2 text-xs text-gray-500">ID: <?= htmlspecialchars($ficha['tutor_id']) ?></span>
                        </div>
                        <div class="flex flex-col gap-1">
                            <div>
                                <span class="text-gray-500 text-xs">Nome</span>
                                <div class="font-semibold text-gray-700"><?= htmlspecialchars($ficha['tutor_nome']) ?></div>
                            </div>
                            <div>
                                <span class="text-gray-500 text-xs">Telefone</span>
                                <div class="font-semibold text-gray-700"><?= htmlspecialchars(formatarTelefone($ficha['telefone'])) ?></div>
                            </div>
                            <div>
                                <span class="text-gray-500 text-xs">E-mail</span>
                                <div class="font-semibold text-gray-700"><?= htmlspecialchars($ficha['email']) ?></div>
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
                                <div class="font-semibold text-gray-700"><?= htmlspecialchars($ficha['pet_nome']) ?></div>
                            </div>
                            <div>
                                <span class="text-gray-500 text-xs">Sexo</span>
                                <div class="font-semibold text-gray-700"><?= htmlspecialchars($ficha['sexo']) ?></div>
                            </div>
                            <div>
                                <span class="text-gray-500 text-xs">Espécie</span>
                                <div class="font-semibold text-gray-700"><?= htmlspecialchars($ficha['especie']) ?></div>
                            </div>
                            <div>
                                <span class="text-gray-500 text-xs">Raça</span>
                                <div class="font-semibold text-gray-700"><?= htmlspecialchars($ficha['raca']) ?></div>
                            </div>
                            <div>
                                <span class="text-gray-500 text-xs">Idade</span>
                                <div class="font-semibold text-gray-700"><?= htmlspecialchars($ficha['idade']) ?></div>
                            </div>
                            <div>
                                <span class="text-gray-500 text-xs">Peso</span>
                                <div class="font-semibold text-gray-700"><?= htmlspecialchars($ficha['peso']) ?> kg</div>
                            </div>
                            <div>
                                <span class="text-gray-500 text-xs">Pelagem</span>
                                <div class="font-semibold text-gray-700"><?= htmlspecialchars($ficha['pelagem']) ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- SEPARADOR -->
            <div class="flex items-center gap-3 my-6">
                <div class="flex-1 h-px bg-blue-200"></div>
                <span class="text-blue-400 font-bold text-sm uppercase tracking-widest">Atendimento</span>
                <div class="flex-1 h-px bg-blue-200"></div>
            </div>

            <!-- INFORMAÇÕES DO ATENDIMENTO -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <span class="text-gray-500 text-xs">Data/Hora</span>
                    <div class="font-semibold text-gray-700"><?= formatarData($ficha['data_hora']) ?></div>
                </div>
                <div>
                    <span class="text-gray-500 text-xs">Status</span>
                    <div>
                        <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold text-white
                            <?= $ficha['status'] == 'Pendente' ? 'bg-yellow-500' : 
                               ($ficha['status'] == 'Em Atendimento' ? 'bg-blue-500' : 
                               ($ficha['status'] == 'Finalizado' ? 'bg-green-500' : 
                               ($ficha['status'] == 'Cancelado' ? 'bg-red-500' : 'bg-gray-500'))); ?>">
                            <?= htmlspecialchars($ficha['status']) ?>
                        </span>
                    </div>
                </div>
                <div>
                    <span class="text-gray-500 text-xs">Serviços</span>
                    <div class="font-semibold text-gray-700">
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
                    </div>
                </div>
                <div>
                    <span class="text-gray-500 text-xs">Atendido por</span>
                    <div class="font-semibold text-gray-700"><?= htmlspecialchars($ficha['funcionario_nome'] ?? 'N/A') ?></div>
                </div>
                <div>
                    <span class="text-gray-500 text-xs">Data do atendimento</span>
                    <div class="font-semibold text-gray-700"><?= formatarData($ficha['data_preenchimento'] ?? '') ?></div>
                </div>
            </div>

            <!-- SEPARADOR -->
            <div class="flex items-center gap-3 my-6">
                <div class="flex-1 h-px bg-blue-200"></div>
                <span class="text-blue-400 font-bold text-sm uppercase tracking-widest">Observações Visuais</span>
                <div class="flex-1 h-px bg-blue-200"></div>
            </div>

            <!-- OBSERVAÇÕES VISUAIS -->
            <div class="mb-6">
                <?php if (!empty($observacoes)): ?>
                    <ul class="list-disc pl-6">
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
                    <p class="text-gray-500">Nenhuma observação visual registrada.</p>
                <?php endif; ?>
            </div>

            <!-- SEPARADOR -->
            <div class="flex items-center gap-3 my-6">
                <div class="flex-1 h-px bg-blue-200"></div>
                <span class="text-blue-400 font-bold text-sm uppercase tracking-widest">Serviços Realizados</span>
                <div class="flex-1 h-px bg-blue-200"></div>
            </div>

            <!-- SERVIÇOS REALIZADOS agrupados -->
            <div class="mb-6">
                <?php if (!empty($servicos)): ?>
                    <ul class="list-disc pl-6">
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
                <?php else: ?>
                    <p class="text-gray-500">Nenhum serviço registrado.</p>
                <?php endif; ?>
                <?php if (!empty($ficha['altura_pelos'])): ?>
                    <p class="mt-2"><strong>Cumprimento/altura dos pelos:</strong> <?= htmlspecialchars($ficha['altura_pelos']) ?></p>
                <?php endif; ?>
            </div>

            <!-- SEPARADOR -->
            <div class="flex items-center gap-3 my-6">
                <div class="flex-1 h-px bg-blue-200"></div>
                <span class="text-blue-400 font-bold text-sm uppercase tracking-widest">Saúde e Observações</span>
                <div class="flex-1 h-px bg-blue-200"></div>
            </div>

            <!-- INFORMAÇÕES DE SAÚDE -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <span class="text-gray-500 text-xs">Doença Pré-Existente</span>
                    <div class="font-semibold text-gray-700"><?= !empty($ficha['doenca_pre_existente']) ? htmlspecialchars($ficha['doenca_pre_existente']) : 'Nenhuma' ?></div>
                </div>
                <div>
                    <span class="text-gray-500 text-xs">Doença Canal Auditivo/Otite</span>
                    <div class="font-semibold text-gray-700"><?= !empty($ficha['doenca_ouvido']) ? htmlspecialchars($ficha['doenca_ouvido']) : 'Nenhuma' ?></div>
                </div>
                <div>
                    <span class="text-gray-500 text-xs">Doença de Pele</span>
                    <div class="font-semibold text-gray-700"><?= !empty($ficha['doenca_pele']) ? htmlspecialchars($ficha['doenca_pele']) : 'Nenhuma' ?></div>
                </div>
            </div>
            <div class="mt-4">
                <span class="text-gray-500 text-xs">Observações Adicionais</span>
                <div class="font-semibold text-gray-700"><?= !empty($ficha['observacoes']) ? nl2br(htmlspecialchars($ficha['observacoes'])) : 'Nenhuma' ?></div>
            </div>

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

    <script>
    document.getElementById('btnGerarPDF').addEventListener('click', function() {
        const { jsPDF } = window.jspdf;
        const element = document.getElementById('fichaContent');
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Gerando PDF...';
        this.disabled = true;

        html2canvas(element, {
            scale: 2,
            logging: false,
            useCORS: true
        }).then(canvas => {
            const imgData = canvas.toDataURL('image/png');
            const pdf = new jsPDF('p', 'mm', 'a4');
            const pdfWidth = pdf.internal.pageSize.getWidth();
            const imgProps = pdf.getImageProperties(imgData);
            const pdfHeight = (imgProps.height * pdfWidth) / imgProps.width;

            let position = 10;
            pdf.setFontSize(16);
            pdf.text('Ficha do Petshop - HVTPETSHOP', pdfWidth / 2, position, { align: 'center' });
            position += 6;

            pdf.addImage(imgData, 'PNG', 0, position, pdfWidth, pdfHeight);
            pdf.save('ficha_petshop_<?= $ficha['pet_nome'] ?>_<?= date('Ymd') ?>.pdf');
            this.innerHTML = originalText;
            this.disabled = false;
        }).catch(err => {
            alert('Erro ao gerar PDF: ' + err.message);
            this.innerHTML = originalText;
            this.disabled = false;
        });
    });
    </script>
</body>
</html>