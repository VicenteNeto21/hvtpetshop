<?php
// Este componente espera que a variável $path_prefix seja definida antes de ser incluído.
// Ex: $path_prefix = '../'; (para páginas dentro de subpastas)
// Ex: $path_prefix = './'; (para páginas na raiz)
if (!isset($path_prefix)) {
    $path_prefix = './';
}

// Lógica para determinar a página ativa
$current_page = basename($_SERVER['PHP_SELF']);
$active_class = 'bg-slate-100 text-blue-600';
?>
<!-- Navbar responsiva -->
<nav class="w-full bg-white border-b border-slate-200 shadow-sm flex items-center justify-between px-4 md:px-6 py-2 sticky top-0 z-50">
    <div class="flex items-center gap-3">
        <img src="<?= $path_prefix ?>icons/pet.jpg" alt="Logo Petshop" class="w-10 h-10 rounded-full shadow">
        <a href="<?= $path_prefix ?>dashboard.php" class="text-xl md:text-2xl font-bold text-slate-800 tracking-tight">CereniaPet</a>
    </div>
    <!-- Botão hamburguer mobile -->
    <button id="menu-toggle" class="md:hidden text-slate-600 text-2xl focus:outline-none">
        <i class="fa fa-bars"></i>
    </button>
    <!-- Links da navbar -->
    <div id="navbar-links" class="hidden md:flex flex-col md:flex-row md:items-center gap-2 absolute md:static top-16 left-0 w-full md:w-auto bg-white md:bg-transparent shadow-lg md:shadow-none z-50 md:z-auto p-4 md:p-0">
        <a href="<?= $path_prefix ?>dashboard.php" data-page="dashboard.php" class="nav-link text-slate-600 hover:bg-slate-100 hover:text-blue-600 font-semibold transition flex items-center gap-2 px-3 py-2 rounded-md">
            <i class="fa-solid fa-table-columns w-4 text-center"></i><span>Dashboard</span>
        </a>
        <a href="<?= $path_prefix ?>pets/cadastrar_pet.php" data-page="cadastrar_pet.php" class="nav-link text-slate-600 hover:bg-slate-100 hover:text-blue-600 font-semibold transition flex items-center gap-2 px-3 py-2 rounded-md">
            <i class="fa-solid fa-plus w-4 text-center"></i><span>Novo Pet</span>
        </a>
        <a href="<?= $path_prefix ?>tutores/listar_tutores.php" data-page="listar_tutores.php" class="nav-link text-slate-600 hover:bg-slate-100 hover:text-blue-600 font-semibold transition flex items-center gap-2 px-3 py-2 rounded-md">
            <i class="fa-solid fa-users w-4 text-center"></i><span>Tutores</span>
        </a>
        <a href="<?= $path_prefix ?>dashboard/indicadores.php" data-page="indicadores.php" class="nav-link text-slate-600 hover:bg-slate-100 hover:text-blue-600 font-semibold transition flex items-center gap-2 px-3 py-2 rounded-md">
            <i class="fa-solid fa-chart-bar w-4 text-center"></i> <span>Indicadores</span>
        </a>
        <a href="#" class="text-slate-400 font-semibold flex items-center gap-2 px-3 py-2 rounded-md cursor-not-allowed" title="Em breve">
            <i class="fa-solid fa-cash-register w-4 text-center"></i> <span>PDV</span>
        </a>
        <a href="javascript:void(0);" onclick="openConfirmationModal('Confirmar Saída', 'Tem certeza que deseja sair do sistema?', '<?= $path_prefix ?>auth/logout.php', 'Sair', 'bg-red-500', 'hover:bg-red-600')" class="text-slate-600 hover:bg-red-50 hover:text-red-600 font-semibold transition flex items-center gap-2 px-3 py-2 rounded-md md:ml-4">
            <i class="fa-solid fa-right-from-bracket w-4 text-center"></i><span>Sair</span>
        </a>
    </div>
</nav>

<!-- Modal de Confirmação Genérico -->
<div id="confirmationModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
    <div class="bg-white rounded-xl shadow-lg p-6 max-w-sm w-full text-center animate-fade-in">
        <i id="modalIcon" class="fa-solid fa-triangle-exclamation text-red-500 text-4xl mb-3"></i>
        <h3 id="modalTitle" class="text-lg font-bold text-slate-800 mb-2">Confirmar Ação</h3>
        <p id="modalMessage" class="text-slate-600 mb-6">Tem certeza que deseja prosseguir?</p>
        <div class="flex justify-center gap-4">
            <button id="cancelButton" class="bg-slate-200 hover:bg-slate-300 text-slate-700 py-2 px-5 rounded-lg font-semibold shadow-sm transition">
                Cancelar
            </button>
            <a id="confirmButton" href="#" class="bg-red-500 hover:bg-red-600 text-white py-2 px-5 rounded-lg font-semibold shadow-sm transition flex items-center gap-2">
                Confirmar
            </a>
        </div>
    </div>
</div>

<script>
    // Navbar responsiva
    const menuToggle = document.getElementById('menu-toggle');
    const navbarLinks = document.getElementById('navbar-links');
    menuToggle.addEventListener('click', () => navbarLinks.classList.toggle('hidden'));

    // Destaca o link da página ativa
    document.addEventListener('DOMContentLoaded', function() {
        const currentPage = "<?= $current_page ?>";
        const activeClasses = "<?= $active_class ?>".split(' ');
        const navLinks = document.querySelectorAll('.nav-link');
        
        navLinks.forEach(link => {
            if (link.dataset.page === currentPage) {
                link.classList.add(...activeClasses);
            }
        });
    });

    // Funções do Modal de Confirmação
    const confirmationModal = document.getElementById('confirmationModal');
    if (confirmationModal) {
        const modalTitle = document.getElementById('modalTitle');
        const modalMessage = document.getElementById('modalMessage');
        const confirmButton = document.getElementById('confirmButton');
        const cancelButton = document.getElementById('cancelButton');

        function openConfirmationModal(title, message, url, confirmText = 'Confirmar', confirmBg = 'bg-red-500', confirmHoverBg = 'hover:bg-red-600') {
            modalTitle.textContent = title;
            modalMessage.textContent = message;
            confirmButton.href = url;
            confirmButton.textContent = confirmText;

            // Limpa classes de cor antigas e adiciona as novas
            confirmButton.className = confirmButton.className.replace(/bg-\w+-\d+/g, '').replace(/hover:bg-\w+-\d+/g, '');
            confirmButton.classList.add(confirmBg, confirmHoverBg);

            confirmationModal.classList.remove('hidden');
        }

        cancelButton.addEventListener('click', () => confirmationModal.classList.add('hidden'));
    }
</script>

<style>@keyframes fade-in{from{opacity:0;transform:translateY(30px)}to{opacity:1;transform:translateY(0)}}.animate-fade-in{animation:fade-in .5s ease}</style>