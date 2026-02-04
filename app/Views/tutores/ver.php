<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Detalhes do Tutor<?= $this->endSection() ?>

<?= $this->section('content') ?>
        <!-- Breadcrumb & Header -->
        <div class="mb-8 animate-enter">
            <a href="<?= base_url('tutores') ?>" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-brand-600 mb-4 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Voltar para lista
            </a>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-3xl font-bold shadow-sm">
                        <?= mb_substr($tutor['nome'], 0, 1) ?>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900"><?= $tutor['nome'] ?></h1>
                        <p class="text-slate-500 font-medium">Cliente #<?= str_pad($tutor['id'], 4, '0', STR_PAD_LEFT) ?></p>
                    </div>
                </div>

                <div class="flex gap-3">
                     <a href="<?= base_url('tutores/editar/' . $tutor['id']) ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-white hover:border-slate-300 transition-all flex items-center gap-2">
                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                        Editar
                    </a>
                    <a href="<?= base_url('pets/novo?tutor_id=' . $tutor['id']) ?>" class="px-5 py-2.5 rounded-xl bg-brand-500 text-white font-medium hover:bg-brand-600 shadow-lg shadow-brand-500/20 transition-all flex items-center gap-2">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Adicionar Pet
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-enter" style="animation-delay: 0.1s">
            <!-- Left Column: Tutor Info -->
            <div class="space-y-6">
                <!-- Tutor Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5">
                        <i data-lucide="user" class="w-32 h-32 text-slate-900"></i>
                    </div>
                    
                    <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i data-lucide="info" class="w-5 h-5 text-brand-500"></i>
                        Dados Pessoais
                    </h2>

                    <div class="space-y-4 relative z-10">
                        <div class="grid grid-cols-1 gap-4">
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Telefone</label>
                                <div class="flex items-center gap-2 mt-1">
                                    <?php if(!empty($tutor['telefone'])): ?>
                                        <p class="text-slate-700 font-medium"><?= $tutor['telefone'] ?></p>
                                        <?php if($tutor['telefone_is_whatsapp'] == 'Sim'): ?>
                                            <a href="https://wa.me/55<?= preg_replace('/\D/', '', $tutor['telefone']) ?>" target="_blank" class="bg-green-100 hover:bg-green-200 text-green-700 text-xs px-2 py-0.5 rounded-full font-bold flex items-center gap-1 transition-colors">
                                                <i data-lucide="message-circle" class="w-3 h-3"></i> Zap
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <p class="text-slate-400 italic font-normal text-sm">Não informado</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Email</label>
                                <?php if(!empty($tutor['email'])): ?>
                                    <p class="text-slate-700 font-medium truncate" title="<?= $tutor['email'] ?>">
                                        <?= $tutor['email'] ?>
                                    </p>
                                <?php else: ?>
                                    <p class="text-slate-400 italic font-normal text-sm mt-1">Não informado</p>
                                <?php endif; ?>
                            </div>
                            
                            <?php if(!empty($tutor['cidade'])): ?>
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Endereço</label>
                                <p class="text-slate-700 font-medium my-1">
                                    <?= $tutor['rua'] ? $tutor['rua'] . ', ' . $tutor['numero'] . '<br>' : '' ?>
                                    <?= $tutor['bairro'] ? $tutor['bairro'] . '<br>' : '' ?>
                                    <?= $tutor['cidade'] ?> - <?= $tutor['uf'] ?>
                                </p>
                                <?php if($tutor['cep']): ?>
                                    <span class="text-xs text-slate-400">CEP: <?= $tutor['cep'] ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Stats Card (Optional) -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i data-lucide="bar-chart-2" class="w-5 h-5 text-brand-500"></i>
                        Resumo
                    </h2>
                    
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-slate-50 p-4 rounded-xl text-center">
                            <span class="block text-2xl font-bold text-brand-600"><?= count($pets) ?></span>
                            <span class="text-xs text-slate-500 font-medium uppercase">Pets</span>
                        </div>
                        <div class="bg-slate-50 p-4 rounded-xl text-center">
                            <!-- Placeholder for Appointments Count -->
                            <span class="block text-2xl font-bold text-blue-600">-</span>
                            <span class="text-xs text-slate-500 font-medium uppercase">Agendamentos</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Pets List -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 min-h-[500px]">
                     <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="dog" class="w-5 h-5 text-brand-500"></i>
                            Pets Vinculados
                        </h2>
                    </div>

                    <?php if(empty($pets)): ?>
                         <div class="flex flex-col items-center justify-center py-12 text-center h-full">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 border border-dashed border-slate-200">
                                <i data-lucide="dog" class="w-8 h-8 text-slate-300"></i>
                            </div>
                            <h3 class="text-lg font-medium text-slate-900">Nenhum pet cadastrado</h3>
                            <p class="text-slate-500 mb-6 max-w-xs">Este tutor ainda não possui pets vinculados.</p>
                            <a href="<?= base_url('pets/novo?tutor_id='.$tutor['id']) ?>" class="bg-brand-500 text-white px-6 py-2 rounded-xl font-bold shadow-lg shadow-brand-500/20 hover:bg-brand-600 transition-all">
                                Cadastrar Primeiro Pet
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php foreach($pets as $pet): ?>
                                <a href="<?= base_url('pets/ver/'.$pet['id']) ?>" class="group bg-slate-50 hover:bg-white p-4 rounded-2xl border border-slate-100 hover:border-brand-200 hover:shadow-md transition-all flex items-start gap-4">
                                    <div class="w-16 h-16 rounded-xl bg-white shadow-sm flex items-center justify-center shrink-0 text-3xl overflow-hidden border border-slate-100">
                                        <?php if($pet['especie'] == 'Gato'): ?>
                                            <i data-lucide="cat" class="w-8 h-8 text-orange-400"></i>
                                        <?php else: ?>
                                            <i data-lucide="dog" class="w-8 h-8 text-blue-400"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start">
                                            <h4 class="font-bold text-slate-800 text-lg group-hover:text-brand-600 transition-colors truncate"><?= esc($pet['nome']) ?></h4>
                                            <?php if($pet['sexo'] == 'M'): ?>
                                                <i data-lucide="move-up-right" class="w-4 h-4 text-blue-400 shrink-0"></i>
                                            <?php else: ?>
                                                <i data-lucide="move-down-left" class="w-4 h-4 text-pink-400 shrink-0"></i>
                                            <?php endif; ?>
                                        </div>
                                        <p class="text-sm text-slate-500"><?= esc($pet['raca'] ?: 'Raça não informada') ?></p>
                                        
                                        <div class="flex items-center gap-3 mt-3 pt-3 border-t border-slate-200/50 text-xs font-medium text-slate-400">
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="cake" class="w-3 h-3"></i>
                                                <?= $pet['idade'] ?? 0 ?> anos
                                            </span>
                                            <span class="flex items-center gap-1">
                                                <i data-lucide="scale" class="w-3 h-3"></i>
                                                <?= $pet['peso'] ? number_format($pet['peso'], 1).' kg' : '-' ?>
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>
<?= $this->endSection() ?>
