<!-- navbar.php -->
<nav class="bg-gradient-to-r from-blue-600 to-blue-800 text-white p-4 fixed w-full top-0 left-0 shadow-lg z-50">
    <div class="max-w-6xl mx-auto flex justify-between items-center">
        <!-- Logo -->
        <a href="../dashboard.php" class="text-xl font-bold flex items-center space-x-2">
            <i class="fas fa-paw text-2xl"></i>
            <span>HVTPETSHOP</span>
        </a>

        <!-- Menu para desktop -->
        <ul class="hidden md:flex space-x-6">
            <li><a href="../dashboard.php" class="hover:text-blue-200 transition duration-300">Início</a></li>
            <li><a href="../pets/cadastrar_pet.php" class="hover:text-blue-200 transition duration-300">Adicionar Pet</a></li>
        </ul>

        <!-- Botão de Sair para desktop -->
        <a href="../auth/logout.php" class="hidden md:block bg-red-600 hover:bg-red-700 px-4 py-2 rounded-md transition duration-300">
            <i class="fas fa-sign-out-alt"></i> Sair
        </a>

        <!-- Botão do menu hambúrguer para mobile -->
        <button id="mobile-menu-button" class="md:hidden text-white focus:outline-none">
            <i class="fas fa-bars text-2xl"></i>
        </button>
    </div>

    <!-- Menu mobile (oculto por padrão) -->
    <div id="mobile-menu" class="mobile-menu mt-4 md:hidden hidden bg-blue-700 rounded-lg shadow-lg p-4">
        <ul class="space-y-4">
            <li><a href="dashboard.php" class="block hover:bg-blue-600 p-2 rounded-lg transition duration-300">Início</a></li>
            <li><a href="../pets/cadastrar_pet.php" class="block hover:bg-blue-600 p-2 rounded-lg transition duration-300">Adicionar Pet</a></li>
            <li>
                <a href="../auth/logout.php" class="block bg-red-600 hover:bg-red-700 p-2 rounded-lg text-center transition duration-300">
                    <i class="fas fa-sign-out-alt"></i> Sair
                </a>
            </li>
        </ul>
    </div>
</nav>

<!-- Script para o menu mobile -->
<script>
    document.getElementById('mobile-menu-button').addEventListener('click', function() {
        const mobileMenu = document.getElementById('mobile-menu');
        mobileMenu.classList.toggle('hidden');
    });
</script>