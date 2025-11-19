<?php
include "../config/config.php";
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.html");
    exit();
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
                    <div class="relative w-full md:w-auto md:max-w-xs flex-grow">
                        <input type="text" id="buscaInput" placeholder="Pesquisar tutor..." class="w-full p-2 pl-10 border border-slate-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white text-sm" />
                        <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                    </div>
                    <a href="cadastrar_tutor.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold shadow-sm transition flex items-center gap-2 text-sm whitespace-nowrap">
                        <i class="fa fa-plus"></i> Novo Tutor
                    </a>
                </div>
            </div>

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
                    <tbody id="tutors-tbody" class="divide-y divide-slate-100">
                        <!-- Conteúdo carregado via AJAX -->
                    </tbody>
                </table>
            </div>

            <!-- Paginação -->
            <div id="pagination-container" class="flex justify-center mt-6 pt-4 border-t border-slate-200">
                <!-- Links da paginação carregados via AJAX -->
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
        document.addEventListener('DOMContentLoaded', function() {
            const buscaInput = document.getElementById('buscaInput');
            const tutorsTbody = document.getElementById('tutors-tbody');
            const paginationContainer = document.getElementById('pagination-container');
            let currentPage = 1;

            function buscarTutores(termo = '', pagina = 1) {
                currentPage = pagina;
                // Efeito de loading
                tutorsTbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-slate-500"><i class="fas fa-spinner fa-spin mr-2"></i>Buscando...</td></tr>';
                paginationContainer.innerHTML = '';

                fetch(`buscar_tutores.php?busca=${encodeURIComponent(termo)}&pagina=${pagina}`)
                    .then(response => response.json())
                    .then(data => {
                        tutorsTbody.innerHTML = data.tableContent;
                        paginationContainer.innerHTML = data.paginationContent;
                    })
                    .catch(error => {
                        console.error('Erro ao buscar tutores:', error);
                        tutorsTbody.innerHTML = '<tr><td colspan="4" class="p-4 text-center text-red-500">Erro ao carregar os dados.</td></tr>';
                    });
            }

            // Busca inicial
            buscarTutores();

            // Busca ao digitar
            buscaInput.addEventListener('keyup', () => {
                buscarTutores(buscaInput.value, 1); // Sempre volta para a página 1 ao pesquisar
            });

            // Torna a função acessível globalmente para os botões de paginação
            window.buscarTutores = buscarTutores;
        });
    </script>
</body>
</html>