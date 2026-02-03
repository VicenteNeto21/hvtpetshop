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
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 flex flex-col lg:flex-row gap-8 items-center relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-brand-50 rounded-bl-full -mr-8 -mt-8 opacity-50"></div>
                    
                    <div class="flex-1 flex flex-col md:flex-row items-center md:items-start gap-6 relative z-10 w-full">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-600 shrink-0">
                            <i data-lucide="dog" class="w-8 h-8"></i>
                        </div>
                        <div class="text-center md:text-left flex-1">
                            <div class="flex flex-col md:flex-row md:items-center gap-2 mb-2">
                                <h2 class="text-2xl font-black text-slate-900"><?= $agendamento['pet_nome'] ?></h2>
                                <span class="px-3 py-1 bg-violet-100 text-violet-700 rounded-full text-xs font-bold uppercase tracking-wider">
                                    <?= $agendamento['especie'] ?>
                                </span>
                            </div>
                            <p class="text-slate-500 font-medium">
                                <?= $agendamento['raca'] ?> • <?= $agendamento['sexo'] == 'M' ? '♂ Macho' : '♀ Fêmea' ?>
                            </p>
                            <div class="mt-4 flex flex-wrap gap-2 justify-center md:justify-start">
                                <?php 
                                $servicosArr = explode(', ', $agendamento['servicos_previstos']);
                                foreach($servicosArr as $s): ?>
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-brand-500 text-white rounded-lg text-xs font-bold shadow-sm shadow-brand-500/20">
                                        <i data-lucide="check" class="w-3 h-3"></i>
                                        <?= $s ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="w-px h-24 bg-slate-100 hidden lg:block"></div>

                    <div class="flex-1 flex flex-col md:flex-row items-center md:items-start gap-6 relative z-10 w-full">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-600 shrink-0">
                            <i data-lucide="user" class="w-8 h-8"></i>
                        </div>
                        <div class="text-center md:text-left">
                            <h2 class="text-xl font-bold text-slate-800"><?= $agendamento['tutor_nome'] ?></h2>
                            <p class="text-slate-500 flex items-center justify-center md:justify-start gap-2 mt-1">
                                <i data-lucide="phone" class="w-4 h-4 text-slate-400"></i>
                                <?= $agendamento['tutor_telefone'] ?>
                            </p>
                            <?php if($agendamento['transporte'] && $agendamento['transporte'] != 'Tutor leva e retira'): ?>
                                <div class="mt-2 inline-flex items-center gap-2 px-3 py-1.5 bg-rose-50 text-rose-600 rounded-full text-xs font-bold border border-rose-100">
                                    <i data-lucide="truck" class="w-3.5 h-3.5"></i>
                                    <?= $agendamento['transporte'] ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- 1. Avaliação Visual -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center border border-slate-200">
                                <i data-lucide="eye" class="w-5 h-5"></i>
                            </div>
                            Avaliação Visual
                        </h3>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Estado Geral</span>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                            <?php 
                            $marcadasIds = array_column($obs_marcadas, 'observacao_id');
                            $outrosDetalhes = '';
                            foreach($obs_marcadas as $m) {
                                if($m['observacao_id'] == 7) $outrosDetalhes = $m['outros_detalhes'];
                            }
                            ?>

                            <?php foreach($obs_visuais as $obs): ?>
                                <label class="group relative flex items-center gap-4 p-5 bg-white border-2 border-slate-100 rounded-2xl cursor-pointer hover:border-brand-500 hover:bg-brand-50/10 transition-all duration-300">
                                    <input type="checkbox" name="observacao_visual[<?= $obs['id'] ?>]" value="1" 
                                        <?= in_array($obs['id'], $marcadasIds) ? 'checked' : '' ?>
                                        class="peer sr-only">
                                    
                                    <div class="w-6 h-6 rounded-lg border-2 border-slate-200 peer-checked:bg-brand-500 peer-checked:border-brand-500 flex items-center justify-center text-white transition-all transform peer-checked:rotate-0 -rotate-12">
                                        <i data-lucide="check" class="w-4 h-4 opacity-0 peer-checked:opacity-100"></i>
                                    </div>

                                    <span class="font-bold text-slate-600 peer-checked:text-brand-600 transition-colors"><?= $obs['descricao'] ?></span>
                                    
                                    <div class="absolute inset-0 rounded-2xl border-2 border-transparent peer-checked:border-brand-500/30 pointer-events-none transition-all"></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-8 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                            <label class="block text-xs font-black text-slate-400 uppercase tracking-widest mb-3">Detalhes Adicionais (Outros)</label>
                            <textarea name="observacao_visual_outros" rows="2" 
                                class="w-full p-4 bg-white border border-slate-200 rounded-xl text-sm focus:border-brand-500 focus:ring-4 focus:ring-brand-500/5 outline-none transition-all" 
                                placeholder="Especifique qualquer outra observação importante..."><?= $outrosDetalhes ?></textarea>
                        </div>
                    </div>
                </div>

                <!-- 2. Serviços Realizados -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center border border-slate-200">
                                <i data-lucide="scissors" class="w-5 h-5"></i>
                            </div>
                            Serviços Realizados
                        </h3>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-widest">Execução</span>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                            <?php 
                            $realizadosIds = array_column($servicos_realizados, 'servico_id');
                            ?>
                            <?php foreach($servicos as $servico): ?>
                                <label class="group relative flex items-center gap-4 p-5 bg-white border-2 border-slate-100 rounded-2xl cursor-pointer hover:border-brand-500 hover:bg-brand-50/10 transition-all duration-300">
                                    <input type="checkbox" name="servicos_realizados[]" value="<?= $servico['id'] ?>"
                                        <?= in_array($servico['id'], $realizadosIds) ? 'checked' : '' ?>
                                        class="peer sr-only">
                                    
                                    <div class="w-6 h-6 rounded-lg border-2 border-slate-200 peer-checked:bg-brand-500 peer-checked:border-brand-500 flex items-center justify-center text-white transition-all">
                                        <i data-lucide="check" class="w-4 h-4 opacity-0 peer-checked:opacity-100"></i>
                                    </div>

                                    <span class="font-bold text-slate-600 peer-checked:text-brand-600 transition-colors"><?= $servico['nome'] ?></span>
                                    
                                    <div class="absolute inset-0 rounded-2xl border-2 border-transparent peer-checked:border-brand-500/30 pointer-events-none transition-all"></div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 3. Saúde e Detalhes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center border border-slate-200">
                                    <i data-lucide="activity" class="w-5 h-5"></i>
                                </div>
                                Saúde
                            </h3>
                        </div>
                        <div class="p-8 space-y-6">
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Doença Pré-Existente</label>
                                <input type="text" name="doenca_pre_existente" value="<?= $ficha['doenca_pre_existente'] ?? '' ?>" 
                                    class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:bg-white focus:border-brand-500 transition-all outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Problemas de Pele</label>
                                <input type="text" name="doenca_pele" value="<?= $ficha['doenca_pele'] ?? '' ?>" 
                                    class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:bg-white focus:border-brand-500 transition-all outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Problemas de Ouvido</label>
                                <input type="text" name="doenca_ouvido" value="<?= $ficha['doenca_ouvido'] ?? '' ?>" 
                                    class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:bg-white focus:border-brand-500 transition-all outline-none">
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center border border-slate-200">
                                    <i data-lucide="file-text" class="w-5 h-5"></i>
                                </div>
                                Detalhes Técnicos
                            </h3>
                        </div>
                        <div class="p-8 space-y-6">
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Altura dos Pelos</label>
                                <input type="text" name="altura_pelos" value="<?= $ficha['altura_pelos'] ?? '' ?>" 
                                    class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:bg-white focus:border-brand-500 transition-all outline-none" 
                                    placeholder="Ex: Baixo, Médio...">
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Comportamento</label>
                                <textarea name="comportamento_pet" rows="2" 
                                    class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:bg-white focus:border-brand-500 transition-all outline-none resize-none"><?= $ficha['comportamento_pet'] ?? '' ?></textarea>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-2">Observações Gerais</label>
                                <textarea name="observacoes" rows="2" 
                                    class="w-full p-4 bg-slate-50 border border-slate-200 rounded-2xl text-sm focus:bg-white focus:border-brand-500 transition-all outline-none resize-none"><?= $ficha['observacoes'] ?? '' ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Actions -->
                <div class="flex items-center justify-between p-8 bg-brand-50 rounded-3xl border border-brand-100">
                    <div class="hidden md:block">
                        <p class="text-sm font-bold text-brand-900">Finalização de Atendimento</p>
                        <p class="text-xs text-brand-600">Ao salvar, o status do agendamento será atualizado automaticamente.</p>
                    </div>
                    <?= view('components/btn_salvar', ['label' => 'Salvar e Finalizar']) ?>
                </div>

            </form>
        </div>
    </main>
</div>
<?= $this->endSection() ?>
