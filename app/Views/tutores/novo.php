<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?><?= isset($tutor) ? 'Editar' : 'Novo' ?> Tutor<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex min-h-screen bg-slate-50">
    <?= view('components/sidebar') ?>

    <main class="flex-1 md:ml-64 p-4 md:p-8">
        <div class="w-full animate-enter">
            <!-- Header -->
            <div class="mb-8">
                <a href="<?= base_url('tutores') ?>" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-brand-600 mb-4 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Voltar para Lista
                </a>
                <h1 class="text-3xl font-bold text-slate-900"><?= isset($tutor) ? 'Editar Tutor' : 'Novo Tutor' ?></h1>
                <p class="text-slate-500 mt-1">Preencha as informações do cliente.</p>
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

                <form action="<?= base_url('tutores/salvar') ?>" method="POST" class="space-y-8" onsubmit="this.querySelector('button[type=submit]').classList.add('opacity-75', 'cursor-wait'); this.querySelector('button[type=submit] i').classList.replace('check', 'loader-2'); this.querySelector('button[type=submit] i').classList.add('animate-spin');">
                    <?php if(isset($tutor)): ?>
                        <input type="hidden" name="id" value="<?= $tutor['id'] ?>">
                    <?php endif; ?>
                    <?= csrf_field() ?>

                    <!-- Dados Pessoais -->
                    <div class="space-y-6">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i data-lucide="user" class="w-5 h-5 text-brand-500"></i>
                            Dados Pessoais
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Nome Completo *</label>
                                <div class="relative">
                                    <i data-lucide="user" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" name="nome" value="<?= $tutor['nome'] ?? '' ?>" required 
                                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400">
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Telefone / WhatsApp *</label>
                                <div class="relative">
                                    <i data-lucide="phone" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" name="telefone" value="<?= $tutor['telefone'] ?? '' ?>" required oninput="mascaraTelefone(this)" maxlength="15"
                                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400"
                                        placeholder="(00) 00000-0000">
                                </div>
                                <label class="flex items-center gap-2 mt-2 cursor-pointer">
                                    <input type="checkbox" name="telefone_is_whatsapp" value="Sim" 
                                        <?= (isset($tutor) && $tutor['telefone_is_whatsapp'] == 'Sim') ? 'checked' : '' ?>
                                        class="rounded text-brand-600 focus:ring-brand-500 border-gray-300">
                                    <span class="text-sm text-slate-500">Este número é WhatsApp</span>
                                </label>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">E-mail</label>
                                <div class="relative">
                                    <i data-lucide="mail" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="email" name="email" value="<?= $tutor['email'] ?? '' ?>" 
                                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Endereço -->
                    <div class="space-y-6 pt-4">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2 border-b border-slate-100 pb-2">
                            <i data-lucide="map-pin" class="w-5 h-5 text-violet-500"></i>
                            Endereço
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-6 gap-6">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">CEP</label>
                                <div class="relative">
                                    <i data-lucide="map" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
                                    <input type="text" name="cep" id="cep" value="<?= $tutor['cep'] ?? '' ?>" onblur="buscarCep()" maxlength="9"
                                        class="w-full pl-12 pr-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400"
                                        placeholder="00000-000">
                                    <div id="loading-cep" class="absolute right-3 top-1/2 -translate-y-1/2 hidden">
                                        <i data-lucide="loader-2" class="w-4 h-4 animate-spin text-brand-500"></i>
                                    </div>
                                </div>
                            </div>
                            <div class="md:col-span-4">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Rua</label>
                                <input type="text" name="rua" id="rua" value="<?= $tutor['rua'] ?? '' ?>" 
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400 bg-slate-50">
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Número</label>
                                <input type="text" name="numero" value="<?= $tutor['numero'] ?? '' ?>" 
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Bairro</label>
                                <input type="text" name="bairro" id="bairro" value="<?= $tutor['bairro'] ?? '' ?>" 
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400 bg-slate-50">
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-slate-700 mb-1">Cidade</label>
                                <input type="text" name="cidade" id="cidade" value="<?= $tutor['cidade'] ?? '' ?>" 
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400 bg-slate-50">
                            </div>
                            <div class="md:col-span-1">
                                <label class="block text-sm font-medium text-slate-700 mb-1">UF</label>
                                <input type="text" name="uf" id="uf" value="<?= $tutor['uf'] ?? '' ?>" maxlength="2"
                                    class="w-full px-4 py-2.5 rounded-xl border border-slate-200 focus:border-brand-500 focus:ring-1 focus:ring-brand-500 outline-none transition-all placeholder:text-slate-400 bg-slate-50">
                            </div>
                        </div>
                    </div>
                    
                    <div class="border-t border-slate-100 pt-6 flex justify-end gap-3">
                        <button type="button" onclick="window.history.back()" class="px-6 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-bold hover:bg-slate-50 transition-colors">
                            Cancelar
                        </button>
                        <?= view('components/btn_salvar', ['label' => 'Salvar Tutor']) ?>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
    function mascaraTelefone(input) {
        let v = input.value.replace(/\D/g, '');
        if (v.length > 11) v = v.slice(0, 11);
        if (v.length > 0) v = '(' + v;
        if (v.length > 3) v = v.slice(0, 3) + ') ' + v.slice(3);
        if (v.length > 10) v = v.slice(0, 10) + '-' + v.slice(10);
        input.value = v;
    }

    function buscarCep() {
        const cep = document.getElementById('cep').value.replace(/\D/g, '');
        if (cep.length !== 8) return;
        
        const loading = document.getElementById('loading-cep');
        loading.classList.remove('hidden');

        fetch('https://viacep.com.br/ws/' + cep + '/json/')
            .then(response => response.json())
            .then(data => {
                loading.classList.add('hidden');
                if (!data.erro) {
                    document.getElementById('rua').value = data.logradouro || '';
                    document.getElementById('bairro').value = data.bairro || '';
                    document.getElementById('cidade').value = data.localidade || '';
                    document.getElementById('uf').value = data.uf || '';
                    // Focar no número
                    document.querySelector('input[name="numero"]').focus();
                }
            })
            .catch(() => loading.classList.add('hidden'));
    }
</script>
<?= $this->endSection() ?>
