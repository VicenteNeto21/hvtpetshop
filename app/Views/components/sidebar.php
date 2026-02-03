<aside id="sidebar" class="hidden md:flex flex-col w-64 bg-slate-900 border-r border-slate-800 h-screen fixed left-0 top-0 overflow-y-auto z-20 transition-all duration-300">
    <!-- Logo -->
    <div class="p-4 flex items-center justify-between border-b border-slate-800/50">
        <a href="<?= base_url('dashboard') ?>" class="flex items-center gap-3 sidebar-logo">
            <div class="w-10 h-10 rounded-xl bg-brand-500 flex items-center justify-center text-white shadow-lg shadow-brand-500/20 flex-shrink-0">
                <i data-lucide="paw-print" class="w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold text-white tracking-wide sidebar-text">Cerenia<span class="text-brand-400">Pet</span></span>
        </a>
        <!-- Toggle Button -->
        <button onclick="toggleSidebar()" class="p-2 rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors sidebar-toggle" title="Encolher menu">
            <i data-lucide="panel-left-close" class="w-5 h-5 sidebar-icon-collapse"></i>
            <i data-lucide="panel-left-open" class="w-5 h-5 sidebar-icon-expand hidden"></i>
        </button>
    </div>

    <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
        <!-- Seção: Principal -->
        <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 sidebar-text">Principal</p>
        
        <a href="<?= base_url('dashboard') ?>" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= uri_string() == 'dashboard' ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>" title="Dashboard">
            <i data-lucide="layout-dashboard" class="w-5 h-5 flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Dashboard</span>
        </a>

        <a href="<?= base_url('agenda') ?>" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= uri_string() == 'agenda' ? 'bg-brand-500 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>" title="Agenda">
            <i data-lucide="calendar" class="w-5 h-5 flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Agenda</span>
        </a>
        <!-- Seção: Gestão -->
        <div class="pt-4">
            <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 sidebar-text">Gestão</p>
        </div>

        <a href="<?= base_url('pets') ?>" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= strpos(uri_string(), 'pets') !== false ? 'bg-brand-500 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>" title="Pets">
            <i data-lucide="dog" class="w-5 h-5 flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Pets</span>
        </a>
        
        <a href="<?= base_url('tutores') ?>" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= strpos(uri_string(), 'tutores') !== false ? 'bg-brand-500 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>" title="Tutores">
            <i data-lucide="users" class="w-5 h-5 flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Tutores</span>
        </a>

        <a href="<?= base_url('servicos') ?>" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= strpos(uri_string(), 'servicos') !== false ? 'bg-brand-500 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>" title="Serviços">
            <i data-lucide="scissors" class="w-5 h-5 flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Serviços</span>
        </a>

        <?php 
        $userTipo = session()->get('usuario_tipo');
        if (!$userTipo && session()->get('usuario_id')) {
            $userModel = new \App\Models\UsuarioModel();
            $user = $userModel->find(session()->get('usuario_id'));
            $userTipo = $user['tipo'] ?? 'funcionario';
            session()->set('usuario_tipo', $userTipo);
        }
        
        if($userTipo === 'admin'): ?>
            <!-- Seção: Admin -->
            <div class="pt-4">
                <p class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2 sidebar-text">Admin</p>
            </div>

            <a href="<?= base_url('admin') ?>" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= uri_string() == 'admin' ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>" title="Administração">
                <i data-lucide="bar-chart-2" class="w-5 h-5 flex-shrink-0"></i>
                <span class="font-medium sidebar-text">Administração</span>
            </a>

            <a href="<?= base_url('usuarios') ?>" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= strpos(uri_string(), 'usuarios') !== false ? 'bg-brand-500 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>" title="Usuários">
                <i data-lucide="user-cog" class="w-5 h-5 flex-shrink-0"></i>
                <span class="font-medium sidebar-text">Usuários</span>
            </a>
        <?php endif; ?>
    </nav>

    <!-- Footer -->
    <div class="p-3 border-t border-slate-800/50 mt-auto">
        <a href="<?= base_url('perfil') ?>" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl transition-all <?= uri_string() == 'perfil' ? 'bg-white/10 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>" title="Meu Perfil">
            <i data-lucide="user-circle" class="w-5 h-5 flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Meu Perfil</span>
        </a>

        <a href="<?= base_url('logout') ?>" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-all" title="Sair">
            <i data-lucide="log-out" class="w-5 h-5 flex-shrink-0"></i>
            <span class="font-medium sidebar-text">Sair</span>
        </a>
        
        <!-- Versão do Sistema -->
        <div class="mt-3 px-2 sidebar-text">
            <p class="text-[10px] text-slate-600 text-center leading-relaxed">
                © <?= date('Y') ?> CereniaPet. Todos os direitos reservados.
            </p>
            <p class="text-[10px] text-slate-500 text-center mt-1">
                <span class="font-semibold text-brand-500">AMPN 3.1.0</span>
            </p>
            <p class="text-[9px] text-slate-600 text-center mt-0.5">
                Desenvolvido com ☕ por: Vicente Neto
            </p>
        </div>
    </div>
</aside>

<style>
    /* Sidebar collapsed state */
    #sidebar.collapsed {
        width: 72px;
    }
    
    #sidebar.collapsed .sidebar-text {
        display: none;
    }
    
    #sidebar.collapsed .sidebar-logo {
        justify-content: center;
    }
    
    #sidebar.collapsed .sidebar-link {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }
    
    #sidebar.collapsed .sidebar-toggle {
        display: none;
    }
    
    #sidebar.collapsed .sidebar-icon-collapse {
        display: none;
    }
    
    #sidebar.collapsed .sidebar-icon-expand {
        display: block;
    }
    
    /* Hover expand when collapsed */
    #sidebar.collapsed:hover {
        width: 256px;
    }
    
    #sidebar.collapsed:hover .sidebar-text {
        display: inline;
    }
    
    #sidebar.collapsed:hover .sidebar-link {
        justify-content: flex-start;
        padding-left: 0.75rem;
        padding-right: 0.75rem;
    }
    
    #sidebar.collapsed:hover .sidebar-toggle {
        display: block;
    }
    
    /* Adjust main content when sidebar is collapsed */
    body.sidebar-collapsed main {
        margin-left: 72px !important;
    }
    
    body.sidebar-collapsed main:has(#sidebar:hover) {
        margin-left: 256px !important;
    }
</style>

<script>
    // Check saved state on load
    document.addEventListener('DOMContentLoaded', function() {
        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsed) {
            document.getElementById('sidebar').classList.add('collapsed');
            document.body.classList.add('sidebar-collapsed');
        }
    });
    
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const isCollapsed = sidebar.classList.toggle('collapsed');
        document.body.classList.toggle('sidebar-collapsed', isCollapsed);
        localStorage.setItem('sidebarCollapsed', isCollapsed);
        
        // Recreate icons after toggle
        if (typeof lucide !== 'undefined') {
            setTimeout(() => lucide.createIcons(), 100);
        }
    }
</script>
