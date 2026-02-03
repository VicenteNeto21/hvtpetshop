<aside class="hidden md:flex flex-col w-64 bg-slate-900 border-r border-slate-800 h-screen fixed left-0 top-0 overflow-y-auto z-20 transition-all duration-300">
    <div class="p-6 flex items-center justify-center border-b border-slate-800/50">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-brand-500 flex items-center justify-center text-white shadow-lg shadow-brand-500/20">
                <i data-lucide="paw-print" class="w-5 h-5"></i>
            </div>
            <span class="text-xl font-bold text-white tracking-wide">Cerenia<span class="text-brand-400">Pet</span></span>
        </div>
    </div>

    <nav class="flex-1 px-4 py-6 space-y-1">
        <p class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Principal</p>
        
        <a href="<?= base_url('dashboard') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= uri_string() == 'dashboard' ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
            <i data-lucide="layout-dashboard" class="w-5 h-5"></i>
            <span class="font-medium">Dashboard</span>
        </a>

        <a href="<?= base_url('admin') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= uri_string() == 'admin' ? 'bg-brand-500 text-white shadow-lg shadow-brand-500/20' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
            <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
            <span class="font-medium">Administração</span>
        </a>

        <a href="<?= base_url('agenda') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= uri_string() == 'agenda' ? 'bg-brand-500 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
            <i data-lucide="calendar" class="w-5 h-5"></i>
            <span class="font-medium">Agenda</span>
        </a>

        <p class="px-2 text-xs font-semibold text-slate-500 uppercase tracking-wider mt-6 mb-2">Gestão</p>

        <a href="<?= base_url('pets') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= strpos(uri_string(), 'pets') !== false ? 'bg-brand-500 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
            <i data-lucide="dog" class="w-5 h-5"></i>
            <span class="font-medium">Pets</span>
        </a>
        
        <a href="<?= base_url('tutores') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= strpos(uri_string(), 'tutores') !== false ? 'bg-brand-500 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
            <i data-lucide="users" class="w-5 h-5"></i>
            <span class="font-medium">Tutores</span>
        </a>

         <a href="<?= base_url('servicos') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all <?= strpos(uri_string(), 'servicos') !== false ? 'bg-brand-500 text-white' : 'text-slate-400 hover:bg-slate-800 hover:text-white' ?>">
            <i data-lucide="scissors" class="w-5 h-5"></i>
            <span class="font-medium">Serviços</span>
        </a>
    </nav>

    <div class="p-4 border-t border-slate-800/50">
        <a href="<?= base_url('logout') ?>" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-all">
            <i data-lucide="log-out" class="w-5 h-5"></i>
            <span class="font-medium">Sair</span>
        </a>
    </div>
</aside>
