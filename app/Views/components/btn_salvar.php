<?php
// Default values
$label = $label ?? 'Salvar';
$icon = $icon ?? 'check';
$extraClass = $extraClass ?? '';
?>

<button type="submit" class="px-8 py-3 rounded-xl bg-brand-500 text-white font-bold hover:bg-brand-600 shadow-lg shadow-brand-500/20 hover:shadow-brand-500/30 transition-all flex items-center justify-center gap-2 <?= $extraClass ?>">
    <i data-lucide="<?= $icon ?>" class="w-5 h-5"></i>
    <span><?= $label ?></span>
</button>
