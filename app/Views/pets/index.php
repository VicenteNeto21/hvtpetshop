<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Gerenciar Pets<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <?= view('components/sidebar') ?>

    <!-- Main Content -->
    <main class="flex-1 md:ml-64 p-4 md:p-8">
        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 animate-enter">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="dog" class="w-8 h-8 text-brand-500"></i>
                    Gerenciar Pets
                </h1>
                <p class="text-slate-500">Consulte, edite ou cadastre novos pacientes.</p>
            </div>
            <a href="<?= base_url('pets/novo') ?>" class="bg-slate-900 hover:bg-slate-800 text-white px-5 py-2.5 rounded-xl font-medium shadow-lg shadow-slate-900/20 transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Novo Pet
            </a>
        </header>

        <!-- Search & Filters -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 animate-enter" style="animation-delay: 0.1s">
            <div class="relative">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"></i>
                <input type="text" id="searchInput" placeholder="Busque por Nome do Pet, Tutor ou ID..." 
                    class="w-full pl-12 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all text-slate-700 placeholder-slate-400">
            </div>
        </div>

        <!-- Pets List -->
        <div id="petsContainer" class="flex flex-col gap-3 animate-enter" style="animation-delay: 0.2s">
            <?php foreach($pets as $pet): ?>
                <?= view_cell('App\Cells\PetCard::render', ['pet' => $pet]) ?>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <div class="mt-8 animate-enter" style="animation-delay: 0.3s">
            <?= $pager->links('default', 'tailwind_full') ?>
        </div>
        
        <!-- Loading State (Hidden by default) -->
        <div id="loadingState" class="hidden py-12 text-center text-slate-400">
            <i data-lucide="loader-2" class="w-8 h-8 animate-spin mx-auto mb-2"></i>
            <p>Buscando pets...</p>
        </div>

        <!-- Empty State (Hidden) -->
        <div id="emptyState" class="hidden py-12 text-center text-slate-400 bg-white rounded-2xl border border-dashed border-slate-200">
            <i data-lucide="search-x" class="w-12 h-12 mx-auto mb-4 opacity-50"></i>
            <p class="text-lg font-medium text-slate-600">Nenhum pet encontrado.</p>
            <p class="text-sm">Tente buscar por outro termo.</p>
        </div>

    </main>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const searchInput = document.getElementById('searchInput');
    const petsContainer = document.getElementById('petsContainer');
    const loadingState = document.getElementById('loadingState');
    const emptyState = document.getElementById('emptyState');
    let debounceTimer;

    searchInput.addEventListener('input', function(e) {
        clearTimeout(debounceTimer);
        const term = e.target.value.trim();

        debounceTimer = setTimeout(() => {
            fetchPets(term);
        }, 300); // 300ms debounce
    });

    function fetchPets(term) {
        petsContainer.classList.add('opacity-50');
        // loadingState.classList.remove('hidden'); // Optional: show spinner
        
        fetch(`<?= base_url('pets/search') ?>?term=${term}`)
            .then(response => response.json())
            .then(data => {
                petsContainer.innerHTML = '';
                
                if (data.length === 0) {
                    emptyState.classList.remove('hidden');
                    petsContainer.classList.add('hidden');
                } else {
                    emptyState.classList.add('hidden');
                    petsContainer.classList.remove('hidden');
                    
                    data.forEach(pet => {
                        const cardHTML = buildPetCard(pet);
                        petsContainer.insertAdjacentHTML('beforeend', cardHTML);
                    });
                    lucide.createIcons(); // Re-init icons for new content
                }
            })
            .finally(() => {
                petsContainer.classList.remove('opacity-50');
            });
    }

    function buildPetCard(pet) {
        return `
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 hover:shadow-md transition-all group flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4 flex-1">
                    <div class="w-12 h-12 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center text-lg font-bold shrink-0">
                        ${pet.nome.charAt(0)}
                    </div>
                    <div>
                        <div class="flex items-center gap-2">
                            <h3 class="font-bold text-slate-800 text-lg leading-tight">${pet.nome}</h3>
                            <span class="text-xs text-slate-500 font-medium bg-slate-100 px-2 py-0.5 rounded-full">
                                ${pet.raca || 'SRD'}
                            </span>
                        </div>
                        <div class="flex items-center gap-2 text-sm text-slate-500 mt-0.5">
                            <i data-lucide="user" class="w-3 h-3"></i>
                            <span>${pet.tutor_nome || 'Sem Tutor'}</span>
                        </div>
                    </div>
                </div>
                
                <div class="flex items-center gap-3 pt-3 md:pt-0 border-t md:border-t-0 border-slate-50 md:border-l md:pl-4 border-slate-100">
                     <a href="<?= base_url('pets/editar') ?>/${pet.id}" class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 border border-slate-200 transition-colors">
                        Editar
                    </a>
                    <a href="<?= base_url('agenda/novo') ?>?pet=${pet.id}" class="px-4 py-2 rounded-lg text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 shadow-sm shadow-brand-500/20 transition-colors flex items-center gap-2">
                        <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                        Agendar
                    </a>
                </div>
            </div>
        `;
    }
</script>
<?= $this->endSection() ?>
