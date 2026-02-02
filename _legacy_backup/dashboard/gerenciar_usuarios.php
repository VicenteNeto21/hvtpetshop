<?php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Verifica se o usuário é admin
$stmtAdmin = $pdo->prepare("SELECT tipo FROM usuarios WHERE id = ?");
$stmtAdmin->execute([$_SESSION['usuario_id']]);
$usuarioAtual = $stmtAdmin->fetch(PDO::FETCH_ASSOC);

if ($usuarioAtual['tipo'] !== 'admin') {
    $_SESSION['mensagem'] = "Acesso negado. Apenas administradores podem acessar esta página.";
    $_SESSION['tipo_mensagem'] = "error";
    header("Location: ../dashboard.php");
    exit();
}

// Contagem de usuários pendentes
$totalPendentes = $pdo->query("SELECT COUNT(*) FROM usuarios WHERE status = 'pendente'")->fetchColumn();

// Busca todos os usuários agrupados por status
$stmtPendentes = $pdo->query("SELECT id, nome, email, tipo, criado_em FROM usuarios WHERE status = 'pendente' ORDER BY criado_em DESC");
$pendentes = $stmtPendentes->fetchAll(PDO::FETCH_ASSOC);

$stmtAprovados = $pdo->query("SELECT id, nome, email, tipo, status, criado_em FROM usuarios WHERE status = 'aprovado' ORDER BY nome");
$aprovados = $stmtAprovados->fetchAll(PDO::FETCH_ASSOC);

