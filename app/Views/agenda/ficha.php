<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Ficha de Atendimento<?= $this->endSection() ?>

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
                <h1 class="text-3xl font-bold text-slate-900">Ficha de Atendimento</h1>
                <p class="text-slate-500 mt-1">Registre os detalhes técnicos e observações do serviço.</p>
            </div>

            <!-- Main Form -->
            <form action="<?= base_url('agenda/salvarFicha') ?>" method="POST" class="space-y-8">
                <input type="hidden" name="agendamento_id" value="<?= $agendamento['id'] ?>">
                <?= csrf_field() ?>

                <!-- Info Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex flex-col md:flex-row gap-6">
                    <div class="flex-1 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-violet-100 flex items-center justify-center text-violet-600 shrink-0">
                            <i data-lucide="dog" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-800"><?= $agendamento['pet_nome'] ?></h2>
                            <p class="text-sm text-slate-500">
                                <?= $agendamento['raca'] ?> • <?= $agendamento['sexo'] == 'M' ? 'Macho' : 'Fêmea' ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="w-px bg-slate-100 hidden md:block"></div>

                    <div class="flex-1 flex items-start gap-4">
                        <div class="w-12 h-12 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 shrink-0">
                            <i data-lucide="user" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <h2 class="text-lg font-bold text-slate-800"><?= $agendamento['tutor_nome'] ?></h2>
                            <p class="text-sm text-slate-500"><?= $agendamento['tutor_telefone'] ?></p>
                        </div>
                    </div>
                </div>

                <!-- 1. Avaliação Visual -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="eye" class="w-5 h-5 text-brand-500"></i>
                            Avaliação Visual
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php 
                        // Preparar array de IDs marcados para fácil verificação
                        $marcadasIds = array_column($obs_marcadas, 'observacao_id');
                        // Buscar detalhes de 'Outros' (ID 7)
                        $outrosDetalhes = '';
                        foreach($obs_marcadas as $m) {
                            if($m['observacao_id'] == 7) $outrosDetalhes = $m['outros_detalhes'];
                        }
                        ?>

                        <?php foreach($obs_visuais as $obs): ?>
                            <label class="group relative flex items-center gap-3 p-4 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-brand-500 hover:shadow-md hover:shadow-brand-500/5 transition-all">
                                <input type="checkbox" name="observacao_visual[<?= $obs['id'] ?>]" value="1" 
                                    <?= in_array($obs['id'], $marcadasIds) ? 'checked' : '' ?>
                                    class="peer sr-only">
                                
                                <!-- Custom Checkbox UI -->
                                <div class="w-5 h-5 rounded border border-slate-300 peer-checked:bg-brand-500 peer-checked:border-brand-500 flex items-center justify-center text-white transition-colors">
                                    <i data-lucide="check" class="w-3.5 h-3.5 opacity-0 peer-checked:opacity-100"></i>
                                </div>

                                <span class="font-semibold text-slate-700 peer-checked:text-brand-600"><?= $obs['descricao'] ?></span>
                                
                                <!-- Highlight Border -->
                                <div class="absolute inset-0 rounded-xl border-2 border-transparent peer-checked:border-brand-500 pointer-events-none transition-colors"></div>
                            </label>
                        <?php endforeach; ?>
                        
                        <!-- Campo condicional para Outros -->
                        <div class="col-span-2 md:col-span-4 mt-2">
                            <label class="block text-sm font-medium text-slate-600 mb-1">Detalhes da Observação (se 'Outros' selecionado)</label>
                            <input type="text" name="observacao_visual_outros" value="<?= $outrosDetalhes ?>" 
                                class="w-full p-2.5 border border-slate-200 rounded-lg text-sm focus:border-brand-500 focus:ring-1 focus:ring-brand-500" 
                                placeholder="Especifique...">
                        </div>
                    </div>
                </div>

                <!-- 2. Serviços Realizados -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="scissors" class="w-5 h-5 text-brand-500"></i>
                            Serviços Realizados
                        </h3>
                    </div>
                    <div class="p-6 grid grid-cols-2 md:grid-cols-4 gap-4">
                        <?php 
                        $realizadosIds = array_column($servicos_realizados, 'servico_id');
                        ?>
                        <?php foreach($servicos as $servico): ?>
                            <label class="group relative flex items-center gap-3 p-4 bg-white border border-slate-200 rounded-xl cursor-pointer hover:border-brand-500 hover:shadow-md hover:shadow-brand-500/5 transition-all">
                                <input type="checkbox" name="servicos_realizados[]" value="<?= $servico['id'] ?>"
                                    <?= in_array($servico['id'], $realizadosIds) ? 'checked' : '' ?>
                                    class="peer sr-only">
                                
                                <!-- Custom Checkbox UI -->
                                <div class="w-5 h-5 rounded border border-slate-300 peer-checked:bg-brand-500 peer-checked:border-brand-500 flex items-center justify-center text-white transition-colors">
                                    <i data-lucide="check" class="w-3.5 h-3.5 opacity-0 peer-checked:opacity-100"></i>
                                </div>

                                <span class="font-semibold text-slate-700 peer-checked:text-brand-600"><?= $servico['nome'] ?></span>
                                
                                <!-- Highlight Border -->
                                <div class="absolute inset-0 rounded-xl border-2 border-transparent peer-checked:border-brand-500 pointer-events-none transition-colors"></div>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- 3. Saúde e Detalhes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <i data-lucide="activity" class="w-5 h-5 text-rose-500"></i>
                                Saúde
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Doença Pré-Existente</label>
                                <input type="text" name="doenca_pre_existente" value="<?= $ficha['doenca_pre_existente'] ?? '' ?>" class="w-full p-2.5 border border-slate-200 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Problemas de Pele</label>
                                <input type="text" name="doenca_pele" value="<?= $ficha['doenca_pele'] ?? '' ?>" class="w-full p-2.5 border border-slate-200 rounded-lg text-sm">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Problemas de Ouvido</label>
                                <input type="text" name="doenca_ouvido" value="<?= $ficha['doenca_ouvido'] ?? '' ?>" class="w-full p-2.5 border border-slate-200 rounded-lg text-sm">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                                <i data-lucide="file-text" class="w-5 h-5 text-blue-500"></i>
                                Detalhes
                            </h3>
                        </div>
                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Altura dos Pelos</label>
                                <input type="text" name="altura_pelos" value="<?= $ficha['altura_pelos'] ?? '' ?>" class="w-full p-2.5 border border-slate-200 rounded-lg text-sm" placeholder="Ex: Baixo, Médio...">
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Comportamento</label>
                                <textarea name="comportamento_pet" rows="2" class="w-full p-2.5 border border-slate-200 rounded-lg text-sm"><?= $ficha['comportamento_pet'] ?? '' ?></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase text-slate-400 mb-1">Observações Gerais</label>
                                <textarea name="observacoes" rows="2" class="w-full p-2.5 border border-slate-200 rounded-lg text-sm"><?= $ficha['observacoes'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-end gap-4">
                    <button type="submit" class="px-8 py-3 rounded-xl bg-brand-500 text-white font-bold hover:bg-brand-600 shadow-lg shadow-brand-500/20 hover:shadow-brand-500/30 transition-all flex items-center gap-2">
                        <i data-lucide="check-check" class="w-5 h-5"></i>
                        Salvar e Finalizar
                    </button>
                </div>

            </form>
        </div>
    </main>
</div>
<?= $this->endSection() ?>
