<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Login<?= $this->endSection() ?>

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
            <!-- Header Mobile Friendly -->
            <div class="mb-10 text-center lg:text-left">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-brand-500 text-white mb-6 shadow-lg shadow-brand-500/30">
                    <i data-lucide="paw-print" class="w-6 h-6"></i>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-slate-900 mb-2">Bem-vindo de volta!</h1>
                <p class="text-slate-500">Acesse o painel do CereniaPet para gerenciar tudo.</p>
            </div>

            <!-- Feedback Messages -->
            <div id="statusMsg" class="hidden p-4 rounded-xl mb-6 text-sm font-medium border"></div>

            <form id="loginForm" class="space-y-5">
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

                <button type="submit" id="submitButton"
                    class="w-full bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3.5 rounded-xl shadow-lg shadow-slate-900/20 transition-all transform active:scale-[0.98] flex items-center justify-center gap-2 mt-4">
                    <span id="buttonText">Acessar Sistema</span>
                    <i data-lucide="arrow-right" class="w-4 h-4 opacity-70"></i>
                    <svg id="loadingSpinner" class="animate-spin h-5 w-5 text-white/90 hidden" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </button>
            </form>

            <div class="mt-8 text-center">
                <p class="text-sm text-slate-500">
                    Não tem acesso? <a href="<?= base_url('cadastro') ?>" class="font-semibold text-brand-600 hover:text-brand-700 transition-colors">Criar conta</a>
                </p>
            </div>
        </div>
    </div>

    <!-- Lado Direito (Visual Decorativo - Apenas Desktop) -->
    <div class="hidden lg:flex lg:w-1/2 bg-slate-50 relative items-center justify-center p-12">
        <div class="relative w-full max-w-lg aspect-square">
            <!-- Abstract Cards Glassmorphism -->
            <div class="absolute top-1/4 left-0 right-0 bg-white/40 backdrop-blur-xl border border-white/50 p-6 rounded-3xl shadow-2xl animate-float">
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 rounded-full bg-orange-100 flex items-center justify-center text-orange-500">
                        <i data-lucide="bone" class="w-6 h-6"></i>
                    </div>
                    <div>
                        <div class="h-2.5 w-24 bg-slate-800/10 rounded-full mb-2"></div>
                        <div class="h-2 w-16 bg-slate-800/5 rounded-full"></div>
                    </div>
                </div>
                <div class="h-32 bg-gradient-to-br from-brand-50 to-white rounded-2xl border border-white/60 mb-3"></div>
                <div class="flex justify-between items-center mt-4">
                     <div class="h-8 w-20 bg-brand-500 rounded-lg opacity-20"></div>
                     <div class="h-2 w-12 bg-slate-800/10 rounded-full"></div>
                </div>
            </div>
            
            <!-- Floating Element -->
            <div class="absolute -bottom-10 -right-10 bg-white p-5 rounded-2xl shadow-xl border border-slate-100 animate-float animation-delay-2000">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-brand-100 flex items-center justify-center text-brand-600">
                        <i data-lucide="check" class="w-5 h-5"></i>
                    </div>
                    <div>
                        <p class="font-bold text-slate-800 text-sm">Status Atualizado</p>
                        <p class="text-xs text-slate-500">Agendamento concluído</p>
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
    // Logic from previous file, updated for new IDs and classes if needed
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const status = urlParams.get('status');
        const statusMsg = document.getElementById('statusMsg');

        if (status === 'expired') {
            showStatus('Sua sessão expirou. Faça login novamente.', 'warning');
        } else if (status === 'logout') {
            showStatus('Você saiu com segurança. Até logo!', 'success');
        }

        function showStatus(msg, type) {
            statusMsg.textContent = msg;
            statusMsg.classList.remove('hidden');
            if(type === 'success') {
                statusMsg.className = 'p-4 rounded-xl mb-6 text-sm font-medium border bg-brand-50 text-brand-700 border-brand-200';
            } else if (type === 'warning') {
                statusMsg.className = 'p-4 rounded-xl mb-6 text-sm font-medium border bg-amber-50 text-amber-700 border-amber-200';
            } else {
                statusMsg.className = 'p-4 rounded-xl mb-6 text-sm font-medium border bg-red-50 text-red-700 border-red-200';
            }
        }

        const loginForm = document.getElementById("loginForm");
        const submitButton = document.getElementById('submitButton');
        const buttonText = document.getElementById('buttonText');
        const loadingSpinner = document.getElementById('loadingSpinner');
        const senhaInput = document.getElementById("senha");

        loginForm.addEventListener("submit", function(event) {
            event.preventDefault();
            
            // UI Loading State
            submitButton.disabled = true;
            buttonText.textContent = 'Autenticando...';
            loadingSpinner.classList.remove('hidden');
            
            const formData = new FormData(loginForm);

            fetch(baseUrl + 'login/auth', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = baseUrl + 'dashboard';
                } else {
                    showStatus(data.message || 'Erro ao entrar.', 'error');
                    submitButton.classList.add('animate-shake');
                    setTimeout(() => submitButton.classList.remove('animate-shake'), 500);
                }
            })
            .catch(error => {
                showStatus('Erro de conexão com o servidor.', 'error');
            })
            .finally(() => {
                submitButton.disabled = false;
                buttonText.textContent = 'Acessar Sistema';
                loadingSpinner.classList.add('hidden');
            });
        });
    });
</script>
<?= $this->endSection() ?>
