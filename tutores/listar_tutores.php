<?php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Busca todos os tutores e a quantidade de pets de cada um
$busca = isset($_GET['busca']) ? trim($_GET['busca']) : '';
$params = [];
$sql = "
    SELECT t.id, t.nome, t.email, t.telefone, COUNT(p.id) as total_pets
    FROM tutores t
    LEFT JOIN pets p ON p.tutor_id = t.id
";
if ($busca !== '') {
    $sql .= " WHERE t.nome LIKE :busca OR t.email LIKE :busca OR t.telefone LIKE :busca ";
    $params[':busca'] = "%$busca%";
}
$sql .= " GROUP BY t.id, t.nome, t.email, t.telefone ORDER BY t.nome";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$tutores = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

// Função para buscar pets do tutor
function buscarPets($pdo, $tutor_id) {
    $stmt = $pdo->prepare("SELECT id, nome, especie, raca FROM pets WHERE tutor_id = ?");
    $stmt->execute([$tutor_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Tutores - CereniaPet</title>
    <link rel="icon" type="image/x-icon" href="../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <?php
    // Define o prefixo do caminho para o navbar. Como estamos em uma subpasta, é '../'
    $path_prefix = '../';
    include '../components/navbar.php';
    ?>

    <?php include '../components/toast.php'; ?>

    <main class="flex-1 w-full p-4 md:p-6 lg:p-8">
        <!-- Cabeçalho da Página -->
        <div class="mb-8 animate-fade-in">
            <h1 class="text-3xl font-bold text-slate-800">Tutores Cadastrados</h1>
            <p class="text-slate-500 mt-1">Gerencie os tutores e seus pets.</p>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-sm animate-fade-in">
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                <h2 class="text-xl font-bold text-slate-800 flex items-center gap-3">
                    <i class="fa-solid fa-users text-amber-500"></i>
                    Lista de Tutores
                </h2>
                <div class="flex items-center gap-4 w-full md:w-auto">
                    <!-- Busca -->
                    <form method="get" class="relative w-full md:w-auto md:max-w-xs flex-grow">
                        <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Pesquisar tutor..." class="w-full p-2 pl-10 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm" />
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </form>
                    <a href="cadastrar_tutor.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition flex items-center gap-2 text-sm whitespace-nowrap">
                        <i class="fa fa-plus"></i> Novo Tutor
                    </a>
                </div>
            </div>

            <?php if (empty($tutores)): ?>
                <div class="text-slate-500 text-center py-8 border-2 border-dashed rounded-lg">Nenhum tutor encontrado com os filtros atuais.</div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full text-sm">
                        <thead class="border-b-2 border-slate-200">
                            <tr>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Tutor</th>
                                <th class="px-4 py-3 text-left font-semibold text-slate-600 uppercase tracking-wider">Contato</th>
                                <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase tracking-wider">Pets</th>
                                <th class="px-4 py-3 text-center font-semibold text-slate-600 uppercase tracking-wider">Ações</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <?php foreach ($tutores as $tutor): ?>
                                <tr class="hover:bg-slate-50 group cursor-pointer" onclick="window.location='visualizar_tutor.php?id=<?= $tutor['id']; ?>'">
                                    <td class="px-4 py-4 font-semibold text-slate-800 whitespace-nowrap">
                                        <?= htmlspecialchars($tutor['nome']) ?>
                                    </td>
                                    <td class="px-4 py-4" onclick="event.stopPropagation();">
                                        <div class="flex flex-col">
                                            <span class="text-slate-600"><?= htmlspecialchars($tutor['email']) ?></span>
                                            <span class="text-slate-500 text-xs"><?= htmlspecialchars(formatarTelefone($tutor['telefone'])) ?></span>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center text-slate-500">
                                        <?= $tutor['total_pets'] ?>
                                    </td>
                                    <td class="px-4 py-4" onclick="event.stopPropagation();">
                                        <div class="flex items-center justify-center gap-4">
                                            <a href="editar_tutor.php?id=<?= $tutor['id']; ?>" class="text-amber-600 hover:text-amber-800" title="Editar Tutor"><i class="fas fa-edit"></i></a>
                                            <a href="javascript:void(0);" onclick="openConfirmationModal('Excluir Tutor', 'Tem certeza que deseja excluir o tutor \'<?= htmlspecialchars($tutor['nome'], ENT_QUOTES) ?>\'? Todos os pets e agendamentos associados também serão removidos. Esta ação não pode ser desfeita.', 'excluir_tutor.php?id=<?= $tutor['id']; ?>')" class="text-red-600 hover:text-red-800" title="Excluir Tutor"><i class="fas fa-trash"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
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
</body>
</html>