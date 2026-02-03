<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Criar Conta<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex min-h-screen relative overflow-hidden">
    <!-- Background Decorativo -->
    <div class="absolute inset-0 z-0">
        <div class="absolute -top-20 -left-20 w-96 h-96 bg-brand-100 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob"></div>
        <div class="absolute top-0 right-0 w-96 h-96 bg-purple-100 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-2000"></div>
        <div class="absolute -bottom-32 left-20 w-96 h-96 bg-pink-100 rounded-full mix-blend-multiply filter blur-3xl opacity-70 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Lado Esquerdo (Formulário) -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 z-10">
        <div class="w-full max-w-md animate-enter">
            <!-- Header -->
            <div class="mb-10 text-center lg:text-left">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500 text-white mb-6 shadow-lg shadow-brand-500/30">
                    <i data-lucide="user-plus" class="w-6 h-6"></i>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 mb-2">Criar sua conta</h1>
                <p class="text-slate-500">Solicite acesso ao painel do CereniaPet.</p>
            </div>

            <!-- Feedback Messages -->
            <div id="statusMsg" class="hidden p-4 rounded-xl mb-6 text-sm font-medium border"></div>

            <form id="registerForm" class="space-y-5">
                <?= view('components/input', [
                    'id' => 'nome',
                    'name' => 'nome',
                    'label' => 'Nome Completo',
                    'placeholder' => 'Seu nome completo',
                    'icon' => 'user',
                    'required' => true
                ]) ?>

                <?= view('components/input', [
                    'id' => 'email',
                    'name' => 'email',
                    'label' => 'E-mail Corporativo',
                    'placeholder' => 'seu@email.com',
                    'icon' => 'mail',
                    'type' => 'email',
                    'required' => true
                ]) ?>

                <?= view('components/input', [
                    'id' => 'senha',
                    'name' => 'senha',
                    'label' => 'Senha',
                    'placeholder' => '••••••••',
                    'icon' => 'lock',
                    'type' => 'password',
                    'required' => true
                ]) ?>

                <?= view('components/input', [
                    'id' => 'confirmar_senha',
                    'name' => 'confirmar_senha',
                    'label' => 'Confirmar Senha',
                    'placeholder' => '••••••••',
                    'icon' => 'shield-check',
                    'type' => 'password',
                    'required' => true
                ]) ?>

                <button type="submit" id="submitButton"
                    class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-slate-900/20 transition-all transform active:scale-[0.98] flex items-center justify-center gap-2 mt-4">
                    <span id="buttonText">Solicitar Cadastro</span>
                    <i data-lucide="send" class="w-4 h-4 opacity-70"></i>
                    <svg id="loadingSpinner" class="animate-spin h-5 w-5 text-white/90 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-slate-500">
                    Já tem uma conta? <a href="<?= base_url('login') ?>" class="font-semibold text-brand-600 hover:text-brand-700 transition-colors">Fazer login</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Lado Direito (Visual Decorativo - Apenas Desktop) -->
    <div class="hidden lg:flex lg:w-1/2 bg-slate-50 relative items-center justify-center p-12">
        <div class="relative w-full max-w-lg aspect-square">
            <div class="absolute top-1/4 left-0 right-0 bg-white/40 backdrop-blur-xl border border-white/50 p-6 rounded-3xl shadow-2xl animate-float">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-brand-100 flex items-center justify-center text-brand-500">
                        <i data-lucide="shield-check" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800">Segurança em primeiro lugar</p>
                        <p class="text-xs text-slate-500">Todos os acessos passam por aprovação.</p>
                    </div>
                </div>
                <div class="h-32 bg-gradient-to-br from-brand-50 to-white rounded-2xl border border-white/60 mb-3 flex items-center justify-center">
                    <i data-lucide="lock" class="w-12 h-12 text-brand-200"></i>
                </div>
            </div>
            
            <div class="absolute -bottom-10 -right-10 bg-white p-5 rounded-2xl shadow-xl border border-slate-100 animate-float animation-delay-2000">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-100 flex items-center justify-center text-amber-600">
                        <i data-lucide="clock" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Aguardando</p>
                        <p class="text-xs text-slate-500">Aprovação pendente</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    @keyframes blob {
        0% { transform: translate(0px, 0px) scale(1); }
        33% { transform: translate(30px, -50px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
        100% { transform: translate(0px, 0px) scale(1); }
    }
    .animate-blob { animation: blob 7s infinite; }
    
    @keyframes float {
        0% { transform: translateY(0px); }
        50% { transform: translateY(-20px); }
        100% { transform: translateY(0px); }
    }
    .animate-float { animation: float 6s ease-in-out infinite; }
    .animation-delay-2000 { animation-delay: 2s; }
    .animation-delay-4000 { animation-delay: 4s; }
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
        
        const registerForm = document.getElementById("registerForm");
        const submitButton = document.getElementById('submitButton');
        const buttonText = document.getElementById('buttonText');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const statusMsg = document.getElementById('statusMsg');

        function showStatus(msg, type) {
            statusMsg.textContent = msg;
            statusMsg.classList.remove('hidden');
            if(type === 'success') {
                statusMsg.className = 'p-4 rounded-xl mb-6 text-sm font-medium border bg-brand-50 text-brand-700 border-brand-200';
            } else {
                statusMsg.className = 'p-4 rounded-xl mb-6 text-sm font-medium border bg-red-50 text-red-700 border-red-200';
            }
        }

        registerForm.addEventListener("submit", function(event) {
            event.preventDefault();
            
            const senha = document.getElementById('senha').value;
            const confirmar = document.getElementById('confirmar_senha').value;

            if (senha !== confirmar) {
                showStatus('As senhas não coincidem.', 'error');
                return;
            }

            submitButton.disabled = true;
            buttonText.textContent = 'Processando...';
            loadingSpinner.classList.remove('hidden');
            
            const formData = new FormData(registerForm);

            fetch(baseUrl + 'auth/processar-cadastro', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showStatus(data.message, 'success');
                    registerForm.reset();
                    // Opcional: Redirecionar após alguns segundos
                    setTimeout(() => {
                        window.location.href = baseUrl + 'login';
                    }, 5000);
                } else {
                    showStatus(data.message || 'Erro ao realizar cadastro.', 'error');
                }
            })
            .catch(error => {
                showStatus('Erro de conexão com o servidor.', 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                buttonText.textContent = 'Solicitar Cadastro';
                loadingSpinner.classList.add('hidden');
            });
        });
    });
</script>
<?= $this->endSection() ?>
