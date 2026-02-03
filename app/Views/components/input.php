<?php 
/**
 * Componente: Input Standard
 * Argumentos: 
 * - id: string
 * - name: string
 * - label: string
 * - type: string (default: text)
 * - icon: string (Lucide icon name)
 * - placeholder: string
 * - required: bool
 * - value: string
 * - extraClass: string
 */
$type = $type ?? 'text';
$isRequired = ($required ?? false) ? 'required' : '';
?>

<div class="space-y-2 <?= $extraClass ?? '' ?>">
    <?php if(!empty($label)): ?>
        <label for="<?= $id ?>" class="text-sm font-semibold text-slate-700 ml-1"><?= $label ?></label>
    <?php endif; ?>
    
    <div class="relative group">
        <?php if(!empty($icon)): ?>
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                <i data-lucide="<?= $icon ?>" class="w-5 h-5 text-slate-400 group-focus-within:text-brand-500 transition-colors"></i>
            </div>
        <?php endif; ?>

        <input 
            type="<?= $type ?>" 
            name="<?= $name ?>" 
            id="<?= $id ?>" 
            <?= $isRequired ?> 
            placeholder="<?= $placeholder ?? '' ?>"
            value="<?= $value ?? '' ?>"
            class="block w-full <?= !empty($icon) ? 'pl-11' : 'px-4' ?> <?= $type === 'password' ? 'pr-12' : 'pr-4' ?> py-3.5 bg-white border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all shadow-sm group-hover:border-slate-300"
        >

        <?php if($type === 'password'): ?>
            <button type="button" 
                onclick="toggleInputPassword('<?= $id ?>', this)" 
                class="absolute inset-y-0 right-0 pr-4 flex items-center text-slate-400 hover:text-slate-600 cursor-pointer transition-colors">
                <i data-lucide="eye" class="w-5 h-5"></i>
            </button>
        <?php endif; ?>
    </div>
</div>

<?php if(!isset($passwordScriptAdded)): ?>
    <script>
        function toggleInputPassword(id, btn) {
            const input = document.getElementById(id);
            const icon = btn.querySelector('i');
            
            if (input.type === "password") {
                input.type = "text";
                icon.setAttribute('data-lucide', 'eye-off');
            } else {
                input.type = "password";
                icon.setAttribute('data-lucide', 'eye');
            }
            
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        }
    </script>
    <?php $passwordScriptAdded = true; ?>
<?php endif; ?>
