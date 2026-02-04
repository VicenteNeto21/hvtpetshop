<?php 
/**
 * Componente: Custom Select Dropdown (Sem usar o select nativo do browser)
 * Argumentos: 
 * - id: string (requerido) - ID único para o componente
 * - name: string (requerido) - Nome do campo para o formulário
 * - label: string - Rótulo acima do campo
 * - options: array (requerido) - Array de opções [['value' => '', 'text' => '', 'icon' => '' (opcional)], ...]
 * - selected: string - Valor selecionado por padrão
 * - icon: string (Lucide icon name) - Ícone no botão
 * - placeholder: string - Texto quando nada selecionado
 * - required: bool
 * - extraClass: string - Classes extras para o container
 * - onchange: string - Função JS para chamar ao mudar
 */
$isRequired = ($required ?? false) ? 'required' : '';
$selectedValue = $selected ?? ($options[0]['value'] ?? '');
$selectedText = '';
$selectedIcon = '';

// Encontrar o texto e ícone do item selecionado
foreach ($options as $opt) {
    if ($opt['value'] === $selectedValue) {
        $selectedText = $opt['text'];
        $selectedIcon = $opt['icon'] ?? '';
        break;
    }
}
if (empty($selectedText) && !empty($options)) {
    $selectedText = $options[0]['text'];
    $selectedValue = $options[0]['value'];
    $selectedIcon = $options[0]['icon'] ?? '';
}
?>

<div class="<?= $extraClass ?? '' ?>">
    <?php if(!empty($label)): ?>
        <label class="block text-sm font-medium text-slate-600 mb-1"><?= $label ?></label>
    <?php endif; ?>

    <div class="relative custom-select-wrapper" id="<?= $id ?>-wrapper">
        <!-- Hidden Input for Form Submission -->
        <input type="hidden" name="<?= $name ?>" id="<?= $id ?>" value="<?= $selectedValue ?>" <?= $isRequired ?>>

        <!-- Styled Button (Trigger) -->
        <button type="button" 
            onclick="toggleCustomSelect('<?= $id ?>')"
            class="w-full flex items-center gap-3 p-2.5 bg-slate-50 border border-slate-200 rounded-xl text-left text-slate-700 hover:border-slate-300 focus:ring-2 focus:ring-brand-500/30 focus:border-brand-500 focus:bg-white transition-all outline-none"
            id="<?= $id ?>-button"
            aria-haspopup="listbox"
            aria-expanded="false">
            
            <?php if(!empty($icon)): ?>
                <i data-lucide="<?= $icon ?>" class="w-5 h-5 text-slate-400 shrink-0"></i>
            <?php endif; ?>

            <?php if(!empty($selectedIcon)): ?>
                <i data-lucide="<?= $selectedIcon ?>" class="w-5 h-5 text-brand-500 shrink-0" id="<?= $id ?>-selected-icon"></i>
            <?php endif; ?>
            
            <span class="flex-1 truncate" id="<?= $id ?>-selected-text"><?= $selectedText ?: ($placeholder ?? 'Selecione...') ?></span>
            
            <i data-lucide="chevron-down" class="w-4 h-4 text-slate-400 shrink-0 transition-transform duration-200" id="<?= $id ?>-chevron"></i>
        </button>

        <!-- Dropdown Options -->
        <div class="absolute z-50 w-full mt-2 bg-white border border-slate-200 rounded-xl shadow-lg opacity-0 invisible transform -translate-y-2 transition-all duration-200 max-h-60 overflow-y-auto"
             id="<?= $id ?>-dropdown"
             role="listbox">
            <?php foreach ($options as $opt): ?>
                <div class="flex items-center gap-3 px-4 py-2.5 cursor-pointer hover:bg-brand-50 transition-colors <?= $opt['value'] === $selectedValue ? 'bg-brand-50 text-brand-700' : 'text-slate-700' ?>"
                     data-value="<?= $opt['value'] ?>"
                     data-text="<?= $opt['text'] ?>"
                     data-icon="<?= $opt['icon'] ?? '' ?>"
                     onclick="selectCustomOption('<?= $id ?>', this, '<?= $onchange ?? '' ?>')"
                     role="option"
                     aria-selected="<?= $opt['value'] === $selectedValue ? 'true' : 'false' ?>">
                    
                    <?php if(!empty($opt['icon'])): ?>
                        <i data-lucide="<?= $opt['icon'] ?>" class="w-5 h-5 <?= $opt['value'] === $selectedValue ? 'text-brand-600' : 'text-slate-400' ?>"></i>
                    <?php endif; ?>
                    
                    <span class="flex-1"><?= $opt['text'] ?></span>
                    
                    <?php if($opt['value'] === $selectedValue): ?>
                        <i data-lucide="check" class="w-4 h-4 text-brand-600"></i>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php if(!isset($customSelectScriptAdded)): ?>
