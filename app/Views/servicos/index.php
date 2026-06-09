<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Serviços<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="animate-enter">
        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 animate-enter">
            <div>
                <h1 class="text-2xl font-bold text-slate-900 flex items-center gap-2">
                    <i data-lucide="scissors" class="w-8 h-8 text-brand-500"></i>
                    Serviços
                </h1>
                <p class="text-slate-500">Gerencie o catálogo de serviços, preços e durações.</p>
            </div>
            <a href="<?= base_url('servicos/novo') ?>" class="bg-brand-500 hover:bg-brand-600 text-white px-5 py-2.5 rounded-xl font-bold shadow-lg shadow-brand-500/20 transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Novo Serviço
            </a>
        </header>

        <!-- Search -->
        <div class="bg-white p-4 rounded-2xl shadow-sm border border-slate-100 mb-6 animate-enter" style="animation-delay: 0.1s">
            <form action="<?= base_url('servicos') ?>" method="GET" class="relative">
                <i data-lucide="search" class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 w-5 h-5"></i>
                <input type="text" name="search" value="<?= $search ?? '' ?>" 
                    class="w-full pl-12 pr-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400"
                    placeholder="Buscar serviço por nome ou descrição...">
            </form>
        </div>

        <!-- Services Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-enter" style="animation-delay: 0.2s">
            <?php foreach($servicos as $servico): ?>
                <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 hover:border-brand-200 hover:shadow-md transition-all group relative">
                    <div class="flex justify-between items-start mb-4">
                        <div class="w-12 h-12 rounded-xl bg-brand-50 border border-brand-100 text-brand-500 flex items-center justify-center shadow-sm">
                            <i data-lucide="sparkles" class="w-6 h-6"></i>
                        </div>
                        <div class="flex gap-2 invisible opacity-0 group-hover:visible group-hover:opacity-100 transition-all">
                            <a href="<?= base_url('servicos/editar/'.$servico['id']) ?>" class="p-2 hover:bg-brand-50 rounded-lg text-slate-400 hover:text-brand-600 border border-transparent hover:border-brand-200 transition-colors tooltip-action" title="Editar">
                                <i data-lucide="edit-2" class="w-4 h-4"></i>
                            </a>
                            <button onclick="openConfirmModal('<?= base_url('servicos/excluir/'.$servico['id']) ?>', 'Excluir Serviço', 'Tem certeza que deseja excluir o serviço <?= addslashes($servico['nome']) ?>?', 'danger', 'trash-2')" class="p-2 hover:bg-red-50 rounded-lg text-slate-400 hover:text-red-500 border border-transparent hover:border-red-200 transition-colors tooltip-action" title="Excluir">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                    </div>
                    
                    <h3 class="font-bold text-lg text-slate-800 mb-1"><?= esc($servico['nome']) ?></h3>
                    <p class="text-sm text-slate-500 mb-4 line-clamp-2 min-h-[40px]"><?= esc($servico['descricao'] ?: 'Sem descrição') ?></p>
                    
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                        <div class="flex items-center gap-1.5 text-slate-500 text-sm font-medium">
                            <i data-lucide="clock" class="w-4 h-4"></i>
                            <span><?= esc($servico['duracao_estimada']) ?> min</span>
                        </div>
                        <span class="font-black text-brand-600 text-lg">
                            R$ <?= number_format($servico['preco'], 2, ',', '.') ?>
                        </span>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if(empty($servicos)): ?>
            <div class="text-center py-12 bg-white rounded-2xl border border-slate-100 shadow-sm mt-2">
                <div class="w-16 h-16 bg-brand-50 rounded-full flex items-center justify-center mx-auto mb-4 border border-brand-100">
                    <i data-lucide="scissors" class="w-8 h-8 text-brand-400"></i>
                </div>
                <h3 class="text-lg font-bold text-slate-900">Nenhum serviço encontrado</h3>
                <p class="text-slate-500">Tente outra busca ou cadastre um novo serviço.</p>
            </div>
        <?php endif; ?>

        <!-- Pagination -->
        <div class="mt-8">
            <?= $pager->links('default', 'tailwind_full') ?>
        </div>
</div>
<?php /* Tags de fechamento main/div removidas pelo padrão main.php */ ?>
<?= view('components/modal_confirm') ?>
<?= $this->endSection() ?>
