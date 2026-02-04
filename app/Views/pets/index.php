<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Gerenciar Pets<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="animate-enter">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 animate-enter">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="dog" class="w-8 h-8 text-brand-500"></i>
                    Gerenciar Pets
                </h1>
                <p class="text-slate-500">Consulte, edite ou cadastre novos pacientes.</p>
            </div>
            <a href="<?= base_url('pets/novo') ?>" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-brand-500/20 transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Novo Pet
            </a>
        </div>

        <!-- Search Bar -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6 animate-enter" style="animation-delay: 0.1s">
            <form action="<?= base_url('pets') ?>" method="GET" class="relative">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"></i>
                <input type="text" name="search" value="<?= $search ?? '' ?>" 
                    class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400"
                    placeholder="Busque por Nome do Pet, Tutor ou Raça...">
            </form>
        </div>

        <!-- Pets List -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden animate-enter" style="animation-delay: 0.2s">
            
            <!-- Desktop Table (hidden on mobile) -->
            <div class="hidden sm:block">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50 text-xs uppercase text-slate-500 font-bold tracking-wider">
                            <th class="p-5">Pet / Raça</th>
                            <th class="p-5">Tutor Responsável</th>
                            <th class="p-5 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(empty($pets)): ?>
                            <tr>
                                <td colspan="3" class="p-12 text-center text-slate-400">
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-dashed border-slate-200">
                                        <i data-lucide="search-x" class="w-8 h-8 text-slate-300"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-slate-900">Nenhum pet encontrado</h3>
                                    <p class="text-slate-500">Tente buscar por outro termo ou cadastre um novo pet.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($pets as $pet): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="p-5">
                                        <div class="flex items-center gap-4">
                                            <div class="w-12 h-12 rounded-xl bg-<?= isset($pet['especie']) && $pet['especie'] == 'Gato' ? 'orange' : 'blue' ?>-50 text-<?= isset($pet['especie']) && $pet['especie'] == 'Gato' ? 'orange' : 'blue' ?>-500 flex items-center justify-center text-lg font-bold shrink-0">
                                                <?php if(isset($pet['especie']) && $pet['especie'] == 'Gato'): ?>
                                                    <i data-lucide="cat" class="w-6 h-6"></i>
                                                <?php else: ?>
                                                    <i data-lucide="dog" class="w-6 h-6"></i>
                                                <?php endif; ?>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-slate-800 text-lg">
                                                    <a href="<?= base_url('pets/ver/' . $pet['id']) ?>" class="hover:text-brand-600 transition-colors">
                                                        <?= $pet['nome'] ?>
                                                    </a>
                                                </h3>
                                                <div class="flex items-center gap-2 text-sm text-slate-500">
                                                    <span class="bg-slate-100 px-2 py-0.5 rounded-full text-xs font-medium">
                                                        <?= $pet['raca'] ?: 'SRD' ?>
                                                    </span>
                                                    <span><?= $pet['sexo'] == 'M' ? 'Macho' : 'Fêmea' ?></span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-5">
                                        <div class="flex items-center gap-2 text-slate-700">
                                            <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                                                <i data-lucide="user" class="w-4 h-4"></i>
                                            </div>
                                            <div>
                                                <a href="<?= base_url('tutores/ver/' . $pet['tutor_id']) ?>" class="font-medium hover:text-brand-600 transition-colors">
                                                    <?= $pet['tutor_nome'] ?>
                                                </a>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-5">
                                        <div class="flex justify-end gap-2 transition-opacity">
                                            <a href="<?= base_url('pets/ver/' . $pet['id']) ?>" class="p-2 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Ver Detalhes">
                                                <i data-lucide="eye" class="w-5 h-5"></i>
                                            </a>
                                            <a href="<?= base_url('pets/editar/' . $pet['id']) ?>" class="p-2 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-colors" title="Editar">
                                                <i data-lucide="edit-2" class="w-5 h-5"></i>
                                            </a>
                                            <a href="<?= base_url('agenda/novo?pet=' . $pet['id']) ?>" class="p-2 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-colors" title="Novo Agendamento">
                                                <i data-lucide="calendar-plus" class="w-5 h-5"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile Cards (hidden on desktop) -->
            <div class="sm:hidden p-4 space-y-3">
                <?php if(empty($pets)): ?>
                    <div class="p-8 text-center text-slate-400">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-dashed border-slate-200">
                            <i data-lucide="search-x" class="w-8 h-8 text-slate-300"></i>
                        </div>
                        <h3 class="text-lg font-medium text-slate-900">Nenhum pet encontrado</h3>
                        <p class="text-slate-500">Tente buscar por outro termo.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($pets as $pet): ?>
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                            <!-- Pet Info -->
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-12 h-12 rounded-xl bg-<?= isset($pet['especie']) && $pet['especie'] == 'Gato' ? 'orange' : 'blue' ?>-50 text-<?= isset($pet['especie']) && $pet['especie'] == 'Gato' ? 'orange' : 'blue' ?>-500 flex items-center justify-center shrink-0">
                                    <?php if(isset($pet['especie']) && $pet['especie'] == 'Gato'): ?>
                                        <i data-lucide="cat" class="w-6 h-6"></i>
                                    <?php else: ?>
                                        <i data-lucide="dog" class="w-6 h-6"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-slate-800 text-lg truncate"><?= $pet['nome'] ?></h3>
                                    <div class="flex items-center gap-2 text-sm text-slate-500">
                                        <span class="bg-slate-200 px-2 py-0.5 rounded-full text-xs font-medium">
                                            <?= $pet['raca'] ?: 'SRD' ?>
                                        </span>
                                        <span><?= $pet['sexo'] == 'M' ? 'Macho' : 'Fêmea' ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Tutor -->
                            <div class="flex items-center gap-2 text-sm text-slate-600 mb-3 pb-3 border-b border-slate-200">
                                <i data-lucide="user" class="w-4 h-4 text-slate-400"></i>
                                <span><?= $pet['tutor_nome'] ?></span>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex gap-2">
                                <a href="<?= base_url('pets/ver/' . $pet['id']) ?>" class="flex-1 flex items-center justify-center gap-1 py-2 rounded-lg bg-blue-100 text-blue-600 text-xs font-medium hover:bg-blue-200 transition-colors">
                                    <i data-lucide="eye" class="w-3.5 h-3.5"></i>
                                    Ver
                                </a>
                                <a href="<?= base_url('pets/editar/' . $pet['id']) ?>" class="flex-1 flex items-center justify-center gap-1 py-2 rounded-lg bg-brand-100 text-brand-600 text-xs font-medium hover:bg-brand-200 transition-colors">
                                    <i data-lucide="edit-2" class="w-3.5 h-3.5"></i>
                                    Editar
                                </a>
                                <a href="<?= base_url('agenda/novo?pet=' . $pet['id']) ?>" class="flex-1 flex items-center justify-center gap-1 py-2 rounded-lg bg-brand-500 text-white text-xs font-medium hover:bg-brand-600 transition-colors">
                                    <i data-lucide="calendar-plus" class="w-3.5 h-3.5"></i>
                                    Agendar
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Pagination -->
             <?php if ($pager): ?>
                <div class="p-5 border-t border-slate-100">
                    <?= $pager->links('default', 'tailwind_full') ?>
                </div>
            <?php endif; ?>
        </div>
        
</div>
<?php /* Tags de fechamento main/div removidas pelo padrão main.php */ ?>
<?= view('components/modal_confirm') ?>
<?= $this->endSection() ?>
