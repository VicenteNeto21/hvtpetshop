<?php
include "../config/config.php";
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
}

// Deletar tutor (e opcionalmente os pets dele)
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];
    try {
        $pdo->beginTransaction();
        $pdo->prepare("DELETE FROM pets WHERE tutor_id = ?")->execute([$delete_id]);
        $pdo->prepare("DELETE FROM tutores WHERE id = ?")->execute([$delete_id]);
        $pdo->commit();
        $_SESSION['mensagem'] = "Tutor e seus pets excluídos com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['mensagem'] = "Erro ao excluir tutor: " . $e->getMessage();
        $_SESSION['tipo_mensagem'] = "error";
    }
    header("Location: listar_tutores.php");
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
    <title>Lista de Tutores - HVTPETSHOP</title>
    <link rel="icon" type="image/x-icon" href="../icons/pet.jpg">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gradient-to-br from-blue-50 to-blue-200 flex flex-col">

    <!-- Navbar padrão -->
    <nav class="w-full bg-white/90 shadow flex items-center justify-between px-6 py-3">
        <div class="flex items-center gap-3">
            <img src="../icons/pet.jpg" alt="Logo Petshop" class="w-10 h-10 rounded-full shadow">
            <span class="text-2xl font-bold text-blue-700 tracking-tight">HVTPETSHOP</span>
        </div>
        <div class="flex items-center gap-4">
            <a href="../dashboard.php" class="text-blue-600 hover:text-blue-800 font-semibold transition"><i class="fa fa-home mr-1"></i>Dashboard</a>
            <a href="../pets/cadastrar_pet.php" class="text-green-600 hover:text-green-800 font-semibold transition"><i class="fa fa-plus mr-1"></i>Novo Pet</a>
            <a href="listar_tutores.php" class="text-blue-500 hover:text-blue-700 font-semibold transition"><i class="fa fa-users mr-1"></i>Tutores</a>
            <a href="../auth/logout.php" class="text-red-500 hover:text-red-700 font-semibold transition"><i class="fa fa-sign-out-alt mr-1"></i>Sair</a>
        </div>
    </nav>

    <main class="flex-1 w-full max-w-6xl mx-auto mt-10 p-4">
        <div class="bg-white/90 rounded-2xl shadow-xl p-8 animate-fade-in mb-8">
            <h2 class="text-2xl font-bold text-blue-700 mb-6 flex items-center gap-2">
                <i class="fa fa-users"></i> Lista de Tutores
            </h2>

            <!-- Busca -->
            <form method="get" class="mb-8 flex items-center gap-2 max-w-md">
                <div class="relative w-full">
                    <input type="text" name="busca" value="<?= htmlspecialchars($busca) ?>" placeholder="Pesquisar por nome, e-mail ou telefone..." class="w-full border border-blue-200 rounded-lg px-4 py-2 pl-10 focus:ring-2 focus:ring-blue-400 shadow-sm text-gray-700" />
                    <span class="absolute left-3 top-2.5 text-blue-400"><i class="fa fa-search"></i></span>
                </div>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold shadow transition flex items-center gap-2">
                    <i class="fa fa-search"></i> Buscar
                </button>
                <?php if ($busca): ?>
                    <a href="listar_tutores.php" class="ml-2 text-xs text-blue-600 hover:underline">Limpar</a>
                <?php endif; ?>
            </form>

            <?php if (isset($_SESSION['mensagem'])): ?>
                <div class="mb-6 p-4 rounded-lg <?= $_SESSION['tipo_mensagem'] === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                    <div class="flex items-center">
                        <i class="fas <?= $_SESSION['tipo_mensagem'] === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle' ?> mr-3"></i>
                        <span><?= htmlspecialchars($_SESSION['mensagem']) ?></span>
                    </div>
                    <?php unset($_SESSION['mensagem']); unset($_SESSION['tipo_mensagem']); ?>
                </div>
            <?php endif; ?>

            <?php if (empty($tutores)): ?>
                <div class="text-center text-gray-500 py-8">Nenhum tutor cadastrado.</div>
            <?php else: ?>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($tutores as $tutor): ?>
                        <div class="bg-white border border-blue-100 rounded-2xl shadow-lg p-6 flex flex-col gap-4 hover:shadow-2xl transition-shadow duration-300 group cursor-pointer relative"
                             onclick="window.location='visualizar_tutor.php?id=<?= $tutor['id'] ?>'">
                            <div class="flex items-center gap-4 mb-2">
                                <div class="bg-gradient-to-br from-blue-200 to-blue-400 text-blue-900 rounded-full w-14 h-14 flex items-center justify-center text-2xl font-bold shadow-inner border-2 border-white">
                                    <i class="fa fa-user"></i>
                                </div>
                                <div>
                                    <div class="font-bold text-xl text-blue-800"><?= htmlspecialchars($tutor['nome']) ?></div>
                                    <div class="text-xs text-gray-500 flex items-center gap-1">
                                        <i class="fa fa-envelope text-blue-300"></i>
                                        <?= htmlspecialchars($tutor['email']) ?>
                                    </div>
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-4 items-center text-gray-700 text-sm">
                                <div class="flex items-center gap-2 bg-blue-50 px-3 py-1 rounded-full shadow-sm">
                                    <i class="fa fa-phone text-blue-400"></i>
                                    <span><?= htmlspecialchars(formatarTelefone($tutor['telefone'])) ?></span>
                                </div>
                                <div class="flex items-center gap-2 bg-green-50 px-3 py-1 rounded-full shadow-sm">
                                    <i class="fa fa-paw text-green-500"></i>
                                    <span><?= $tutor['total_pets'] ?> pet(s)</span>
                                </div>
                            </div>
                            <div class="flex gap-2 mt-2">
                                <a href="editar_tutor.php?id=<?= $tutor['id'] ?>"
                                   class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg text-xs font-semibold transition flex items-center gap-2 shadow z-10"
                                   title="Editar" onclick="event.stopPropagation();">
                                    <i class="fa fa-edit"></i> Editar
                                </a>
                                <a href="listar_tutores.php?delete_id=<?= $tutor['id'] ?>"
                                   class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-xs font-semibold transition flex items-center gap-2 shadow z-10"
                                   onclick="event.stopPropagation(); return confirm('Tem certeza que deseja excluir este tutor e todos os seus pets?')"
                                   title="Excluir">
                                    <i class="fa fa-trash"></i> Excluir
                                </a>
                            </div>
                            <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition">
                                <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-xs font-semibold shadow flex items-center gap-1">
                                    <i class="fa fa-eye"></i> Visualizar
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
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