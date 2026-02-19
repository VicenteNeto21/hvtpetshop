<?php
/**
 * Toast Global — Componente de notificação
 * Exibe mensagens de sucesso, erro e warning automaticamente via flash data.
 * Incluído no layouts/main.php, funciona em TODAS as telas.
 */
$toasts = [];

if (session()->getFlashdata('success')) {
    $toasts[] = ['type' => 'success', 'message' => session()->getFlashdata('success')];
}
if (session()->getFlashdata('error')) {
    $toasts[] = ['type' => 'error', 'message' => session()->getFlashdata('error')];
}
if (session()->getFlashdata('warning')) {
    $toasts[] = ['type' => 'warning', 'message' => session()->getFlashdata('warning')];
}

// Também suporta array de erros de validação
if (session()->getFlashdata('errors') && is_array(session()->getFlashdata('errors'))) {
    foreach (session()->getFlashdata('errors') as $err) {
        $toasts[] = ['type' => 'error', 'message' => $err];
    }
}
?>

<?php if (!empty($toasts)): ?>
<div id="toast-container" class="fixed top-6 right-6 z-50 flex flex-col gap-3" style="max-width: 420px;">
    <?php foreach ($toasts as $i => $toast): 
        $colors = match($toast['type']) {
            'success' => 'bg-emerald-600 shadow-emerald-600/30',
            'error'   => 'bg-red-600 shadow-red-600/30',
            'warning' => 'bg-amber-500 shadow-amber-500/30',
            default   => 'bg-slate-700 shadow-slate-700/30'
        };
        $icon = match($toast['type']) {
            'success' => 'check-circle',
            'error'   => 'alert-circle',
            'warning' => 'alert-triangle',
            default   => 'info'
        };
    ?>
    <div class="toast-item flex items-center gap-3 <?= $colors ?> text-white px-5 py-4 rounded-2xl shadow-2xl font-medium text-sm animate-enter"
         style="animation-delay: <?= $i * 0.1 ?>s"
         onclick="this.style.transition='opacity 0.3s,transform 0.3s';this.style.opacity='0';this.style.transform='translateX(20px)';setTimeout(()=>this.remove(),300)">
        <i data-lucide="<?= $icon ?>" class="w-5 h-5 shrink-0"></i>
        <span class="flex-1"><?= esc($toast['message']) ?></span>
        <button class="shrink-0 opacity-60 hover:opacity-100 transition-opacity" onclick="event.stopPropagation();this.parentElement.style.transition='opacity 0.3s,transform 0.3s';this.parentElement.style.opacity='0';this.parentElement.style.transform='translateX(20px)';setTimeout(()=>this.parentElement.remove(),300)">
            <i data-lucide="x" class="w-4 h-4"></i>
        </button>
    </div>
    <?php endforeach; ?>
</div>

<script>
    // Auto-dismiss toasts after 5 seconds
    setTimeout(() => {
        document.querySelectorAll('.toast-item').forEach((t, i) => {
            setTimeout(() => {
                t.style.transition = 'opacity 0.5s, transform 0.5s';
                t.style.opacity = '0';
                t.style.transform = 'translateY(10px)';
                setTimeout(() => t.remove(), 500);
            }, i * 200);
        });
    }, 5000);
</script>
<?php endif; ?>
