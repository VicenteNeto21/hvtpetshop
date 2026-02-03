<!-- Tutor Select Component -->
<div class="relative">
    <label class="block text-sm font-medium text-slate-700 mb-1">Tutor (Proprietário) *</label>
    <div class="relative">
        <select id="select-tutor" name="tutor_id" required class="w-full">
            <option value="">Selecione um tutor...</option>
            <?php foreach ($tutores as $t): ?>
                <option value="<?= $t['id'] ?>" <?= (isset($selected_id) && $selected_id == $t['id']) ? 'selected' : '' ?>>
                    <?= $t['nome'] ?>
                </option>
            <?php endforeach; ?>
        </select>
        <!-- Icon Overlay (Will be handled by TomSelect rendering or CSS) -->
        <i data-lucide="user" class="w-5 h-5 absolute left-4 top-1/2 -translate-y-1/2 z-10 text-slate-400 pointer-events-none" id="tutor-icon"></i>
    </div>
</div>

<!-- Styles specific to making TomSelect look like our standard inputs -->
<style>
    .ts-control {
        border-radius: 0.75rem !important; /* rounded-xl */
        padding-top: 0.75rem !important; /* py-3 ish to match height */
        padding-bottom: 0.75rem !important;
        padding-left: 3rem !important; /* pl-12 */
        border-color: #e2e8f0 !important; /* border-slate-200 */
        box-shadow: none !important;
        background-color: transparent !important;
        min-height: 46px !important; /* Ensure same height as standard inputs */
        display: flex !important;
        align-items: center !important;
    }
    .ts-control.focus {
        border-color: #6366f1 !important; /* brand-500 */
        box-shadow: 0 0 0 1px #6366f1 !important;
    }
    .ts-dropdown {
        border-radius: 0.75rem !important;
        border-color: #e2e8f0 !important;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1) !important;
        margin-top: 4px !important;
        z-index: 50 !important;
    }
    .ts-dropdown .option {
        padding: 10px 15px !important;
    }
    .ts-dropdown .active {
        background-color: #f8fafc !important; /* slate-50 */
        color: #4f46e5 !important; /* brand-600 */
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        if(document.getElementById('select-tutor')) {
            new TomSelect("#select-tutor", {
                create: false,
                sortField: { block: "text", direction: "asc" }, // Correção na ordenação
                placeholder: "Busque pelo nome...",
                plugins: ['dropdown_input'],
                render: {
                    option: function(data, escape) {
                        return '<div class="flex items-center gap-3">' +
                                '<div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center text-slate-500 font-bold text-xs">' + escape(data.text.charAt(0)) + '</div>' +
                                '<div class="flex flex-col leading-tight">' +
                                    '<span class="font-medium text-slate-700">' + escape(data.text) + '</span>' +
                                '</div>' +
                            '</div>';
                    },
                    item: function(data, escape) {
                        return '<div>' + escape(data.text) + '</div>';
                    }
                },
                onInitialize: function() {
                    // Ensure icon stays visible
                    document.getElementById('tutor-icon').style.display = 'block';
                }
            });
        }
    });
</script>
