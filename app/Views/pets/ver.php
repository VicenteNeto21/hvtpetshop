<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Detalhes do Pet<?= $this->endSection() ?>

<?= $this->section('content') ?>
        <!-- Breadcrumb & Header -->
        <div class="mb-8 animate-enter">
            <a href="<?= base_url('pets') ?>" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-brand-600 mb-4 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Voltar para lista
            </a>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-3xl font-bold shadow-sm">
                        <?= mb_substr($pet['nome'], 0, 1) ?>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900"><?= $pet['nome'] ?></h1>
                        <p class="text-slate-500 font-medium">Paciente #<?= str_pad($pet['id'], 4, '0', STR_PAD_LEFT) ?></p>
                    </div>
                </div>

                <div class="flex gap-3">
                     <a href="<?= base_url('pets/editar/' . $pet['id']) ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-white hover:border-slate-300 transition-all flex items-center gap-2">
                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                        Editar
                    </a>
                    <a href="<?= base_url('agenda/novo?pet=' . $pet['id']) ?>" class="px-5 py-2.5 rounded-xl bg-brand-500 text-white font-medium hover:bg-brand-600 shadow-lg shadow-brand-500/20 transition-all flex items-center gap-2">
                        <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                        Novo Agendamento
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-enter" style="animation-delay: 0.1s">
            <!-- Left Column: Pet Info & Tutor -->
            <div class="space-y-6">
                <!-- Pet Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5">
                        <i data-lucide="dog" class="w-32 h-32 text-slate-900"></i>
                    </div>
                    
                    <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i data-lucide="info" class="w-5 h-5 text-brand-500"></i>
                        Ficha do Animal
                    </h2>

                    <div class="space-y-4 relative z-10">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Espécie</label>
                                <p class="text-slate-700 font-medium"><?= $pet['especie'] ?></p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Raça</label>
                                <p class="text-slate-700 font-medium"><?= $pet['raca'] ?></p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Sexo</label>
                                <p class="text-slate-700 font-medium"><?= $pet['sexo'] == 'M' ? 'Macho' : 'Fêmea' ?></p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Cor</label>
                                <p class="text-slate-700 font-medium"><?= ($pet['cor'] ?? false) ?: '-' ?></p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Peso</label>
                                <p class="text-slate-700 font-medium"><?= ($pet['peso'] ?? false) ? number_format($pet['peso'], 2) . ' kg' : '-' ?></p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Nascimento</label>
                                <p class="text-slate-700 font-medium">
                                    <?php 
                                        $nasc = $pet['nascimento'] ?? null;
                                        if ($nasc && $nasc != '0000-00-00' && strtotime($nasc) > 0) {
                                            echo date('d/m/Y', strtotime($nasc));
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <?php if($pet['observacoes']): ?>
                            <div class="pt-4 border-t border-slate-50">
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Observações</label>
                                <p class="text-sm text-slate-600 mt-1 bg-slate-50 p-3 rounded-lg border border-slate-100">
                                    <?= nl2br($pet['observacoes']) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tutor Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i data-lucide="user" class="w-5 h-5 text-brand-500"></i>
                        Tutor Responsável
                    </h2>
                    
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                             <i data-lucide="user" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800"><?= $pet['tutor_nome'] ?></p>
                            <p class="text-xs text-slate-500">ID: <?= $pet['tutor_id'] ?></p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                         <div class="flex items-center gap-3 text-sm text-slate-600 bg-slate-50 p-3 rounded-lg">
                            <i data-lucide="phone" class="w-4 h-4 text-slate-400"></i>
                            <?= $pet['tutor_telefone'] ?: 'Não informado' ?>
                        </div>
                        <a href="<?= base_url('tutores/editar/' . $pet['tutor_id']) ?>" class="flex items-center justify-center gap-2 w-full py-2 text-sm font-medium text-brand-600 border border-brand-200 rounded-lg hover:bg-brand-50 transition-colors">
                            Ver Cadastro Completo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Column: Timeline/History -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 min-h-[500px]">
                     <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="history" class="w-5 h-5 text-brand-500"></i>
                            Histórico Clínico
                        </h2>
                        <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2 py-1 rounded-full">
                            Últimos 50 registros
                        </span>
                    </div>

                    <?php if(empty($historico)): ?>
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                <i data-lucide="calendar-off" class="w-8 h-8 text-slate-300"></i>
                            </div>
                            <p class="text-slate-600 font-medium">Nenhum histórico encontrado</p>
                            <p class="text-sm text-slate-400 max-w-xs mt-1">Este pet ainda não realizou agendamentos ou atendimentos no sistema.</p>
                            <a href="<?= base_url('agenda/novo?pet=' . $pet['id']) ?>" class="mt-4 text-brand-600 font-medium hover:underline">
                                Agendar Primeiro Serviço
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="relative pl-4 border-l-2 border-slate-100 space-y-8 ml-2">
                            <?php foreach($historico as $item): ?>
                                <div class="relative">
                                    <div class="absolute -left-[25px] top-0 w-4 h-4 rounded-full border-2 border-white 
                                        <?= $item['status'] == 'Finalizado' ? 'bg-green-500 ring-4 ring-green-50' : 
                                           ($item['status'] == 'Cancelado' ? 'bg-red-400' : 'bg-brand-500') ?>">
                                    </div>
                                    
                                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 hover:border-brand-200 transition-colors">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                                    <?= $item['servico_nome'] ?>
                                                    <?php if($item['status'] == 'Finalizado'): ?>
                                                        <i data-lucide="check-circle-2" class="w-3 h-3 text-green-500"></i>
                                                    <?php endif; ?>
                                                </h3>
                                                <p class="text-xs text-slate-500 flex items-center gap-1 mt-1">
                                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                                    <?= date('d/m/Y \à\s H:i', strtotime($item['data_hora'])) ?>
                                                </p>
                                            </div>
                                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full
                                                <?= $item['status'] == 'Finalizado' ? 'bg-green-100 text-green-700' : 
                                                   ($item['status'] == 'Cancelado' ? 'bg-red-100 text-red-700' : 'bg-brand-100 text-brand-700') ?>">
                                                <?= $item['status'] ?>
                                            </span>
                                        </div>
                                        
                                        <?php if($item['observacoes']): ?>
                                            <p class="text-sm text-slate-600 italic bg-white p-2 rounded border border-slate-200/50 mt-2">
                                                “<?= $item['observacoes'] ?>”
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="mt-3 flex gap-2">
                                            <a href="#" class="text-xs font-medium text-slate-400 hover:text-brand-600 flex items-center gap-1 transition-colors">
                                                <i data-lucide="file-text" class="w-3 h-3"></i>
                                                Ver Detalhes
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>
<?= $this->endSection() ?>
