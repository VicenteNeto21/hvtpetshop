<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
<?= isset($produto) ? 'Editar Produto' : 'Novo Produto' ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<div class="max-w-4xl mx-auto">
    <div class="mb-8 flex items-center gap-4">
        <a href="<?= base_url('produtos') ?>" class="p-2 text-slate-400 hover:text-slate-600 hover:bg-slate-100 rounded-xl transition-colors">
            <i data-lucide="arrow-left" class="w-5 h-5"></i>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight"><?= isset($produto) ? 'Editar Produto' : 'Cadastrar Produto' ?></h1>
            <p class="text-sm text-slate-500 mt-1"><?= isset($produto) ? 'Atualize as informações do estoque.' : 'Preencha os dados do novo produto para vendas no PDV.' ?></p>
        </div>
    </div>

<?php if(session()->get('errors')): ?>
    <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-100 text-red-700">
        <ul class="list-disc pl-5 space-y-1 text-sm">
            <?php foreach(session()->get('errors') as $error): ?>
                <li><?= $error ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form action="<?= base_url('produtos/salvar') ?>" method="POST" class="max-w-4xl">
    <?= csrf_field() ?>
    <?php if(isset($produto)): ?>
        <input type="hidden" name="id" value="<?= $produto['id'] ?>">
    <?php endif; ?>

    <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden mb-6">
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                <i data-lucide="info" class="w-5 h-5 text-brand-500"></i>
                Informações Principais
            </h2>
        </div>
        
        <div class="p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nome -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Nome do Produto *</label>
                    <input type="text" name="nome" value="<?= old('nome', $produto['nome'] ?? '') ?>" required 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors"
                           placeholder="Ex: Ração Golden Special Cães Adultos 15kg">
                </div>

                <!-- Código de Barras -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2 flex items-center gap-2">
                        Código de Barras
                        <span class="text-xs text-slate-400 font-normal">(Opcional, mas recomendado para o PDV)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <i data-lucide="barcode" class="w-5 h-5 text-slate-400"></i>
                        </div>
                        <input type="text" name="codigo_barras" value="<?= old('codigo_barras', $produto['codigo_barras'] ?? '') ?>" 
                               class="w-full pl-11 pr-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors"
                               placeholder="Bipper o produto aqui ou digite o código EAN">
                    </div>
                </div>

                <!-- Descrição -->
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-2">Descrição</label>
                    <textarea name="descricao" rows="3" 
                              class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors resize-none"
                              placeholder="Detalhes adicionais, marca, tamanho..."><?= old('descricao', $produto['descricao'] ?? '') ?></textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <!-- Preços -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                    <i data-lucide="circle-dollar-sign" class="w-5 h-5 text-emerald-500"></i>
                    Precificação
                </h2>
            </div>
            <div class="p-6 space-y-6">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Preço de Venda (R$) *</label>
                    <input type="text" name="preco_venda" value="<?= old('preco_venda', isset($produto) ? number_format($produto['preco_venda'], 2, ',', '.') : '') ?>" required
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors money-mask"
                           placeholder="0,00">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Preço de Custo (R$)</label>
                    <input type="text" name="preco_custo" value="<?= old('preco_custo', isset($produto) ? number_format($produto['preco_custo'], 2, ',', '.') : '') ?>" 
                           class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors money-mask"
                           placeholder="0,00">
                </div>
            </div>
        </div>

        <!-- Estoque e Status -->
        <div class="bg-white border border-slate-200 rounded-2xl shadow-sm overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                    <i data-lucide="package-search" class="w-5 h-5 text-orange-500"></i>
                    Estoque e Status
                </h2>
            </div>
            <div class="p-6 space-y-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Estoque Atual</label>
                        <input type="number" name="estoque_atual" value="<?= old('estoque_atual', $produto['estoque_atual'] ?? '0') ?>" 
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Estoque Mínimo</label>
                        <input type="number" name="estoque_minimo" value="<?= old('estoque_minimo', $produto['estoque_minimo'] ?? '0') ?>" 
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-2">Status do Produto</label>
                    <select name="status" class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-colors">
                        <option value="ativo" <?= old('status', $produto['status'] ?? '') == 'ativo' ? 'selected' : '' ?>>Ativo (Visível no PDV)</option>
                        <option value="inativo" <?= old('status', $produto['status'] ?? '') == 'inativo' ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="flex justify-end gap-3 pt-4">
        <a href="<?= base_url('produtos') ?>" class="px-5 py-2.5 text-sm font-medium text-slate-600 bg-white border border-slate-200 rounded-xl hover:bg-slate-50 hover:text-slate-900 transition-colors">
            Cancelar
        </a>
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-white bg-brand-500 rounded-xl hover:bg-brand-600 focus:ring-4 focus:ring-brand-500/20 transition-all shadow-sm">
            <i data-lucide="save" class="w-4 h-4"></i>
            Salvar Produto
        </button>
    </div>
</form>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    $(document).ready(function(){
        $('.money-mask').mask('#.##0,00', {reverse: true});
        
        // Auto-focus no campo de barras se for novo produto (facilita pro operador bipar)
        <?php if(!isset($produto)): ?>
        $('input[name="codigo_barras"]').focus();
        <?php endif; ?>
    });
</script>

<?= $this->endSection() ?>