$stmtRejeitados = $pdo->query("SELECT id, nome, email, tipo, criado_em FROM usuarios WHERE status = 'rejeitado' ORDER BY criado_em DESC");
$rejeitados = $stmtRejeitados->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Usuários - CereniaPet</title>
    <link rel="icon" type="image/x-icon" href="../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <?php
    $path_prefix = '../';
    include '../components/navbar.php';
    ?>
    <?php include '../components/toast.php'; ?>

    <main class="flex-1 w-full p-4 md:p-6 lg:p-8">
        <!-- Cabeçalho -->
        <div class="mb-8 animate-fade-in">
            <h1 class="text-3xl font-bold text-slate-800">Gerenciar Usuários</h1>
            <p class="text-slate-500 mt-1">Aprove ou rejeite cadastros de novos usuários.</p>
        </div>

        <!-- Card de Pendentes (destaque) -->
        <?php if (count($pendentes) > 0): ?>
        <div class="bg-amber-50 border-l-4 border-amber-500 p-6 rounded-r-lg shadow-sm mb-8 animate-fade-in">
            <h2 class="text-xl font-bold text-amber-800 mb-4 flex items-center gap-3">
                <i class="fa-solid fa-user-clock text-amber-500"></i>
                Aguardando Aprovação
                <span class="bg-amber-500 text-white text-sm px-2 py-0.5 rounded-full"><?= count($pendentes) ?></span>
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b-2 border-amber-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-amber-700 uppercase tracking-wider">Nome</th>
                            <th class="px-4 py-3 text-left font-semibold text-amber-700 uppercase tracking-wider">E-mail</th>
                            <th class="px-4 py-3 text-left font-semibold text-amber-700 uppercase tracking-wider">Solicitado em</th>
                            <th class="px-4 py-3 text-center font-semibold text-amber-700 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-amber-100">
                        <?php foreach ($pendentes as $user): ?>
                        <tr class="hover:bg-amber-100/50" id="user-row-<?= $user['id'] ?>">
                            <td class="px-4 py-4 font-semibold text-slate-800"><?= htmlspecialchars($user['nome']) ?></td>
                            <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-4 py-4 text-slate-500"><?= date('d/m/Y H:i', strtotime($user['criado_em'])) ?></td>
                            <td class="px-4 py-4 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <button onclick="alterarStatus(<?= $user['id'] ?>, 'aprovado')" class="bg-green-500 hover:bg-green-600 text-white px-3 py-1.5 rounded-lg text-sm font-semibold transition flex items-center gap-1">
                                        <i class="fas fa-check"></i> Aprovar
                                    </button>
                                    <button onclick="alterarStatus(<?= $user['id'] ?>, 'rejeitado')" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1.5 rounded-lg text-sm font-semibold transition flex items-center gap-1">
                                        <i class="fas fa-times"></i> Rejeitar
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php else: ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-6 rounded-r-lg shadow-sm mb-8 animate-fade-in">
            <p class="text-green-700 flex items-center gap-2">
                <i class="fas fa-check-circle text-green-500"></i>
                Nenhum usuário aguardando aprovação.
            </p>
        </div>
        <?php endif; ?>

        <!-- Usuários Aprovados -->
        <div class="bg-white p-6 rounded-lg shadow-sm mb-8 animate-fade-in">
            <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                <i class="fa-solid fa-users text-green-500"></i>
                Usuários Aprovados
                <span class="bg-green-500 text-white text-sm px-2 py-0.5 rounded-full"><?= count($aprovados) ?></span>
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b-2 border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Nome</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">E-mail</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Tipo</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($aprovados as $user): ?>
                        <tr class="hover:bg-slate-50" id="user-row-<?= $user['id'] ?>">
                            <td class="px-4 py-4 font-semibold text-slate-800"><?= htmlspecialchars($user['nome']) ?></td>
                            <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-4 py-4">
                                <?php if ($user['id'] != $_SESSION['usuario_id']): ?>
                                <select onchange="alterarTipo(<?= $user['id'] ?>, this.value)" class="px-2.5 py-1 rounded-lg text-xs font-semibold border cursor-pointer <?= $user['tipo'] === 'admin' ? 'bg-purple-50 border-purple-300 text-purple-800' : 'bg-slate-50 border-slate-300 text-slate-700' ?>">
                                    <option value="funcionario" <?= $user['tipo'] === 'funcionario' ? 'selected' : '' ?>>Funcionário</option>
                                    <option value="admin" <?= $user['tipo'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                                <?php else: ?>
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                    Admin (Você)
                                </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-4 text-center">
                                <?php if ($user['id'] != $_SESSION['usuario_id']): ?>
                                <button onclick="alterarStatus(<?= $user['id'] ?>, 'rejeitado')" class="text-red-500 hover:text-red-700 transition" title="Revogar Acesso">
                                    <i class="fas fa-ban"></i>
                                </button>
                                <?php else: ?>
                                <span class="text-slate-400 text-xs italic">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Usuários Rejeitados -->
        <?php if (count($rejeitados) > 0): ?>
        <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
            <h2 class="text-xl font-bold text-slate-800 mb-4 flex items-center gap-3">
                <i class="fa-solid fa-user-slash text-red-500"></i>
                Usuários Rejeitados
                <span class="bg-red-500 text-white text-sm px-2 py-0.5 rounded-full"><?= count($rejeitados) ?></span>
            </h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="border-b-2 border-slate-200">
                        <tr>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Nome</th>
                            <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">E-mail</th>
                            <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php foreach ($rejeitados as $user): ?>
                        <tr class="hover:bg-slate-50 opacity-60" id="user-row-<?= $user['id'] ?>">
                            <td class="px-4 py-4 font-semibold text-slate-800"><?= htmlspecialchars($user['nome']) ?></td>
                            <td class="px-4 py-4 text-slate-600"><?= htmlspecialchars($user['email']) ?></td>
                            <td class="px-4 py-4 text-center">
                                <button onclick="alterarStatus(<?= $user['id'] ?>, 'aprovado')" class="text-green-500 hover:text-green-700 transition" title="Aprovar">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
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
        function alterarStatus(userId, novoStatus) {
            const acao = novoStatus === 'aprovado' ? 'aprovar' : 'revogar o acesso de';
            if (!confirm(`Tem certeza que deseja ${acao} este usuário?`)) return;

            fetch('gerenciar_usuarios_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `user_id=${userId}&status=${novoStatus}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro de comunicação com o servidor.');
            });
        }

        function alterarTipo(userId, novoTipo) {
            const acao = novoTipo === 'admin' ? 'promover este usuário a Administrador' : 'rebaixar este usuário para Funcionário';
            if (!confirm(`Tem certeza que deseja ${acao}?`)) {
                location.reload(); // Restaura o select se cancelar
                return;
            }

            fetch('gerenciar_usuarios_action.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `user_id=${userId}&tipo=${novoTipo}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert('Erro: ' + data.message);
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Erro:', error);
                alert('Erro de comunicação com o servidor.');
                location.reload();
            });
        }
    </script>
</body>
</html>
