<?php
// filepath: c:\xampp\htdocs\dashboard\hvt_petshop\vendas\cadastrar_venda.php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Busca pets para seleção
$pets = $pdo->query("
    SELECT p.id, p.nome, t.nome as tutor_nome, t.id as tutor_id
    FROM pets p
    LEFT JOIN tutores t ON p.tutor_id = t.id
    ORDER BY t.nome, p.nome
")->fetchAll(PDO::FETCH_ASSOC);

// Busca tutores para venda avulsa
$tutores = $pdo->query("SELECT id, nome FROM tutores ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);

$valor_total = 0;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_id = $_POST['pet_id'] ?? null;
    $tutor_id = $_POST['tutor_id'] ?? null;
    $data_hora = date('Y-m-d H:i:s');

    // Se veio pet, calcula o valor total dos serviços desse pet
    if ($pet_id) {
        $sql = "SELECT SUM(s.preco) as total
                FROM servico s
                WHERE s.pet_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$pet_id]);
        $valor_total = $stmt->fetchColumn() ?: 0;
    } else {
        $valor_total = str_replace(',', '.', preg_replace('/[^\d,\.]/', '', $_POST['valor_total'] ?? ''));
    }

    if (!$valor_total || !is_numeric($valor_total)) {
        $_SESSION['mensagem'] = "Informe um valor válido!";
        $_SESSION['tipo_mensagem'] = "error";
    } else {
        $stmt = $pdo->prepare("INSERT INTO vendas (tutor_id, pet_id, data_hora, valor_total) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $tutor_id ?: null,
            $pet_id ?: null,
            $data_hora,
            $valor_total
        ]);
        $_SESSION['mensagem'] = "Venda registrada com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
        header("Location: ../caixa.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Finalizar Venda - HVTPETSHOP</title>
    <link rel="icon" type="image/x-icon" href="../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Atualiza valor total e serviços automaticamente ao selecionar pet
        function atualizarValorTotal() {
            const select = document.getElementById('pet_id');
            const valorInput = document.getElementById('valor_total');
            const servicosDiv = document.getElementById('servicos_pet');
            const petId = select.value;

            servicosDiv.innerHTML = '';

            if (petId) {
                fetch('valor_servicos_pet.php?id=' + petId)
                    .then(resp => resp.json())
                    .then(data => {
                        valorInput.value = data.total;
                        valorInput.readOnly = true;
                    });

                fetch('servicos_pet.php?id=' + petId)
                    .then(resp => resp.json())
                    .then(servicos => {
                        if (servicos.length > 0) {
                            let html = '<div class="mt-2 mb-2 p-3 bg-blue-50 rounded shadow text-sm"><b>Serviços deste pet:</b><ul class="list-disc ml-5">';
                            servicos.forEach(s => {
                                html += `<li>${s.nome} <span class="text-green-700 font-semibold">R$ ${parseFloat(s.preco).toLocaleString('pt-BR', {minimumFractionDigits: 2})}</span></li>`;
                            });
                            html += '</ul></div>';
                            servicosDiv.innerHTML = html;
                        }
                    });
            } else {
                valorInput.value = '';
                valorInput.readOnly = false;
            }
        }
    </script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">
    <nav class="w-full bg-white/90 shadow flex items-center justify-between px-6 py-3">
        <div class="flex items-center gap-3">
            <img src="../icons/pet.jpg" alt="Logo Petshop" class="w-10 h-10 rounded-full shadow">
            <span class="text-2xl font-bold text-blue-700 tracking-tight">HVTPETSHOP</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="../dashboard.php" class="text-blue-600 hover:text-blue-800 font-semibold transition"><i class="fa fa-home mr-1"></i>Dashboard</a>
            <a href="../caixa.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg font-semibold shadow flex items-center gap-2 transition">
                <i class="fa fa-cash-register"></i> PDV
            </a>
            <a href="../tutores/listar_tutores.php" class="text-blue-500 hover:text-blue-700 font-semibold transition"><i class="fa fa-users mr-1"></i>Tutores</a>
            <a href="../auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition"><i class="fa fa-sign-out-alt mr-1"></i>Sair</a>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-md mx-auto mt-10 p-4">
        <div class="bg-white/90 p-8 rounded-2xl shadow-xl animate-fade-in">
            <h1 class="text-2xl font-bold text-blue-700 mb-6 flex items-center gap-2">
                <i class="fa fa-cash-register"></i> Finalizar Venda
            </h1>
            <?php if (isset($_SESSION['mensagem'])): ?>
                <div class="mb-4 p-3 rounded <?= $_SESSION['tipo_mensagem'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <?= htmlspecialchars($_SESSION['mensagem']) ?>
                    <?php unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']); ?>
                </div>
            <?php endif; ?>
            <form method="POST" class="space-y-6">
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Pet (opcional)</label>
                    <select name="pet_id" id="pet_id" class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm" onchange="atualizarValorTotal()">
                        <option value="">Venda avulsa (sem pet)</option>
                        <?php foreach ($pets as $pet): ?>
                            <option value="<?= $pet['id'] ?>">
                                <?= htmlspecialchars($pet['nome']) ?> (Tutor: <?= htmlspecialchars($pet['tutor_nome']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div id="servicos_pet"></div>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Cliente (opcional)</label>
                    <select name="tutor_id" class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm">
                        <option value="">Venda avulsa (sem cliente)</option>
                        <?php foreach ($tutores as $tutor): ?>
                            <option value="<?= $tutor['id'] ?>"><?= htmlspecialchars($tutor['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-gray-700 text-sm mb-1">Valor Total <span class="text-red-500">*</span></label>
                    <input type="text" id="valor_total" name="valor_total" required placeholder="Ex: 99,90" class="border rounded px-3 py-2 w-full focus:ring-2 focus:ring-blue-400 text-gray-700 shadow-sm" inputmode="decimal" pattern="^\d+([,\.]\d{2})?$">
                    <small class="text-gray-400">Se selecionar um pet, o valor será preenchido automaticamente.</small>
                </div>
                <div class="flex gap-3 mt-8 justify-center">
                    <button type="submit" class="bg-blue-600 text-white px-8 py-2 rounded-lg hover:bg-blue-700 font-semibold shadow transition flex items-center gap-2">
                        <i class="fa fa-save"></i> Finalizar Venda
                    </button>
                    <a href="../caixa.php" class="bg-gray-300 text-gray-700 px-8 py-2 rounded-lg hover:bg-gray-400 font-semibold shadow transition flex items-center gap-2">
                        <i class="fa fa-arrow-left"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>