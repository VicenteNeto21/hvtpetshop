<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Detalhes do Pet<?= $this->endSection() ?>

<?= $this->section('content') ?>
        <!-- Breadcrumb & Header -->
        <div class="mb-8 animate-enter">
            <a href="<?= base_url('pets') ?>" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-brand-600 mb-4 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Voltar para lista
            </a>
            
            <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-brand-50 text-brand-600 flex items-center justify-center text-3xl font-bold shadow-sm border border-brand-100">
                        <?php if(isset($pet['especie']) && strtolower(trim($pet['especie'])) == 'gato'): ?>
                            <i data-lucide="cat" class="w-8 h-8"></i>
                        <?php else: ?>
                            <i data-lucide="dog" class="w-8 h-8"></i>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-slate-900"><?= $pet['nome'] ?></h1>
                        <p class="text-slate-500 font-medium">Paciente #<?= str_pad($pet['id'], 4, '0', STR_PAD_LEFT) ?></p>
                    </div>
                </div>

                <div class="flex gap-3">
                     <a href="<?= base_url('pets/editar/' . $pet['id']) ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-medium hover:bg-white hover:border-slate-300 transition-all flex items-center gap-2">
                        <i data-lucide="edit-2" class="w-4 h-4"></i>
                        Editar
                    </a>
                    <a href="<?= base_url('agenda/novo?pet=' . $pet['id']) ?>" class="px-5 py-2.5 rounded-xl bg-brand-500 text-white font-medium hover:bg-brand-600 shadow-lg shadow-brand-500/20 transition-all flex items-center gap-2">
                        <i data-lucide="calendar-plus" class="w-4 h-4"></i>
                        Novo Agendamento
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-enter" style="animation-delay: 0.1s">
            <!-- Left Column: Pet Info & Tutor -->
            <div class="space-y-6">
                <!-- Pet Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-5">
                        <i data-lucide="dog" class="w-32 h-32 text-slate-900"></i>
                    </div>
                    
                    <h2 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i data-lucide="info" class="w-5 h-5 text-brand-500"></i>
                        Ficha do Animal
                    </h2>

                    <div class="space-y-4 relative z-10">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Espécie</label>
                                <p class="text-slate-700 font-medium"><?= $pet['especie'] ?></p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Raça</label>
                                <p class="text-slate-700 font-medium"><?= $pet['raca'] ?></p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Sexo</label>
                                <p class="text-slate-700 font-medium"><?= $pet['sexo'] == 'M' ? 'Macho' : 'Fêmea' ?></p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Cor</label>
                                <p class="text-slate-700 font-medium"><?= ($pet['cor'] ?? false) ?: '-' ?></p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Peso</label>
                                <p class="text-slate-700 font-medium"><?= ($pet['peso'] ?? false) ? number_format($pet['peso'], 2) . ' kg' : '-' ?></p>
                            </div>
                            <div>
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Nascimento</label>
                                <p class="text-slate-700 font-medium">
                                    <?php 
                                        $nasc = $pet['nascimento'] ?? null;
                                        if ($nasc && $nasc != '0000-00-00' && strtotime($nasc) > 0) {
                                            echo date('d/m/Y', strtotime($nasc));
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </p>
                            </div>
                        </div>
                        
                        <?php if($pet['observacoes']): ?>
                            <div class="pt-4 border-t border-slate-50">
                                <label class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Observações</label>
                                <p class="text-sm text-slate-600 mt-1 bg-slate-50 p-3 rounded-lg border border-slate-100">
                                    <?= nl2br($pet['observacoes']) ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tutor Card -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                        <i data-lucide="user" class="w-5 h-5 text-brand-500"></i>
                        Tutor Responsável
                    </h2>
                    
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-500">
                             <i data-lucide="user" class="w-6 h-6"></i>
                        </div>
                        <div>
                            <p class="font-bold text-slate-800"><?= $pet['tutor_nome'] ?></p>
                            <p class="text-xs text-slate-500">ID: <?= $pet['tutor_id'] ?></p>
                        </div>
                    </div>
                    
                    <div class="space-y-3">
                         <div class="flex items-center gap-3 text-sm text-slate-600 bg-slate-50 p-3 rounded-lg">
                            <i data-lucide="phone" class="w-4 h-4 text-slate-400"></i>
                            <?= $pet['tutor_telefone'] ?: 'Não informado' ?>
                        </div>
                        <a href="<?= base_url('tutores/ver/' . $pet['tutor_id']) ?>" class="flex items-center justify-center gap-2 w-full py-2 text-sm font-medium text-brand-600 border border-brand-200 rounded-lg hover:bg-brand-50 transition-colors">
                            Ver Cadastro Completo
                        </a>
                    </div>
                </div>
            </div>

            <!-- Right Column: Timeline/History -->
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 min-h-[500px]">
                     <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="history" class="w-5 h-5 text-brand-500"></i>
                            Histórico Clínico
                        </h2>
                        <span class="text-xs font-medium text-slate-500 bg-slate-100 px-2 py-1 rounded-full">
                            Últimos 50 registros
                        </span>
                    </div>

                    <?php if(empty($historico)): ?>
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4">
                                <i data-lucide="calendar-off" class="w-8 h-8 text-slate-300"></i>
                            </div>
                            <p class="text-slate-600 font-medium">Nenhum histórico encontrado</p>
                            <p class="text-sm text-slate-400 max-w-xs mt-1">Este pet ainda não realizou agendamentos ou atendimentos no sistema.</p>
                            <a href="<?= base_url('agenda/novo?pet=' . $pet['id']) ?>" class="mt-4 text-brand-600 font-medium hover:underline">
                                Agendar Primeiro Serviço
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="relative pl-4 border-l-2 border-slate-100 space-y-8 ml-2">
                            <?php foreach($historico as $item): ?>
                                <div class="relative">
                                    <div class="absolute -left-[25px] top-0 w-4 h-4 rounded-full border-2 border-white 
                                        <?= $item['status'] == 'Finalizado' ? 'bg-green-500 ring-4 ring-green-50' : 
                                           ($item['status'] == 'Cancelado' ? 'bg-red-400' : 'bg-brand-500') ?>">
                                    </div>
                                    
                                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100 hover:border-brand-200 transition-colors">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                                    <?= $item['servico_nome'] ?>
                                                    <?php if($item['status'] == 'Finalizado'): ?>
                                                        <i data-lucide="check-circle-2" class="w-3 h-3 text-green-500"></i>
                                                    <?php endif; ?>
                                                </h3>
                                                <p class="text-xs text-slate-500 flex items-center gap-1 mt-1">
                                                    <i data-lucide="calendar" class="w-3 h-3"></i>
                                                    <?= date('d/m/Y \à\s H:i', strtotime($item['data_hora'])) ?>
                                                </p>
                                            </div>
                                            <span class="text-[10px] font-bold uppercase tracking-wider px-2 py-1 rounded-full
                                                <?= $item['status'] == 'Finalizado' ? 'bg-green-100 text-green-700' : 
                                                   ($item['status'] == 'Cancelado' ? 'bg-red-100 text-red-700' : 'bg-brand-100 text-brand-700') ?>">
                                                <?= $item['status'] ?>
                                            </span>
                                        </div>
                                        
                                        <?php if($item['observacoes']): ?>
                                            <p class="text-sm text-slate-600 italic bg-white p-2 rounded border border-slate-200/50 mt-2">
                                                “<?= $item['observacoes'] ?>”
                                            </p>
                                        <?php endif; ?>
                                        
                                        <div class="mt-3 flex gap-2">
                                            <a href="<?= base_url('agenda/ficha/' . $item['id']) ?>" class="text-xs font-medium text-slate-400 hover:text-brand-600 flex items-center gap-1 transition-colors">
                                                <i data-lucide="file-text" class="w-3 h-3"></i>
                                                Ver Detalhes
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Carteira de Vacinação -->
                <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="syringe" class="w-5 h-5 text-brand-500"></i>
                            Carteira de Vacinação
                        </h2>
                        <div class="flex gap-2">
                            <a href="<?= base_url('vacinas/imprimir/' . $pet['id']) ?>" target="_blank" class="text-xs font-medium bg-slate-50 hover:bg-slate-100 text-slate-600 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                                <i data-lucide="printer" class="w-3.5 h-3.5"></i>
                                Imprimir
                            </a>
                            <button onclick="openNovaVacinaModal()" class="text-xs font-medium bg-brand-50 hover:bg-brand-100 text-brand-600 px-3 py-1.5 rounded-lg transition-colors flex items-center gap-1">
                                <i data-lucide="plus" class="w-3.5 h-3.5"></i>
                                Nova Vacina
                            </button>
                        </div>
                    </div>

                    <?php if(empty($vacinas)): ?>
                        <div class="flex flex-col items-center justify-center py-8 text-center border border-dashed border-slate-200 rounded-xl bg-slate-50">
                            <i data-lucide="shield-alert" class="w-8 h-8 text-slate-300 mb-2"></i>
                            <p class="text-slate-500 text-sm font-medium">Nenhuma vacina registrada</p>
                        </div>
                    <?php else: ?>
                        <div class="space-y-3">
                            <?php foreach($vacinas as $vacina): ?>
                                <div class="flex items-center justify-between p-4 rounded-xl border <?= $vacina['status'] == 'Pendente' ? 'border-amber-200 bg-amber-50' : 'border-slate-100 bg-white' ?>">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full flex items-center justify-center <?= $vacina['status'] == 'Pendente' ? 'bg-amber-100 text-amber-600' : 'bg-brand-50 text-brand-600' ?>">
                                            <?php if(($vacina['tipo_registro'] ?? 'vacina') == 'medicamento'): ?>
                                                <i data-lucide="pill" class="w-5 h-5"></i>
                                            <?php else: ?>
                                                <i data-lucide="syringe" class="w-5 h-5"></i>
                                            <?php endif; ?>
                                        </div>
                                        <div>
                                            <h3 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                                                <?= $vacina['nome_vacina'] ?>
                                                <?php if(($vacina['recorrencia'] ?? '') == 'anual'): ?>
                                                    <span class="text-[10px] bg-brand-100 text-brand-700 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">Anual</span>
                                                <?php elseif(($vacina['recorrencia'] ?? '') == 'serie'): ?>
                                                    <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">Dose <?= $vacina['dose_atual'] ?>/<?= $vacina['doses_totais'] ?></span>
                                                <?php elseif(strpos(($vacina['recorrencia'] ?? ''), 'personalizado:') === 0): ?>
                                                    <?php 
                                                        list($prefix, $num, $per) = explode(':', $vacina['recorrencia']);
                                                        $perDisplay = $per;
                                                        if ($num == 1 && $per == 'meses') $perDisplay = 'mês';
                                                        elseif ($num == 1 && $per == 'anos') $perDisplay = 'ano';
                                                        elseif ($num == 1 && $per == 'dias') $perDisplay = 'dia';
                                                    ?>
                                                    <span class="text-[10px] bg-brand-100 text-brand-700 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">A cada <?= $num ?> <?= $perDisplay ?></span>
                                                <?php else: ?>
                                                    <span class="text-[10px] bg-slate-100 text-slate-600 px-2 py-0.5 rounded-full font-bold uppercase tracking-wider">Dose Única</span>
                                                <?php endif; ?>
                                            </h3>
                                            <p class="text-xs text-slate-500">
                                                Lote: <?= $vacina['lote'] ?: '-' ?> • Vet: <?= $vacina['veterinario'] ?: '-' ?>
                                            </p>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <?php if($vacina['status'] == 'Pendente'): ?>
                                            <span class="text-xs font-bold text-amber-600 uppercase tracking-wide">Pendente</span>
                                            <p class="text-xs text-amber-700 font-medium mt-1">Para: <?= date('d/m/Y', strtotime($vacina['data_proxima_dose'])) ?></p>
                                        <?php else: ?>
                                            <span class="text-xs font-bold text-green-600 uppercase tracking-wide">Aplicada</span>
                                            <p class="text-xs text-slate-500 mt-1"><?= date('d/m/Y', strtotime($vacina['data_aplicacao'])) ?></p>
                                            <?php if($vacina['data_proxima_dose']): ?>
                                                <p class="text-[10px] text-slate-400">Próx: <?= date('d/m/Y', strtotime($vacina['data_proxima_dose'])) ?></p>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="pl-4 border-l border-slate-100 ml-4 flex gap-2">
                                        <?php if($vacina['status'] == 'Pendente'): ?>
                                            <a href="<?= base_url('vacinas/aplicar/'.$vacina['id']) ?>" class="p-2 bg-white rounded shadow-sm text-brand-600 hover:bg-brand-50 transition-colors tooltip-action" title="Marcar como Aplicada">
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            </a>
                                        <?php endif; ?>
                                        <button type="button" onclick="openEditarVacinaModal(<?= $vacina['id'] ?>, '<?= htmlspecialchars(addslashes($vacina['nome_vacina']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($vacina['tipo_registro'] ?? 'vacina'), ENT_QUOTES) ?>', '<?= $vacina['data_aplicacao'] ?>', '<?= $vacina['data_proxima_dose'] ?>', '<?= htmlspecialchars(addslashes($vacina['lote'] ?? ''), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($vacina['veterinario'] ?? ''), ENT_QUOTES) ?>')" class="p-2 bg-white rounded shadow-sm text-slate-400 hover:text-brand-600 hover:bg-brand-50 transition-colors tooltip-action" title="Editar">
                                            <i data-lucide="edit-2" class="w-4 h-4"></i>
                                        </button>
                                        <button onclick="openConfirmModal('<?= base_url('vacinas/excluir/'.$vacina['id']) ?>', 'Excluir Vacina', 'Tem certeza que deseja excluir este registro?', 'danger', 'trash')" class="p-2 bg-white rounded shadow-sm text-red-500 hover:bg-red-50 transition-colors tooltip-action" title="Excluir">
                                            <i data-lucide="trash" class="w-4 h-4"></i>
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>

        <!-- Modal Nova Vacina -->
        <div id="modal-nova-vacina" class="fixed inset-0 z-[60] hidden" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeNovaVacinaModal()"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0 pointer-events-none">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg pointer-events-auto">
                        <form action="<?= base_url('vacinas/salvar') ?>" method="POST">
                            <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="flex justify-between items-center mb-5">
                                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2" id="modal-nova-vacina-title">
                                        <i data-lucide="syringe" class="w-5 h-5 text-brand-500"></i>
                                        Registrar Vacina
                                    </h3>
                                    <button type="button" onclick="closeNovaVacinaModal()" class="text-slate-400 hover:text-slate-500">
                                        <i data-lucide="x" class="w-5 h-5"></i>
                                    </button>
                                </div>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de Registro</label>
                                            <select name="tipo_registro" class="custom-select w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                                                <option value="vacina">Vacina</option>
                                                <option value="medicamento">Medicamento (Ex: Antipulgas, Vermífugo)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Nome *</label>
                                            <input type="text" name="nome_vacina" required placeholder="Ex: V10, Antirrábica, Simparic" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                                        </div>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de Agendamento</label>
                                        <select name="recorrencia" id="recorrencia" onchange="toggleRecorrenciaFields()" class="custom-select w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                                            <option value="nenhuma">Dose Única</option>
                                            <option value="anual">Anual (Repete em 1 ano após a aplicação)</option>
                                            <option value="serie">Série de Doses (Ex: Vacinas de Filhotes)</option>
                                            <option value="personalizado">Personalizado (Ex: A cada X meses)</option>
                                        </select>
                                    </div>

                                    <div id="serie-fields" class="hidden grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Total de Doses</label>
                                            <input type="number" name="doses_totais" min="2" max="10" value="3" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Intervalo (dias)</label>
                                            <input type="number" name="intervalo_dias" min="1" max="365" value="21" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                                        </div>
                                    </div>

                                    <div id="personalizado-fields" class="hidden grid-cols-2 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Repetir a cada</label>
                                            <input type="number" name="personalizado_numero" min="1" value="1" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Período</label>
                                            <select name="personalizado_periodo" class="custom-select w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none bg-white">
                                                <option value="dias">Dias</option>
                                                <option value="meses" selected>Meses</option>
                                                <option value="anos">Anos</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Data da Aplicação (ou Dose 1)</label>
                                            <input type="date" name="data_aplicacao" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                                            <p class="text-[10px] text-slate-400 mt-1">Deixe vazio se for agendar pro futuro</p>
                                        </div>
                                        <div id="proxima-dose-container">
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Próxima Dose</label>
                                            <input type="date" name="data_proxima_dose" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Lote</label>
                                            <input type="text" name="lote" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Veterinário</label>
                                            <input type="text" name="veterinario" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500 outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-100">
                                <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 sm:ml-3 sm:w-auto transition-colors">
                                    Salvar Registro
                                </button>
                                <button type="button" onclick="closeNovaVacinaModal()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-6 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Editar Vacina -->
        <div id="modal-editar-vacina" class="fixed inset-0 z-[60] hidden" aria-labelledby="modal-editar-title" role="dialog" aria-modal="true">
            <div class="fixed inset-0 bg-slate-900/50 backdrop-blur-sm transition-opacity" onclick="closeEditarVacinaModal()"></div>
            <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
                <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0 pointer-events-none">
                    <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg pointer-events-auto">
                        <form id="form-editar-vacina" method="POST">
                            <input type="hidden" name="pet_id" value="<?= $pet['id'] ?>">
                            <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                                <div class="flex justify-between items-center mb-5">
                                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2" id="modal-editar-title">
                                        <i data-lucide="edit-2" class="w-5 h-5 text-brand-500"></i>
                                        Editar Registro Clínico
                                    </h3>
                                    <button type="button" onclick="closeEditarVacinaModal()" class="text-slate-400 hover:text-slate-500">
                                        <i data-lucide="x" class="w-5 h-5"></i>
                                    </button>
                                </div>
                                <div class="space-y-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Tipo de Registro</label>
                                            <select name="tipo_registro" id="edit_tipo_registro" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none">
                                                <option value="vacina">Vacina</option>
                                                <option value="medicamento">Medicamento</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Nome / Título *</label>
                                            <input type="text" name="nome_vacina" id="edit_nome_vacina" required class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Data Aplicação</label>
                                            <input type="date" name="data_aplicacao" id="edit_data_aplicacao" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Próxima Dose</label>
                                            <input type="date" name="data_proxima_dose" id="edit_data_proxima_dose" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none">
                                        </div>
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Lote</label>
                                            <input type="text" name="lote" id="edit_lote" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-slate-700 mb-1">Veterinário</label>
                                            <input type="text" name="veterinario" id="edit_veterinario" class="w-full px-4 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-brand-500 outline-none">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="bg-slate-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-slate-100">
                                <button type="submit" class="inline-flex w-full justify-center rounded-lg bg-brand-500 px-6 py-2 text-sm font-semibold text-white shadow-sm hover:bg-brand-600 sm:ml-3 sm:w-auto transition-colors">
                                    Salvar Alterações
                                </button>
                                <button type="button" onclick="closeEditarVacinaModal()" class="mt-3 inline-flex w-full justify-center rounded-lg bg-white px-6 py-2 text-sm font-semibold text-slate-700 shadow-sm ring-1 ring-inset ring-slate-300 hover:bg-slate-50 sm:mt-0 sm:w-auto transition-colors">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function openEditarVacinaModal(id, nome, tipo, aplicacao, proxima, lote, vet) {
                document.getElementById('form-editar-vacina').action = '<?= base_url('vacinas/atualizar/') ?>' + id;
                document.getElementById('edit_nome_vacina').value = nome;
                document.getElementById('edit_tipo_registro').value = tipo;
                document.getElementById('edit_data_aplicacao').value = aplicacao;
                document.getElementById('edit_data_proxima_dose').value = proxima;
                document.getElementById('edit_lote').value = lote;
                document.getElementById('edit_veterinario').value = vet;
                
                document.getElementById('modal-editar-vacina').classList.remove('hidden');
            }
            function closeEditarVacinaModal() {
                document.getElementById('modal-editar-vacina').classList.add('hidden');
            }

            function openNovaVacinaModal() {
                document.getElementById('modal-nova-vacina').classList.remove('hidden');
            }
            function closeNovaVacinaModal() {
                document.getElementById('modal-nova-vacina').classList.add('hidden');
            }
            function toggleRecorrenciaFields() {
                const tipo = document.getElementById('recorrencia').value;
                const serieFields = document.getElementById('serie-fields');
                const personalizadoFields = document.getElementById('personalizado-fields');
                const proximaDoseField = document.getElementById('proxima-dose-container');

                serieFields.classList.add('hidden');
                serieFields.classList.remove('grid');
                personalizadoFields.classList.add('hidden');
                personalizadoFields.classList.remove('grid');
                if(proximaDoseField) proximaDoseField.classList.remove('hidden');

                if (tipo === 'serie') {
                    serieFields.classList.remove('hidden');
                    serieFields.classList.add('grid');
                    if(proximaDoseField) proximaDoseField.classList.add('hidden');
                } else if (tipo === 'personalizado') {
                    personalizadoFields.classList.remove('hidden');
                    personalizadoFields.classList.add('grid');
                    if(proximaDoseField) proximaDoseField.classList.add('hidden');
                }
            }

            // Converter Selects Nativos em Dropdowns Customizados
            document.addEventListener('DOMContentLoaded', () => {
                const selects = document.querySelectorAll('select.custom-select');
                selects.forEach(select => {
                    select.style.display = 'none';
                    
                    const wrapper = document.createElement('div');
                    wrapper.className = 'relative w-full custom-dropdown-wrapper';
                    select.parentNode.insertBefore(wrapper, select);
                    wrapper.appendChild(select);
                    
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'w-full px-4 py-2 border border-slate-300 rounded-lg flex justify-between items-center bg-white hover:border-brand-500 focus:outline-none focus:ring-2 focus:ring-brand-500/20 transition-all text-sm text-slate-700 h-[42px]';
                    
                    const textSpan = document.createElement('span');
                    const selectedOpt = select.options[select.selectedIndex];
                    textSpan.textContent = selectedOpt ? selectedOpt.text : '';
                    textSpan.className = 'truncate pr-2';
                    
                    const iconDiv = document.createElement('div');
                    iconDiv.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 shrink-0 transition-transform duration-200"><path d="m6 9 6 6 6-6"/></svg>';
                    const chevron = iconDiv.querySelector('svg');
                    
                    btn.appendChild(textSpan);
                    btn.appendChild(iconDiv);
                    wrapper.appendChild(btn);
                    
                    const dropdown = document.createElement('div');
                    dropdown.className = 'absolute z-[70] w-full mt-1 bg-white border border-slate-100 rounded-xl shadow-xl shadow-slate-200/50 hidden overflow-hidden transform opacity-0 scale-95 transition-all duration-200 origin-top';
                    
                    const ul = document.createElement('ul');
                    ul.className = 'max-h-60 overflow-y-auto py-1.5 scrollbar-thin scrollbar-thumb-slate-200';
                    
                    Array.from(select.options).forEach(opt => {
                        const li = document.createElement('li');
                        li.className = 'px-4 py-2.5 text-sm text-slate-600 hover:bg-brand-50 hover:text-brand-700 cursor-pointer transition-colors flex items-center justify-between group';
                        
                        const liText = document.createElement('span');
                        liText.textContent = opt.text;
                        li.appendChild(liText);
                        
                        const checkIcon = document.createElement('div');
                        checkIcon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-brand-600 opacity-0 transition-opacity"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                        li.appendChild(checkIcon);
                        
                        if (opt.selected) {
                            li.classList.add('bg-slate-50', 'font-medium', 'text-brand-700');
                            checkIcon.querySelector('svg').classList.remove('opacity-0');
                        }
                        
                        li.addEventListener('click', () => {
                            select.value = opt.value;
                            textSpan.textContent = opt.text;
                            
                            ul.querySelectorAll('li').forEach(l => {
                                l.classList.remove('bg-slate-50', 'font-medium', 'text-brand-700');
                                l.querySelector('svg').classList.add('opacity-0');
                            });
                            
                            li.classList.add('bg-slate-50', 'font-medium', 'text-brand-700');
                            checkIcon.querySelector('svg').classList.remove('opacity-0');
                            
                            select.dispatchEvent(new Event('change'));
                            closeDropdown();
                        });
                        ul.appendChild(li);
                    });
                    
                    dropdown.appendChild(ul);
                    wrapper.appendChild(dropdown);
                    
                    function openDropdown() {
                        document.querySelectorAll('.custom-dropdown-wrapper .absolute').forEach(d => {
                            if(d !== dropdown) {
                                d.classList.add('hidden', 'opacity-0', 'scale-95');
                                d.classList.remove('opacity-100', 'scale-100');
                                d.previousElementSibling.querySelector('svg').classList.remove('rotate-180');
                            }
                        });
                        dropdown.classList.remove('hidden');
                        setTimeout(() => {
                            dropdown.classList.remove('opacity-0', 'scale-95');
                            dropdown.classList.add('opacity-100', 'scale-100');
                            chevron.classList.add('rotate-180');
                        }, 10);
                    }
                    
                    function closeDropdown() {
                        dropdown.classList.remove('opacity-100', 'scale-100');
                        dropdown.classList.add('opacity-0', 'scale-95');
                        chevron.classList.remove('rotate-180');
                        setTimeout(() => {
                            dropdown.classList.add('hidden');
                        }, 200);
                    }
                    
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        if (dropdown.classList.contains('hidden')) {
                            openDropdown();
                        } else {
                            closeDropdown();
                        }
                    });
                    
                    document.addEventListener('click', (e) => {
                        if (!wrapper.contains(e.target)) {
                            closeDropdown();
                        }
                    });
                });
            });
        </script>
    </main>
</div>
<?= view('components/modal_confirm') ?>
<?= $this->endSection() ?>
