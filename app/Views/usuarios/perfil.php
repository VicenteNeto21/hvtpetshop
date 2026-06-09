<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Meu Perfil<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="animate-enter">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Meu Perfil</h1>
            <p class="text-slate-500 text-sm">Visualize suas informações de acesso ao sistema.</p>
        </div>
        <div class="w-12 h-12 rounded-2xl bg-brand-50 flex items-center justify-center text-brand-600 shadow-sm border border-brand-100">
            <i data-lucide="user" class="w-6 h-6"></i>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Sidebar do Perfil (Cartão de Avatar) -->
        <div class="md:col-span-1 space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 text-center relative overflow-hidden group">
                <div class="absolute top-0 left-0 w-full h-2 bg-brand-500"></div>
                
                <div class="relative inline-block mb-4">
                    <div class="w-24 h-24 rounded-full bg-slate-50 border-4 border-white shadow-md flex items-center justify-center text-slate-300 relative overflow-hidden">
                        <i data-lucide="user" class="w-12 h-12"></i>
                        <!-- Letra Inicial opcional -->
                        <span class="absolute inset-0 flex items-center justify-center text-3xl font-bold text-brand-600 bg-brand-50 opacity-0 group-hover:opacity-100 transition-opacity">
                            <?= substr($user['nome'], 0, 1) ?>
                        </span>
                    </div>
                </div>
                
                <h2 class="text-xl font-bold text-slate-800"><?= $user['nome'] ?></h2>
                <p class="text-slate-500 text-sm mb-4"><?= $user['email'] ?></p>
                
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?= $user['tipo'] == 'admin' ? 'bg-amber-100 text-amber-700' : 'bg-brand-100 text-brand-700' ?>">
                    <i data-lucide="<?= $user['tipo'] == 'admin' ? 'shield' : 'user' ?>" class="w-3 h-3"></i>
                    <?= $user['tipo'] == 'admin' ? 'Administrador' : 'Funcionário' ?>
                </div>
            </div>

            <div class="bg-slate-900 rounded-3xl p-6 text-white shadow-lg shadow-slate-900/20">
                <h3 class="font-bold mb-4 flex items-center gap-2">
                    <i data-lucide="info" class="w-4 h-4 text-brand-400"></i>
                    Informação
                </h3>
                <p class="text-xs text-slate-400 leading-relaxed">
                    Esta tela é apenas para visualização de dados. Para alterar qualquer informação ou sua senha, entre em contato com o administrador do sistema.
                </p>
            </div>
        </div>

        <!-- Detalhes do Perfil -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2 text-brand-600">
                    <i data-lucide="file-text" class="w-5 h-5"></i>
                    Dados Pessoais
                </h3>
                
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Nome Completo</label>
                        <p class="text-slate-700 font-medium bg-slate-50/50 p-3 rounded-xl border border-dashed border-slate-200"><?= $user['nome'] ?></p>
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">E-mail de Acesso</label>
                        <p class="text-slate-700 font-medium bg-slate-50/50 p-3 rounded-xl border border-dashed border-slate-200"><?= $user['email'] ?></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Data de Cadastro</label>
                            <div class="flex items-center gap-2 text-slate-700 font-medium bg-slate-50/50 p-3 rounded-xl border border-dashed border-slate-200">
                                <i data-lucide="calendar" class="w-4 h-4 text-slate-400"></i>
                                <?= date('d/m/Y H:i', strtotime($user['criado_em'])) ?>
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Status da Conta</label>
                            <div class="flex items-center gap-2 text-slate-700 font-medium bg-slate-50/50 p-3 rounded-xl border border-dashed border-slate-200">
                                <div class="w-2 h-2 rounded-full bg-green-500"></div>
                                <?= ucfirst($user['status']) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Segurança (Visualização) -->
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8">
                <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2 text-brand-600">
                    <i data-lucide="lock" class="w-5 h-5"></i>
                    Segurança
                </h3>
                
                <div class="space-y-4">
                    <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-400 shadow-sm">
                                <i data-lucide="key" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-700">Senha de Acesso</p>
                                <p class="text-xs text-slate-400">A senha está protegida com criptografia ponta a ponta.</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider">Protegido</span>
                    </div>

                    <div class="flex items-center justify-between p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 rounded-xl bg-white flex items-center justify-center text-slate-400 shadow-sm">
                                <i data-lucide="shield-check" class="w-5 h-5"></i>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-slate-700">Nível de Permissão</p>
                                <p class="text-xs text-slate-400">Você possui acesso de <?= $user['tipo'] ?>.</p>
                            </div>
                        </div>
                        <span class="text-xs font-bold text-brand-500 uppercase tracking-wider">Ativo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
