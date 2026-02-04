<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Cadastro Rápido<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="w-full animate-enter">
            <!-- Header -->
            <div class="mb-8">
                <a href="<?= base_url('agenda/novo') ?>" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-brand-600 mb-4 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Voltar para Agendamento
                </a>
                <h1 class="text-3xl font-bold text-slate-900">Cadastro Rápido</h1>
                <p class="text-slate-500 mt-1">Crie o registro do Tutor e Pet em uma única etapa.</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100">
                <form action="<?= base_url('agenda/salvar-cadastro-rapido') ?>" method="POST" class="p-6 md:p-8 space-y-8">
                    <?= csrf_field() ?>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                        <!-- Lado Esquerdo: Tutor -->
                        <div class="space-y-6">
                            <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
                                <i data-lucide="user" class="w-5 h-5 text-brand-500"></i>
                                Dados do Tutor
                            </h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-1">Nome Completo *</label>
                                    <input type="text" name="tutor_nome" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all outline-none">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-1">Telefone / WhatsApp</label>
                                    <input type="text" name="tutor_telefone" id="tutor_telefone" placeholder="(xx) xxxxx-xxxx" oninput="mascaraTelefone(this)" maxlength="15"
                                        class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all outline-none disabled:bg-slate-100 disabled:text-slate-400">
                                    
                                    <div class="flex flex-col gap-2 mt-3">
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" id="no_phone" onchange="togglePhoneField(this)" class="rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                                            <span class="text-sm text-slate-600">Telefone não informado</span>
                                        </label>
                                        
                                        <label class="flex items-center gap-2 cursor-pointer">
                                            <input type="checkbox" name="is_whatsapp" id="is_whatsapp" value="1" checked class="rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                                            <span class="text-sm text-slate-600">Este número é WhatsApp</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Lado Direito: Pet -->
                        <div class="space-y-6">
                            <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
                                <i data-lucide="dog" class="w-5 h-5 text-brand-500"></i>
                                Dados do Pet
                            </h2>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-1">Nome do Pet *</label>
                                    <input type="text" name="pet_nome" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all outline-none">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <?= view('components/custom_select', [
                                        'id' => 'pet_especie',
                                        'name' => 'pet_especie',
                                        'label' => 'Espécie *',
                                        'options' => [
                                            ['value' => 'Cachorro', 'text' => 'Cachorro', 'icon' => 'dog'],
                                            ['value' => 'Gato', 'text' => 'Gato', 'icon' => 'cat'],
                                        ],
                                        'selected' => 'Cachorro',
                                        'required' => true,
                                        'onchange' => 'updateBreedOptions'
                                    ]) ?>

                                    <?= view('components/custom_select', [
                                        'id' => 'pet_sexo',
                                        'name' => 'pet_sexo',
                                        'label' => 'Sexo *',
                                        'options' => [
                                            ['value' => 'M', 'text' => 'Macho', 'icon' => 'circle-arrow-up'],
                                            ['value' => 'F', 'text' => 'Fêmea', 'icon' => 'circle-arrow-down'],
                                        ],
                                        'selected' => 'M',
                                        'required' => true
                                    ]) ?>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-1">Raça</label>
                                    <div class="relative">
                                        <select id="select-raca" name="pet_raca" class="w-full"></select>
                                        <i data-lucide="dna" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 z-10 text-slate-400 pointer-events-none" id="raca-icon"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-50">
                        <a href="<?= base_url('agenda/novo') ?>" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-colors">
                            Cancelar
                        </a>
                        <?= view('components/btn_salvar', ['label' => 'Salvar e Agendar', 'icon' => 'check']) ?>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<style>
    /* Estilização customizada para TomSelect no Cadastro Rápido */
    .ts-control {
        border-radius: 0.75rem !important;
        padding: 0.65rem 0.75rem 0.65rem 3rem !important;
        border: 1px solid #e2e8f0 !important;
        background-color: #f8fafc !important;
        font-size: 0.875rem !important;
        min-height: 46px;
        transition: all 0.2s;
    }
    .ts-wrapper.focus .ts-control {
        border-color: #8b5cf6 !important;
        background-color: #fff !important;
        box-shadow: 0 0 0 2px #ddd6fe !important;
    }
    .ts-dropdown {
        border-radius: 0.75rem !important;
        margin-top: 8px !important;
        box-shadow: 0 10px 25px -5px rgb(0 0 0 / 0.15), 0 8px 10px -6px rgb(0 0 0 / 0.1) !important;
        border: 1px solid #e2e8f0 !important;
        padding: 0.5rem !important;
        animation: dropdownFade 0.2s ease-out;
    }
    @keyframes dropdownFade {
        from { opacity: 0; transform: translateY(-8px); }
        to { opacity: 1; transform: translateY(0); }
    }
    .ts-dropdown .option {
        padding: 0.65rem 1rem !important;
        border-radius: 0.5rem !important;
        margin: 2px 0 !important;
        transition: all 0.15s !important;
        color: #475569 !important;
    }
    .ts-dropdown .option:hover,
    .ts-dropdown .option.active {
        background-color: #f5f3ff !important; /* brand-50 */
        color: #7c3aed !important; /* brand-600 */
    }
    .ts-dropdown .option.selected {
        background-color: #ede9fe !important; /* brand-100 */
        color: #6d28d9 !important; /* brand-700 */
        font-weight: 500;
    }
    .ts-dropdown .create {
        padding: 0.65rem 1rem !important;
        border-radius: 0.5rem !important;
        color: #8b5cf6 !important;
        font-style: italic;
    }
    #raca-icon { display: none; }
