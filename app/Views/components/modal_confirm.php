<!-- Confirmation Modal Component -->
<div id="confirmation-modal" class="fixed inset-0 z-50 hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm transition-opacity opacity-0 duration-300" id="modal-backdrop"></div>

    <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
            <!-- Modal Panel -->
            <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-md opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95 duration-300" id="modal-panel">
                <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-red-100 sm:mx-0 sm:h-10 sm:w-10 transition-colors duration-300" id="modal-icon-bg">
                            <i id="modal-icon" data-lucide="alert-triangle" class="h-6 w-6 text-red-600 transition-colors duration-300"></i>
                        </div>
                        <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                            <h3 class="text-lg font-bold leading-6 text-slate-900" id="modal-title">Título</h3>
                            <div class="mt-2">
                                <p class="text-sm text-slate-500" id="modal-message">Mensagem de confirmação.</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 gap-2">
                    <a href="#" id="modal-confirm-btn" class="inline-flex w-full justify-center rounded-xl px-4 py-2.5 text-sm font-bold text-white shadow-sm sm:w-auto transition-all hover:scale-105">Confirmar</a>
                    <button type="button" onclick="closeModal()" class="mt-2 sm:mt-0 inline-flex w-full justify-center rounded-xl bg-white px-4 py-2.5 text-sm font-bold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:w-auto transition-all">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const modal = document.getElementById('confirmation-modal');
    const backdrop = document.getElementById('modal-backdrop');
    const panel = document.getElementById('modal-panel');
    const titleEl = document.getElementById('modal-title');
    const messageEl = document.getElementById('modal-message');
    const confirmBtn = document.getElementById('modal-confirm-btn');
    const iconBg = document.getElementById('modal-icon-bg');
    const icon = document.getElementById('modal-icon');

    function openConfirmModal(url, title, message, variant = 'danger', iconName = 'alert-triangle') {
        modal.classList.remove('hidden');
        // Force reflow
        void modal.offsetWidth;
        
        // Animate In
        backdrop.classList.remove('opacity-0');
        panel.classList.remove('opacity-0', 'translate-y-4', 'sm:scale-95');

        titleEl.textContent = title;
        messageEl.textContent = message;
        confirmBtn.href = url;

        // Update Icon
        icon.setAttribute('data-lucide', iconName);
        lucide.createIcons();

        // Styles based on variant
        confirmBtn.className = "inline-flex w-full justify-center rounded-xl px-4 py-2.5 text-sm font-bold text-white shadow-sm sm:w-auto transition-all hover:scale-105";
        iconBg.className = "mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full sm:mx-0 sm:h-10 sm:w-10 transition-colors duration-300";
        icon.className = "h-6 w-6 transition-colors duration-300";

        if (variant === 'danger') {
            confirmBtn.classList.add('bg-red-500', 'hover:bg-red-600', 'shadow-red-500/30');
            iconBg.classList.add('bg-red-100');
            icon.classList.add('text-red-600');
        } else if (variant === 'success') {
            confirmBtn.classList.add('bg-brand-500', 'hover:bg-brand-600', 'shadow-brand-500/30');
            iconBg.classList.add('bg-brand-100');
            icon.classList.add('text-brand-600');
        } else {
            confirmBtn.classList.add('bg-blue-500', 'hover:bg-blue-600', 'shadow-blue-500/30');
            iconBg.classList.add('bg-blue-100');
            icon.classList.add('text-blue-600');
        }
    }

    function closeModal() {
        // Animate Out
        backdrop.classList.add('opacity-0');
        panel.classList.add('opacity-0', 'translate-y-4', 'sm:scale-95');
        
        setTimeout(() => {
            modal.classList.add('hidden');
        }, 300);
    }

    // Fecha ao clicar fora
    backdrop.addEventListener('click', closeModal);
</script>
