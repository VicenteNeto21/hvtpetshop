<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Cadastro Rápido<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex min-h-screen bg-slate-50">
    <?= view('components/sidebar') ?>

    <main class="flex-1 md:ml-64 p-4 md:p-8">
        <div class="w-full animate-enter">
            <!-- Header -->
            <div class="mb-8">
                <a href="<?= base_url('agenda/novo') ?>" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-brand-600 mb-4 transition-colors">
                    <i data-lucide="arrow-left" class="w-4 h-4"></i>
                    Voltar para Agendamento
                </a>
                <h1 class="text-3xl font-bold text-slate-900">Cadastro Rápido</h1>
                <p class="text-slate-500 mt-1">Crie o registro do Tutor e Pet em uma única etapa.</p>
            </div>

            <!-- Form Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
                <form action="<?= base_url('agenda/salvar-cadastro-rapido') ?>" method="POST" class="p-6 md:p-8 space-y-8">
                    <?= csrf_field() ?>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-12">
                        <!-- Lado Esquerdo: Tutor -->
                        <div class="space-y-6">
                            <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
                                <i data-lucide="user" class="w-5 h-5 text-brand-500"></i>
                                Dados do Tutor
                            </h2>
                            
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-1">Nome Completo *</label>
                                    <input type="text" name="tutor_nome" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-1">Telefone / WhatsApp *</label>
                                    <input type="text" name="tutor_telefone" required placeholder="(xx) xxxxx-xxxx" class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                                </div>
                                 <!-- Checkbox WhatsApp -->
                                    <div class="flex items-center gap-2">
                                        <input type="checkbox" name="is_whatsapp" id="is_whatsapp" value="1" checked class="rounded border-slate-300 text-brand-500 focus:ring-brand-500">
                                        <label for="is_whatsapp" class="text-sm text-slate-600 cursor-pointer">Este número é WhatsApp</label>
                                    </div>
                            </div>
                        </div>

                        <!-- Lado Direito: Pet -->
                        <div class="space-y-6">
                            <h2 class="text-lg font-bold text-slate-800 border-b border-slate-100 pb-2 flex items-center gap-2">
                                <i data-lucide="dog" class="w-5 h-5 text-brand-500"></i>
                                Dados do Pet
                            </h2>

                            <div class="space-y-4">
                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-1">Nome do Pet *</label>
                                    <input type="text" name="pet_nome" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-slate-600 mb-1">Espécie *</label>
                                        <select name="pet_especie" id="pet_especie" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                                            <option value="Cachorro">Cachorro</option>
                                            <option value="Gato">Gato</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-slate-600 mb-1">Sexo *</label>
                                        <select name="pet_sexo" required class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                                            <option value="M">Macho</option>
                                            <option value="F">Fêmea</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-slate-600 mb-1">Raça</label>
                                    <input type="text" name="pet_raca" placeholder="Ex: Poodle, SRD..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:bg-white transition-all">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-slate-50">
                        <a href="<?= base_url('agenda/novo') ?>" class="px-6 py-3 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-slate-50 transition-colors">
                            Cancelar
                        </a>
                        <?= view('components/btn_salvar', ['label' => 'Salvar e Agendar', 'icon' => 'check']) ?>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>
<?= $this->endSection() ?>