</style>

<script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>

<script>
    function togglePhoneField(checkbox) {
        const input = document.getElementById('tutor_telefone');
        const isWhatsapp = document.getElementById('is_whatsapp');
        if (checkbox.checked) {
            input.value = '';
            input.disabled = true;
            input.placeholder = "Não informado";
            isWhatsapp.checked = false;
            isWhatsapp.disabled = true;
        } else {
            input.disabled = false;
            input.placeholder = "(xx) xxxxx-xxxx";
            isWhatsapp.disabled = false;
            isWhatsapp.checked = true;
        }
    }

    function mascaraTelefone(input) {
        let v = input.value.replace(/\D/g, '');
        if (v.length > 11) v = v.slice(0, 11);
        if (v.length > 0) v = '(' + v;
        if (v.length > 3) v = v.slice(0, 3) + ') ' + v.slice(3);
        if (v.length > 10) v = v.slice(0, 10) + '-' + v.slice(10);
        input.value = v;
    }

    let racaSelect;
    let racasData = {};

    document.addEventListener('DOMContentLoaded', function() {
        // Init Tom Select for Breeds
        racaSelect = new TomSelect("#select-raca", {
            create: true,
            sortField: { field: "text", direction: "asc" },
            placeholder: "Selecione ou digite a raça...",
            maxOptions: 100,
            plugins: ['dropdown_input'],
            onInitialize: function() {
                document.getElementById('raca-icon').style.display = 'block';
            }
        });

        // Load Breeds JSON
        fetch('<?= base_url('assets/json/racas.json') ?>')
            .then(response => response.json())
            .then(data => {
                racasData = data;
                updateBreedOptions();
            })
            .catch(error => console.error('Erro ao carregar raças:', error));
    });

    function updateBreedOptions() {
        if (!racaSelect) return;

        const especie = document.getElementById('pet_especie').value;
        let key = especie === 'Cachorro' ? 'Canino' : (especie === 'Gato' ? 'Felina' : '');

        racaSelect.clear();
        racaSelect.clearOptions();
        racaSelect.addOption({value: 'SRD', text: 'SRD (Sem Raça Definida)'});

        if (key && racasData[key]) {
            racasData[key].forEach(raca => {
                racaSelect.addOption({value: raca, text: raca});
            });
        }
        
        racaSelect.setValue('SRD');
    }
</script>
<?= $this->endSection() ?>
