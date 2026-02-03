<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Tutores<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex min-h-screen bg-slate-50">
    <?= view('components/sidebar') ?>

    <main class="flex-1 md:ml-64 p-4 md:p-8">
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
            <div class="overflow-x-auto">
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
                                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-dashed border-slate-200">
                                        <i data-lucide="user-x" class="w-8 h-8 text-slate-300"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-slate-900">Nenhum tutor encontrado</h3>
                                    <p class="text-slate-500">Tente buscar por outro termo ou cadastre um novo.</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach($tutores as $tutor): ?>
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="p-5">
                                        <div class="flex items-center gap-4">
                                            <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600 font-bold text-sm">
                                                <?= substr($tutor['nome'], 0, 2) ?>
                                            </div>
                                            <div>
                                                <h3 class="font-bold text-slate-800">
                                                    <a href="<?= base_url('tutores/ver/' . $tutor['id']) ?>" class="hover:text-brand-600 transition-colors">
                                                        <?= $tutor['nome'] ?>
                                                    </a>
                                                </h3>
                                                <div class="flex items-center gap-2 text-sm text-slate-500 mt-0.5">
                                                    <a href="https://wa.me/55<?= preg_replace('/\D/', '', $tutor['telefone']) ?>" target="_blank" class="hover:text-green-600 flex items-center gap-1 transition-colors">
                                                        <i data-lucide="phone" class="w-3 h-3"></i>
                                                        <?= $tutor['telefone'] ?>
                                                    </a>
                                                    <?php if($tutor['email']): ?>
                                                        <span class="text-slate-300">•</span>
                                                        <span class="truncate max-w-[150px]" title="<?= $tutor['email'] ?>"><?= $tutor['email'] ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="p-5 text-sm text-slate-600">
                                        <?php if($tutor['cidade']): ?>
                                            <div class="flex items-center gap-1">
                                                <i data-lucide="map-pin" class="w-3 h-3 text-slate-400"></i>
                                                <?= $tutor['cidade'] ?> <?= $tutor['uf'] ? '- ' . $tutor['uf'] : '' ?>
                                            </div>
                                            <?php if($tutor['bairro']): ?>
                                                <div class="text-xs text-slate-400 ml-4 mt-0.5"><?= $tutor['bairro'] ?></div>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-slate-400 italic">Não informado</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="p-5">
                                        <div class="flex justify-end gap-2 transition-opacity">
                                            <a href="<?= base_url('tutores/ver/' . $tutor['id']) ?>" class="p-2 rounded-lg text-slate-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Ver Detalhes">
                                                <i data-lucide="eye" class="w-5 h-5"></i>
                                            </a>
                                            <a href="<?= base_url('tutores/editar/' . $tutor['id']) ?>" class="p-2 rounded-lg text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-colors" title="Editar">
                                                <i data-lucide="edit-2" class="w-5 h-5"></i>
                                            </a>
                                            <!-- Botão Excluir Com Modal -->
                                            <button onclick="openConfirmModal('<?= base_url('tutores/excluir/' . $tutor['id']) ?>', 'Excluir Tutor', 'Tem certeza que deseja excluir <?= addslashes($tutor['nome']) ?>? Isso pode afetar os pets vinculados.', 'danger', 'trash-2')" 
                                                class="p-2 rounded-lg text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Excluir">
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
            
            <!-- Pagination -->
            <?php if ($pager): ?>
                <div class="p-5 border-t border-slate-100">
                    <?= $pager->links('default', 'tailwind_full') ?>
                </div>
            <?php endif; ?>
        </div>
    </main>
</div>
<?= view('components/modal_confirm') ?>
<?= $this->endSection() ?>
