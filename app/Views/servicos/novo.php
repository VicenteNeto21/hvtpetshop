<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= isset($servico) ? 'Editar' : 'Novo' ?> Serviço<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex min-h-screen bg-slate-50">
    <?= view('components/sidebar') ?>

    <main class="flex-1 md:ml-64 p-4 md:p-8">
        <div class="w-full animate-enter">
            <!-- Header -->
            <div class="mb-8">
                <a href="<?= base_url('servicos') ?>" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-brand-600 mb-4 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Voltar para Lista
                </a>
                <h1 class="text-3xl font-bold text-slate-900"><?= isset($servico) ? 'Editar Serviço' : 'Novo Serviço' ?></h1>
                <p class="text-slate-500 mt-1">Preencha as informações do serviço.</p>
            </div>

            <!-- Error Alert -->
            <?php if (session('errors')): ?>
                <div class="bg-red-50 text-red-600 p-4 rounded-xl text-sm mb-6 flex items-start gap-3 border border-red-100">
                    <i data-lucide="alert-circle" class="w-5 h-5 shrink-0 mt-0.5"></i>
                    <ul class="list-disc list-inside">
                        <?php foreach (session('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
                <form action="<?= base_url('servicos/salvar') ?>" method="POST" class="space-y-8" onsubmit="this.querySelector('button[type=submit]').classList.add('opacity-75', 'cursor-wait'); this.querySelector('button[type=submit] i').classList.replace('check', 'loader-2'); this.querySelector('button[type=submit] i').classList.add('animate-spin');">
                    <?php if(isset($servico)): ?>
                        <input type="hidden" name="id" value="<?= $servico['id'] ?>">
                    <?php endif; ?>
                    <?= csrf_field() ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Nome do Serviço *</label>
                            <div class="relative">
                                <i data-lucide="scissors" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="text" name="nome" value="<?= $servico['nome'] ?? '' ?>" required 
                                    class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400"
                                    placeholder="Ex: Banho e Tosa Completa">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Preço (R$) *</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-bold text-sm">R$</span>
                                <input type="number" step="0.01" name="preco" value="<?= $servico['preco'] ?? '' ?>" required 
                                    class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400"
                                    placeholder="0,00">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1">Duração Estimada (minutos) *</label>
                            <div class="relative">
                                <i data-lucide="clock" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                <input type="number" name="duracao_estimada" value="<?= $servico['duracao_estimada'] ?? '30' ?>" required 
                                    class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400">
                            </div>
                        </div>

                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Descrição</label>
                            <textarea name="descricao" rows="4" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400" placeholder="Detalhes sobre o que está incluso no serviço..."><?= $servico['descricao'] ?? '' ?></textarea>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 pt-6 flex justify-end gap-3">
                        <a href="<?= base_url('servicos') ?>" class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-colors">
                            Cancelar
                        </a>
                        <?= view('components/btn_salvar', ['label' => 'Salvar Serviço']) ?>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<?= $this->endSection() ?>
