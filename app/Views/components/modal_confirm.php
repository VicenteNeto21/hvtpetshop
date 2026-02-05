<?php
/**
 * Componente: Modal de Confirmação
 * Uso via JavaScript: openConfirmModal(title, message, confirmUrl)
 */
?>

<!-- Modal Overlay -->
<div id="confirm-modal-overlay" class="fixed inset-0 bg-black/50 backdrop-blur-sm z-50 hidden opacity-0 transition-opacity duration-300"></div>

<!-- Modal Content -->
<div id="confirm-modal" class="fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 z-50 hidden opacity-0 scale-95 transition-all duration-300 w-full max-w-md">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 overflow-hidden">
        <!-- Header -->
        <div class="p-6 text-center">
            <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i data-lucide="alert-triangle" class="w-8 h-8 text-red-500"></i>
            </div>
            <h3 id="confirm-modal-title" class="text-xl font-bold text-slate-900 mb-2">Confirmar Ação?</h3>
            <p id="confirm-modal-message" class="text-slate-500 text-sm">Esta ação não poderá ser desfeita.</p>
        </div>
        
        <!-- Actions -->
        <div class="flex border-t border-slate-100">
            <button type="button" onclick="closeConfirmModal()" 
                class="flex-1 py-4 font-semibold text-slate-600 hover:bg-slate-50 transition-colors border-r border-slate-100">
                Cancelar
            </button>
            <a id="confirm-modal-action" href="#" 
                class="flex-1 py-4 font-semibold text-center text-red-600 hover:bg-red-50 transition-colors">
                Confirmar
            </a>
        </div>
    </div>
</div>

<script>
    function openConfirmModal(title, message, confirmUrl) {
        const overlay = document.getElementById('confirm-modal-overlay');
        const modal = document.getElementById('confirm-modal');
        const titleEl = document.getElementById('confirm-modal-title');
        const messageEl = document.getElementById('confirm-modal-message');
        const actionEl = document.getElementById('confirm-modal-action');

        titleEl.textContent = title || 'Confirmar Ação?';
        messageEl.textContent = message || 'Esta ação não poderá ser desfeita.';
        actionEl.href = confirmUrl || '#';

        overlay.classList.remove('hidden');
        modal.classList.remove('hidden');

        // Trigger animation
        setTimeout(() => {
            overlay.classList.remove('opacity-0');
            modal.classList.remove('opacity-0', 'scale-95');
            modal.classList.add('scale-100');
        }, 10);

        // Re-render Lucide icons
        if (typeof lucide !== 'undefined') {
            lucide.createIcons();
        }
    }

    function closeConfirmModal() {
        const overlay = document.getElementById('confirm-modal-overlay');
        const modal = document.getElementById('confirm-modal');

        overlay.classList.add('opacity-0');
        modal.classList.add('opacity-0', 'scale-95');
        modal.classList.remove('scale-100');

        setTimeout(() => {
            overlay.classList.add('hidden');
            modal.classList.add('hidden');
        }, 300);
    }

    // Close on overlay click
    document.getElementById('confirm-modal-overlay')?.addEventListener('click', closeConfirmModal);

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeConfirmModal();
        }
    });
</script>
