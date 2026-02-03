<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= isset($pet) ? 'Editar' : 'Novo' ?> Pet<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex min-h-screen bg-slate-50">
    <?= view('components/sidebar') ?>

    <main class="flex-1 md:ml-64 p-4 md:p-8">
        <div class="w-full animate-enter">
            <!-- Header -->
            <div class="mb-8">
                <a href="<?= base_url('pets') ?>" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-brand-600 mb-4 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Voltar para Lista
                </a>
                <h1 class="text-3xl font-bold text-slate-900"><?= isset($pet) ? 'Editar Pet' : 'Novo Pet' ?></h1>
                <p class="text-slate-500 mt-1">Preencha as informações do animal.</p>
            </div>

            <!-- Error Alert -->
            <?php if (session('errors')): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-6 flex items-start gap-3 border border-red-100">
                    <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <ul class="list-disc list-inside">
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                <!-- Dados Principais -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i data-lucide="dog" class="w-5 h-5 text-brand-500"></i>
                            Dados Principais
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Tutor Seletor (Component) -->
                            <div class="md:col-span-2">
                                <?= view('components/tutor_select', ['tutores' => $tutores, 'selected_id' => $selected_tutor_id ?? ($pet['tutor_id'] ?? null)]) ?>
                            </div>

                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nome do Pet *</label>
                                <div class="relative">
                                    <i data-lucide="tag" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" name="nome" value="<?= $pet['nome'] ?? '' ?>" required 
                                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400"
                                        placeholder="Ex: Rex, Luna...">
                                </div>
                            </div>

                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Espécie *</label>
                                <div class="grid grid-cols-3 gap-3">
                                    <?php 
                                    $especies = ['Cachorro', 'Gato', 'Outro'];
                                    $currentEspecie = $pet['especie'] ?? 'Cachorro';
                                    ?>
                                    <?php foreach($especies as $esp): ?>
                                        <label class="cursor-pointer">
                                            <input type="radio" name="especie" value="<?= $esp ?>" <?= $currentEspecie == $esp ? 'checked' : '' ?> class="peer sr-only">
                                            <div class="flex flex-col items-center justify-center p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 peer-checked:bg-brand-50 peer-checked:border-brand-500 peer-checked:text-brand-700 transition-all text-center">
                                                <span class="text-sm font-medium"><?= $esp ?></span>
                                            </div>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Raça</label>
                                <div class="relative">
                                    <select id="select-raca" name="raca" class="w-full" placeholder="Selecione ou digite a raça...">
                                        <option value="">Selecione a raça...</option>
                                        <?php if(isset($pet['raca'])): ?>
                                            <option value="<?= $pet['raca'] ?>" selected><?= $pet['raca'] ?></option>
                                        <?php endif; ?>
                                    </select>
                                    <i data-lucide="dna" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 z-10 text-slate-400 pointer-events-none" id="raca-icon"></i>
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Sexo *</label>
                                <div class="flex gap-4">
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="sexo" value="M" <?= (isset($pet) && $pet['sexo'] == 'M') ? 'checked' : '' ?> class="peer sr-only">
                                        <div class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 peer-checked:bg-blue-50 peer-checked:border-blue-500 peer-checked:text-blue-700 transition-all h-[46px]">
                                            <i data-lucide="move-up-right" class="w-4 h-4"></i>
                                            <span class="text-sm font-medium">Macho</span>
                                        </div>
                                    </label>
                                    <label class="flex-1 cursor-pointer">
                                        <input type="radio" name="sexo" value="F" <?= (isset($pet) && $pet['sexo'] == 'F') ? 'checked' : '' ?> class="peer sr-only">
                                        <div class="flex items-center justify-center gap-2 p-2.5 rounded-xl border border-slate-200 hover:bg-slate-50 peer-checked:bg-pink-50 peer-checked:border-pink-500 peer-checked:text-pink-700 transition-all h-[46px]">
                                            <i data-lucide="move-down-left" class="w-4 h-4"></i>
                                            <span class="text-sm font-medium">Fêmea</span>
                                        </div>
                                    </label>
                                </div>
                            </div>
                             
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nascimento (Aprox.)</label>
                                <div class="relative">
                                    <i data-lucide="calendar-days" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="date" name="nascimento" value="<?= $pet['nascimento'] ?? '' ?>" 
                                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400">
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Peso (kg)</label>
                                <div class="relative">
                                    <i data-lucide="scale" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="number" step="0.1" name="peso" value="<?= $pet['peso'] ?? '' ?>" 
                                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400">
                                </div>
                            </div>
                             <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Cor / Pelagem</label>
                                <div class="relative">
                                    <i data-lucide="palette" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" name="cor" value="<?= $pet['cor'] ?? '' ?>" 
                                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Detalhes Extras -->
                    <div class="space-y-6 pt-4">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i data-lucide="clipboard-list" class="w-5 h-5 text-blue-500"></i>
                            Observações Gerais
                        </h3>
                        <div>
                            <textarea name="observacoes" rows="3" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400" placeholder="Alergias, comportamento, histórico..."><?= $pet['observacoes'] ?? '' ?></textarea>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-6 flex justify-end gap-3">
                        <button type="button" onclick="window.history.back()" class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-colors">
                            Cancelar
                        </button>
                        <?= view('components/btn_salvar', ['label' => 'Salvar Pet']) ?>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<?= $this->endSection() ?>

