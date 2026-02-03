<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Novo Agendamento<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex min-h-screen bg-slate-50">
    <?= view('components/sidebar') ?>

    <main class="flex-1 md:ml-64 p-4 md:p-8">
        <div class="w-full animate-enter">
            <!-- Header -->
            <div class="mb-8">
                <a href="<?= base_url('agenda') ?>" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-brand-600 mb-4 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Voltar para Agenda
                </a>
                <h1 class="text-3xl font-bold text-slate-900">Novo Agendamento</h1>
                <p class="text-slate-500 mt-1">Preencha os dados abaixo para marcar um horário.</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <form action="<?= base_url('agenda/salvar') ?>" method="POST" class="p-6 md:p-8 space-y-8">
                    <?= csrf_field() ?>

                    <!-- 1. Seleção do Pet -->
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i data-lucide="dog" class="w-5 h-5 text-brand-500"></i>
                            1. Selecione o Pet
                        </h2>
                        
                        <!-- Using Tom Select for Searchable Dropdown -->
                        <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.css" rel="stylesheet">
                        <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
                        <style>
                            .ts-control { border-radius: 0.75rem; padding: 1rem; border-color: #e2e8f0; }
                            .ts-wrapper.focus .ts-control { border-color: #8b5cf6; box-shadow: 0 0 0 2px #ddd6fe; }
                            .ts-dropdown { border-radius: 0.75rem; margin-top: 8px; box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1); border: 1px solid #e2e8f0; }
                        </style>

                        <div>
                            <select name="pet_id" id="pet_id" required placeholder="Digite o nome do pet ou tutor...">
                                <option value="">Digite para buscar...</option>
                                <?php foreach ($pets as $pet): ?>
                                    <option value="<?= $pet['id'] ?>" <?= $preselected_pet_id == $pet['id'] ? 'selected' : '' ?>>
                                        <?= $pet['nome'] ?> | Tutor: <?= $pet['tutor_nome'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="mt-2 text-right">
                                <a href="<?= base_url('agenda/cadastro-rapido') ?>" class="text-sm font-medium text-brand-600 hover:text-brand-700 hover:underline flex items-center justify-end gap-1">
                                    <i data-lucide="zap" class="w-3 h-3"></i>
                                    Não encontrou? Cadastre Tutor e Pet rapido aqui
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Data e Hora -->
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i data-lucide="calendar" class="w-5 h-5 text-brand-500"></i>
                            2. Escolha Data e Hora
                        </h2>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Data do Atendimento</label>
                                <input type="date" name="data" id="data" required 
                                    min="<?= date('Y-m-d') ?>" value="<?= date('Y-m-d') ?>"
                                    class="w-full p-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 focus:border-brand-500 transition-all">
                            </div>

                            <div class="flex-1">
                                <label class="block text-sm font-medium text-slate-600 mb-2">Horários Disponíveis</label>
                                <div id="slots-container" class="grid grid-cols-4 gap-2">
                                    <div class="col-span-4 py-8 text-center text-slate-400 text-sm bg-slate-50 rounded-lg border border-dashed border-slate-200">
                                        Selecione uma data para ver os horários
                                    </div>
                                </div>
                                <input type="hidden" name="horario" id="horario_input" required>
                                <p id="horario-error" class="hidden text-red-500 text-xs mt-2 font-medium">Por favor, selecione um horário.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 3. Serviços -->
                    <div>
                        <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i data-lucide="scissors" class="w-5 h-5 text-brand-500"></i>
                            3. Serviços
                        </h2>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                            <?php foreach ($servicos as $servico): ?>
                                <label class="group relative flex items-start gap-3 p-4 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-brand-500 hover:shadow-md hover:shadow-brand-500/5 transition-all">
                                    <input type="checkbox" name="servicos[]" value="<?= $servico['id'] ?>" class="peer sr-only">
                                    
                                    <!-- Custom Checkbox UI -->
                                    <div class="w-5 h-5 mt-0.5 rounded border border-slate-300 peer-checked:bg-brand-500 peer-checked:border-brand-500 flex items-center justify-center text-white transition-colors">
                                        <i data-lucide="check" class="w-3.5 h-3.5 opacity-0 peer-checked:opacity-100"></i>
                                    </div>
                                    
                                    <div>
                                        <span class="block font-semibold text-slate-700 peer-checked:text-brand-600"><?= $servico['nome'] ?></span>
                                        <?php if($servico['preco']): ?>
                                            <span class="text-sm text-slate-400">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <!-- Highlight Border -->
                                    <div class="absolute inset-0 rounded-xl border-2 border-transparent peer-checked:border-brand-500 pointer-events-none transition-colors"></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- 4. Recorrência -->
                    <div class="bg-slate-50 p-6 rounded-2xl border border-dashed border-slate-200 mt-4">
                        <h3 class="text-sm font-bold text-slate-400 uppercase tracking-widest mb-4 flex items-center gap-2">
                            <i data-lucide="refresh-cw" class="w-4 h-4 text-brand-500"></i>
                            Agendamento Recorrente
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-600 mb-2">Frequência</label>
                                <select name="recorrencia_tipo" onchange="toggleRecorrencia(this.value)" class="w-full p-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none transition-all">
                                    <option value="unico" selected>Não repetir (Único)</option>
                                    <option value="semanal">Semanal (Todo mesmo dia)</option>
                                    <option value="quinzenal">Quinzenal (A cada 15 dias)</option>
                                    <option value="mensal">Mensal (Mesmo dia todo mês)</option>
                                </select>
                            </div>
                            <div id="repcet-container" class="opacity-40 pointer-events-none transition-all duration-300">
                                <label class="block text-sm font-medium text-slate-600 mb-2">Repetir quantas vezes?</label>
                                <div class="flex items-center gap-3">
                                    <input type="number" name="recorrencia_repeticoes" value="1" min="1" max="12" 
                                        class="w-full p-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none transition-all">
                                    <span class="text-xs text-slate-400 font-medium">vezes</span>
                                </div>
                                <p class="text-[10px] text-slate-400 mt-1 italic">* Máximo de 12 repetições por vez.</p>
                            </div>
                        </div>
                    </div>

                    <!-- 5. Detalhes Finais -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pt-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">Transporte</label>
                            <select name="transporte" class="w-full p-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none transition-all">
                                <option value="Petshop busca e entrega">Petshop busca e entrega</option>
                                <option value="Petshop busca, tutor retira">Petshop busca, tutor retira</option>
                                <option value="Tutor leva, Petshop entrega">Tutor leva, Petshop entrega</option>
                                <option value="Tutor leva e retira" selected>Tutor leva e retira</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-600 mb-2">Observações</label>
                            <textarea name="observacoes" rows="1" placeholder="Ex: Alérgico a perfume, Cuidado com a pata..." 
                                class="w-full p-3 bg-white border border-slate-200 rounded-xl focus:ring-2 focus:ring-brand-500 outline-none transition-all resize-none"></textarea>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-50">
                        <a href="<?= base_url('agenda') ?>" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-colors">
                            Cancelar
                        </a>
                        <?= view('components/btn_salvar', ['label' => 'Confirmar Agendamento']) ?>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

</script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Init Tom Select
        new TomSelect("#pet_id",{
            create: false,
            sortField: {
                field: "text",
                direction: "asc"
            }
        });

        // Date & Slots Logic
        const dataInput = document.getElementById('data');
        const slotsContainer = document.getElementById('slots-container');
        const horarioInput = document.getElementById('horario_input');
        
        // Fetch slots
        async function fetchHorarios(date) {
            slotsContainer.innerHTML = '<div class="col-span-4 py-8 flex justify-center"><i data-lucide="loader-2" class="w-6 h-6 animate-spin text-brand-500"></i></div>';
            lucide.createIcons();

            try {
                const response = await fetch(`<?= base_url('agenda/horarios') ?>?data=${date}`);
                const slots = await response.json();
                
                slotsContainer.innerHTML = '';
                
                if(slots.length === 0) {
                    slotsContainer.innerHTML = '<div class="col-span-4 text-center text-red-500 py-4 bg-red-50 rounded-lg text-sm">Nenhum horário disponível para esta data.</div>';
                    return;
                }

                slots.forEach(slot => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.textContent = slot.horario;
                    
                    if (slot.disponivel) {
                        // Horário disponível - clicável
                        btn.className = `p-2 rounded-lg border text-sm font-medium transition-all hover:border-brand-500 hover:text-brand-600 ${horarioInput.value === slot.horario ? 'bg-brand-500 text-white border-brand-500 shadow-md ring-2 ring-brand-200' : 'bg-white border-slate-200 text-slate-600'}`;
                        
                        btn.addEventListener('click', () => {
                            // Reset apenas botões disponíveis
                            Array.from(slotsContainer.children).forEach(b => {
                                if (!b.disabled) {
                                    b.className = 'p-2 rounded-lg border border-slate-200 text-slate-600 text-sm font-medium transition-all hover:border-brand-500 hover:text-brand-600 bg-white';
                                }
                            });
                            
                            // Select clicked
                            btn.className = 'p-2 rounded-lg border border-brand-500 bg-brand-500 text-white text-sm font-medium shadow-md ring-2 ring-brand-200 transition-all scale-105';
                            horarioInput.value = slot.horario;
                        });
                    } else {
                        // Horário ocupado - desabilitado, cinza
                        btn.disabled = true;
                        btn.className = 'p-2 rounded-lg border border-slate-100 bg-slate-100 text-slate-400 text-sm font-medium cursor-not-allowed line-through';
                        btn.title = 'Horário ocupado';
                    }

                    slotsContainer.appendChild(btn);
                });

            } catch (e) {
                console.error(e);
                slotsContainer.innerHTML = '<div class="col-span-4 text-center text-red-400 text-sm">Erro ao carregar horários.</div>';
            }
        }

        dataInput.addEventListener('change', (e) => {
            if(e.target.value) {
                horarioInput.value = ''; // Reset selection
                fetchHorarios(e.target.value);
            }
        });

        // Initial fetch
        if(dataInput.value) {
            fetchHorarios(dataInput.value);
        }
    });

    // Função para alternar campos de recorrência no escopo global
    function toggleRecorrencia(tipo) {
        const container = document.getElementById('repcet-container');
        const input = container.querySelector('input');
        
        if (tipo === 'unico') {
            container.classList.add('opacity-40', 'pointer-events-none');
            input.value = 1;
        } else {
            container.classList.remove('opacity-40', 'pointer-events-none');
            input.value = 4; // Sugestão padrão (ex: 4 semanas)
            input.focus();
        }
    }
</script>
<?= $this->endSection() ?>
