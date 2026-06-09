<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Estoque e Produtos
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="max-w-7xl mx-auto">
<div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Estoque de Produtos</h1>
        <p class="text-sm text-slate-500 mt-1">Gerencie rações, medicamentos, brinquedos e acessórios.</p>
    </div>
    
    <div class="flex gap-3">
        <a href="<?= base_url('produtos/cadastrar') ?>" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-brand-500 text-white text-sm font-medium rounded-xl hover:bg-brand-600 focus:ring-4 focus:ring-brand-500/20 transition-all shadow-sm">
            <i data-lucide="plus" class="w-4 h-4"></i>
            Novo Produto
        </a>
    </div>
</div>

<?php if(session()->getFlashdata('success')): ?>
    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 flex items-center gap-3">
        <i data-lucide="check-circle" class="w-5 h-5 text-emerald-500"></i>
        <p class="font-medium"><?= session()->getFlashdata('success') ?></p>
    </div>
<?php endif; ?>

<div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm whitespace-nowrap">
            <thead class="bg-slate-50 border-b border-slate-200 text-slate-600 font-medium">
                <tr>
                    <th class="px-6 py-4">Nome do Produto</th>
                    <th class="px-6 py-4">Código (Barras)</th>
                    <th class="px-6 py-4">Preço (Venda)</th>
                    <th class="px-6 py-4">Estoque</th>
                    <th class="px-6 py-4 text-center">Status</th>
                    <th class="px-6 py-4 text-right">Ações</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 text-slate-700">
                <?php if(empty($produtos)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                    <i data-lucide="package" class="w-8 h-8 text-slate-400"></i>
                                </div>
                                <p class="text-base font-medium text-slate-700">Nenhum produto cadastrado</p>
                                <p class="mt-1 text-sm">Adicione os produtos para vendê-los no PDV.</p>
                                <a href="<?= base_url('produtos/cadastrar') ?>" class="mt-4 text-brand-500 hover:text-brand-600 font-medium text-sm">
                                    + Cadastrar primeiro produto
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($produtos as $prod): ?>
                    <tr class="hover:bg-slate-50/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800"><?= esc($prod['nome']) ?></div>
                            <?php if(!empty($prod['descricao'])): ?>
                                <div class="text-xs text-slate-500 truncate max-w-[200px]"><?= esc($prod['descricao']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            <?= esc($prod['codigo_barras'] ?? 'N/A') ?>
                        </td>
                        <td class="px-6 py-4 font-medium text-emerald-600">
                            R$ <?= number_format($prod['preco_venda'], 2, ',', '.') ?>
                        </td>
                        <td class="px-6 py-4">
                            <?php 
                                $estoque = $prod['estoque_atual'];
                                $minimo = $prod['estoque_minimo'];
                                $corEstoque = 'text-slate-700';
                                $badgeClass = 'bg-slate-100 text-slate-700 border-slate-200';
                                
                                if($estoque <= 0) {
                                    $badgeClass = 'bg-red-50 text-red-700 border-red-200';
                                } elseif($estoque <= $minimo) {
                                    $badgeClass = 'bg-orange-50 text-orange-700 border-orange-200';
                                } else {
                                    $badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                                }
                            ?>
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-xs font-medium border <?= $badgeClass ?>">
                                <?= $estoque ?> un
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <?php if($prod['status'] == 'ativo'): ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-emerald-50 text-emerald-700 text-xs font-medium border border-emerald-100">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    Ativo
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-50 text-slate-600 text-xs font-medium border border-slate-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                    Inativo
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?= base_url('produtos/editar/' . $prod['id']) ?>" class="p-2 text-slate-400 hover:text-brand-500 hover:bg-brand-50 rounded-lg transition-colors" title="Editar">
                                    <i data-lucide="edit-2" class="w-4 h-4"></i>
                                </a>
                                <a href="javascript:void(0)" onclick="confirmarExclusao(<?= $prod['id'] ?>)" class="p-2 text-slate-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition-colors" title="Excluir">
                                    <i data-lucide="trash-2" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function confirmarExclusao(id) {
    if(confirm('Tem certeza que deseja excluir este produto? Ele será enviado para a lixeira (Soft Delete) e não afetará as vendas antigas.')) {
        window.location.href = '<?= base_url('produtos/excluir/') ?>' + id;
    }
}
</script>
</div>

<?= $this->endSection() ?>