<?= $this->section('styles') ?>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Breed Select Initialization
            let racaSelect;
            
            if(document.getElementById('select-raca')) {
                racaSelect = new TomSelect("#select-raca", {
                    create: true, // Allow custom breeds
                    sortField: { block: "text", direction: "asc" },
                    placeholder: "Selecione ou digite...",
                    plugins: ['dropdown_input'],
                    render: {
                        option: function(data, escape) {
                            return '<div class="px-2 py-1">' + escape(data.text) + '</div>';
                        }
                    },
                    onInitialize: function() {
                        document.getElementById('raca-icon').style.display = 'block';
                    }
                });
            }

            // Fetch Breeds JSON
            let racasData = {};
            fetch('<?= base_url('assets/json/racas.json') ?>')
                .then(response => response.json())
                .then(data => {
                    racasData = data;
                    updateBreedOptions(); // Update immediately if species already selected (edit mode)
                })
                .catch(error => console.error('Error loading breeds:', error));

            // Species Change Event
            const especieRadios = document.querySelectorAll('input[name="especie"]');
            especieRadios.forEach(radio => {
                radio.addEventListener('change', updateBreedOptions);
            });

            function updateBreedOptions() {
                if (!racaSelect) return;

                const selectedEspecie = document.querySelector('input[name="especie"]:checked')?.value;
                let key = '';

                // Map frontend values to JSON keys
                if (selectedEspecie === 'Cachorro') key = 'Canino';
                else if (selectedEspecie === 'Gato') key = 'Felina';

                // Store current selection to restore if possible
                const currentVal = racaSelect.getValue();

                racaSelect.clear();
                racaSelect.clearOptions();
                racaSelect.addOption({value: 'SRD', text: 'SRD (Sem Raça Definida)'}); // Always available

                if (key && racasData[key]) {
                    racasData[key].forEach(raca => {
                        racaSelect.addOption({value: raca, text: raca});
                    });
                    racaSelect.enable();
                } else if (selectedEspecie === 'Outro') {
                     racaSelect.enable(); // Allow custom typing for 'Outro'
                }
                
                // Restore val if it exists in new options or add it if it was custom
                if (currentVal) {
                    racaSelect.addOption({value: currentVal, text: currentVal});
                    racaSelect.setValue(currentVal);
                }
            }
        });
    </script>
<?= $this->endSection() ?>
