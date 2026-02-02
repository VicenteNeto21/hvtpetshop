<?php
// Este componente espera $_SESSION['mensagem'] e $_SESSION['tipo_mensagem'] serem definidos.
// Ele exibirá um toast e, em seguida, limpará essas variáveis de sessão.

$toastMessage = null;
$toastType = null;

if (isset($_SESSION['mensagem'])) {
    $toastMessage = $_SESSION['mensagem'];
    $toastType = $_SESSION['tipo_mensagem']; // 'success' ou 'error'
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}

if ($toastMessage):
    $bgColor = ($toastType === 'success') ? 'bg-green-500' : 'bg-red-500';
    $icon = ($toastType === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle';
?>
<div id="toast-message" class="fixed top-20 right-5 <?= $bgColor ?> text-white py-3 px-5 rounded-lg shadow-lg flex items-center gap-3 z-50 animate-slide-in-right">
    <i class="fa-solid <?= $icon ?> text-xl"></i>
    <div>
        <p class="font-bold"><?= htmlspecialchars($toastType === 'success' ? 'Sucesso!' : 'Erro!'); ?></p>
        <p class="text-sm"><?= htmlspecialchars($toastMessage); ?></p>
    </div>
    <button onclick="document.getElementById('toast-message').remove()" class="ml-4 text-lg font-bold opacity-70 hover:opacity-100">&times;</button>
</div>
<style>
    @keyframes slide-in-right {
        from { opacity: 0; transform: translateX(100%); }
        to { opacity: 1; transform: translateX(0); }
    }
    .animate-slide-in-right { animation: slide-in-right 0.5s ease-out forwards; }
</style>
<script>
    setTimeout(() => {
        const toast = document.getElementById('toast-message');
        if (toast) toast.remove();
    }, 5000); // Oculta após 5 segundos
</script>
<?php endif; ?>