<script>
    // Toggle dropdown visibility
    function toggleCustomSelect(id) {
        const dropdown = document.getElementById(id + '-dropdown');
        const chevron = document.getElementById(id + '-chevron');
        const button = document.getElementById(id + '-button');
        const isOpen = dropdown.classList.contains('opacity-100');

        // Close all other dropdowns first
        document.querySelectorAll('.custom-select-wrapper [id$="-dropdown"]').forEach(d => {
            if (d.id !== id + '-dropdown') {
                d.classList.remove('opacity-100', 'visible', 'translate-y-0');
                d.classList.add('opacity-0', 'invisible', '-translate-y-2');
            }
        });
        document.querySelectorAll('.custom-select-wrapper [id$="-chevron"]').forEach(c => {
            if (c.id !== id + '-chevron') {
                c.classList.remove('rotate-180');
            }
        });

        if (isOpen) {
            dropdown.classList.remove('opacity-100', 'visible', 'translate-y-0');
            dropdown.classList.add('opacity-0', 'invisible', '-translate-y-2');
            chevron.classList.remove('rotate-180');
            button.setAttribute('aria-expanded', 'false');
        } else {
            dropdown.classList.remove('opacity-0', 'invisible', '-translate-y-2');
            dropdown.classList.add('opacity-100', 'visible', 'translate-y-0');
            chevron.classList.add('rotate-180');
            button.setAttribute('aria-expanded', 'true');
        }
    }

    // Select an option
    function selectCustomOption(id, element, onchangeFunc) {
        const value = element.dataset.value;
        const text = element.dataset.text;
        const icon = element.dataset.icon;
        
        const hiddenInput = document.getElementById(id);
        const selectedText = document.getElementById(id + '-selected-text');
        const selectedIcon = document.getElementById(id + '-selected-icon');
        const dropdown = document.getElementById(id + '-dropdown');

        // Update hidden input
        hiddenInput.value = value;

        // Update displayed text
        selectedText.textContent = text;

        // Update icon if exists
        if (selectedIcon && icon) {
            selectedIcon.setAttribute('data-lucide', icon);
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }

        // Update visual state of options
        dropdown.querySelectorAll('[role="option"]').forEach(opt => {
            if (opt.dataset.value === value) {
                opt.classList.add('bg-brand-50', 'text-brand-700');
                opt.setAttribute('aria-selected', 'true');
            } else {
                opt.classList.remove('bg-brand-50', 'text-brand-700');
                opt.setAttribute('aria-selected', 'false');
            }
        });

        // Close dropdown
        toggleCustomSelect(id);

        // Call onchange function if provided
        if (onchangeFunc && typeof window[onchangeFunc] === 'function') {
            window[onchangeFunc]();
        }
    }

    // Close dropdown when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.custom-select-wrapper')) {
            document.querySelectorAll('.custom-select-wrapper [id$="-dropdown"]').forEach(d => {
                d.classList.remove('opacity-100', 'visible', 'translate-y-0');
                d.classList.add('opacity-0', 'invisible', '-translate-y-2');
            });
            document.querySelectorAll('.custom-select-wrapper [id$="-chevron"]').forEach(c => {
                c.classList.remove('rotate-180');
            });
            document.querySelectorAll('.custom-select-wrapper [id$="-button"]').forEach(b => {
                b.setAttribute('aria-expanded', 'false');
            });
        }
    });
</script>
<?php $customSelectScriptAdded = true; ?>
<?php endif; ?>
