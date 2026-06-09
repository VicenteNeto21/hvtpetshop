<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Tutores<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="animate-enter">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 animate-enter">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Tutores</h1>
                <p class="text-slate-500 mt-1">Gerencie os clientes e seus dados de contato.</p>
            </div>
            <a href="<?= base_url('tutores/novo') ?>" class="px-5 py-2.5 bg-brand-500 text-white font-bold rounded-xl shadow-lg shadow-brand-500/20 hover:bg-brand-600 transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Novo Tutor
            </a>
        </div>

        <!-- Search Bar -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-6 animate-enter" style="animation-delay: 0.1s">
            <form action="<?= base_url('tutores') ?>" method="GET" class="relative">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"></i>
                <input type="text" name="search" value="<?= $search ?? '' ?>" 
                    class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400"
                    placeholder="Buscar por nome ou telefone...">
            </form>
        </div>

        <!-- List -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden animate-enter" style="animation-delay: 0.2s">
            
            <!-- Desktop Table (hidden on mobile) -->
            <div class="hidden sm:block">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="border-b border-slate-100 bg-slate-50/50 text-xs uppercase text-slate-500 font-bold tracking-wider">
                            <th class="p-5">Nome / Contato</th>
                            <th class="p-5">Localização</th>
                            <th class="p-5 text-right">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50">
                        <?php if(empty($tutores)): ?>
                            <tr>
                                <td colspan="3" class="p-12 text-center text-slate-400">
                                    <div class="w-16 h-16 bg-brand-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-brand-100">
                                        <i data-lucide="users" class="w-8 h-8 text-brand-400"></i>
                                    </div>
                                    <h3 class="text-lg font-bold text-slate-900">Nenhum tutor encontrado</h3>
                                    <p class="text-slate-500">Tente buscar por outro termo ou cadastre um novo tutor.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($tutores as $tutor): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="p-5">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-500 shadow-sm shrink-0">
                                                <i data-lucide="user" class="w-5 h-5"></i>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-slate-800">
                                                    <a href="<?= base_url('tutores/ver/' . $tutor['id']) ?>" class="hover:text-brand-600 transition-colors">
                                                        <?= $tutor['nome'] ?>
                                                    </a>
                                                </h3>
                                                <div class="flex items-center gap-2 text-sm text-slate-500 mt-1">
                                                    <?php if(!empty($tutor['telefone'])): ?>
                                                        <?php 
                                                            $numZap = preg_replace('/[^0-9]/', '', $tutor['telefone']);
                                                            if (strlen($numZap) >= 10):
                                                        ?>
                                                            <a href="https://wa.me/55<?= $numZap ?>" target="_blank" class="flex items-center gap-1 bg-green-50 text-green-700 px-1.5 py-0.5 rounded text-xs border border-green-100 hover:bg-green-100 transition-colors font-medium">
                                                                <i data-lucide="message-circle" class="w-3 h-3"></i> <?= $tutor['telefone'] ?>
                                                            </a>
                                                        <?php else: ?>
                                                            <span class="flex items-center gap-1"><i data-lucide="phone" class="w-3 h-3"></i> <?= $tutor['telefone'] ?></span>
                                                        <?php endif; ?>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($tutor['email']): ?>
                                                        <span class="text-slate-300">•</span>
                                                        <span class="truncate max-w-[150px] flex items-center gap-1" title="<?= $tutor['email'] ?>"><i data-lucide="mail" class="w-3 h-3 text-slate-400"></i> <?= $tutor['email'] ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-5 text-sm text-slate-600">
                                        <?php if($tutor['cidade']): ?>
                                            <div class="flex items-center gap-1.5 font-medium">
                                                <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i>
                                                <?= $tutor['cidade'] ?> <?= $tutor['uf'] ? '- ' . $tutor['uf'] : '' ?>
                                            </div>
                                            <?php if($tutor['bairro']): ?>
                                                <div class="text-xs text-slate-400 ml-5 mt-0.5"><?= $tutor['bairro'] ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic">Não informado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-5">
                                        <div class="flex justify-end gap-2 transition-opacity">
                                            <a href="<?= base_url('tutores/ver/' . $tutor['id']) ?>" class="p-2 rounded-lg border border-slate-200 text-slate-400 hover:text-blue-600 hover:bg-blue-50 hover:border-blue-200 transition-colors tooltip-action" title="Ver Detalhes">
                                                <i data-lucide="eye" class="w-5 h-5"></i>
                                            </a>
                                            <a href="<?= base_url('tutores/editar/' . $tutor['id']) ?>" class="p-2 rounded-lg border border-slate-200 text-slate-400 hover:text-brand-600 hover:bg-brand-50 hover:border-brand-200 transition-colors tooltip-action" title="Editar">
                                                <i data-lucide="edit-2" class="w-5 h-5"></i>
                                            </a>
                                            <button onclick="openConfirmModal('<?= base_url('tutores/excluir/' . $tutor['id']) ?>', 'Excluir Tutor', 'Tem certeza que deseja excluir <?= addslashes($tutor['nome']) ?>? Isso pode afetar os pets vinculados.', 'danger', 'trash-2')" 
                                                class="p-2 rounded-lg border border-slate-200 text-slate-400 hover:text-red-600 hover:bg-red-50 hover:border-red-200 transition-colors tooltip-action" title="Excluir">
                                                <i data-lucide="trash-2" class="w-5 h-5"></i>
                                            </button>
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
                <?php if(empty($tutores)): ?>
                    <div class="p-8 text-center text-slate-400">
                        <div class="w-16 h-16 bg-brand-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-brand-100">
                            <i data-lucide="users" class="w-8 h-8 text-brand-400"></i>
                        </div>
                        <h3 class="text-lg font-bold text-slate-900">Nenhum tutor encontrado</h3>
                        <p class="text-slate-500">Tente buscar por outro termo.</p>
                    </div>
                <?php else: ?>
                    <?php foreach($tutores as $tutor): ?>
                        <div class="bg-white rounded-xl p-4 border border-slate-200 shadow-sm">
                            <!-- Tutor Info -->
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 shadow-sm shrink-0 border border-slate-200">
                                    <i data-lucide="user" class="w-6 h-6"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-slate-800 text-lg truncate"><?= $tutor['nome'] ?></h3>
                                    <?php if($tutor['email']): ?>
                                        <div class="flex items-center gap-1.5 text-xs text-slate-500 mt-0.5">
                                            <i data-lucide="mail" class="w-3.5 h-3.5 text-slate-400"></i>
                                            <span class="truncate"><?= $tutor['email'] ?></span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <!-- Contact -->
                            <div class="flex items-center justify-between gap-2 mb-4 pb-4 border-b border-slate-100">
                                <?php if($tutor['cidade']): ?>
                                    <div class="flex items-center gap-2 text-sm text-slate-600 font-medium">
                                        <i data-lucide="map-pin" class="w-4 h-4 text-slate-400"></i>
                                        <?= $tutor['cidade'] ?><?= $tutor['uf'] ? ' - ' . $tutor['uf'] : '' ?>
                                    </div>
                                <?php else: ?>
                                    <div class="text-sm text-slate-400 italic">Sem endereço</div>
                                <?php endif; ?>
                                
                                <?php if(!empty($tutor['telefone'])): ?>
                                    <?php 
                                        $numZap = preg_replace('/[^0-9]/', '', $tutor['telefone']);
                                        if (strlen($numZap) >= 10):
                                    ?>
                                        <a href="https://wa.me/55<?= $numZap ?>" target="_blank" class="flex items-center gap-1.5 bg-green-50 text-green-700 px-2 py-1 rounded-md text-xs font-bold border border-green-100 hover:bg-green-100 transition-colors">
                                            <i data-lucide="message-circle" class="w-3.5 h-3.5"></i> Zap
                                        </a>
                                    <?php else: ?>
                                        <span class="flex items-center gap-1.5 text-sm text-slate-500 bg-slate-50 px-2 py-1 border border-slate-100 rounded-md">
                                            <i data-lucide="phone" class="w-3.5 h-3.5"></i> <?= $tutor['telefone'] ?>
                                        </span>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex gap-2">
                                <a href="<?= base_url('tutores/ver/' . $tutor['id']) ?>" class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors font-medium">
                                    <i data-lucide="eye" class="w-4 h-4 text-slate-400"></i> Ver
                                </a>
                                <a href="<?= base_url('tutores/editar/' . $tutor['id']) ?>" class="flex-1 flex items-center justify-center gap-1.5 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors font-medium">
                                    <i data-lucide="edit-2" class="w-4 h-4 text-slate-400"></i> Editar
                                </a>
                                <button onclick="openConfirmModal('<?= base_url('tutores/excluir/' . $tutor['id']) ?>', 'Excluir Tutor', 'Tem certeza que deseja excluir <?= addslashes($tutor['nome']) ?>?', 'danger', 'trash-2')"
                                        class="flex items-center justify-center gap-1.5 px-4 py-2.5 rounded-xl border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 transition-colors font-medium">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </button>
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
