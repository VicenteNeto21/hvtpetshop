<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Agenda do Dia<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex min-h-screen bg-slate-50">
    <?= view('components/sidebar') ?>

    <main class="flex-1 md:ml-64 p-4 md:p-8">
        <!-- Header -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4 mb-8 animate-enter">
            <div>
                <h1 class="text-3xl font-bold text-slate-900">Agenda de Serviços</h1>
                <p class="text-slate-500 mt-1">Gerencie os banhos, tosas e consultas do dia.</p>
            </div>
            <a href="<?= base_url('agenda/novo') ?>" class="px-5 py-2.5 bg-brand-500 text-white font-bold rounded-xl shadow-lg shadow-brand-500/20 hover:bg-brand-600 transition-all flex items-center gap-2">
                <i data-lucide="plus" class="w-5 h-5"></i>
                Novo Agendamento
            </a>
        </div>

        <!-- Date Navigation Bar -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-4 mb-8 flex flex-col md:flex-row justify-between items-center gap-4 animate-enter" style="animation-delay: 0.1s">
            <div class="flex items-center gap-4 w-full md:w-auto">
                <a href="<?= base_url('agenda?data=' . date('Y-m-d', strtotime($dataSelecionada . ' -1 day'))) ?>" 
                   class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-brand-600 transition-colors">
                    <i data-lucide="chevron-left" class="w-6 h-6"></i>
                </a>
                
                <div class="flex-1 text-center md:text-left">
                    <h2 class="text-xl font-bold text-slate-800 capitalize">
                        <?php 
                            $dateObj = \DateTime::createFromFormat('Y-m-d', $dataSelecionada);
                            $formatter = new \IntlDateFormatter('pt_BR', \IntlDateFormatter::FULL, \IntlDateFormatter::NONE);
                            echo $dataSelecionada == date('Y-m-d') ? 'Hoje, ' . $dateObj->format('d/m') : $formatter->format($dateObj);
                        ?>
                    </h2>
                </div>

                <a href="<?= base_url('agenda?data=' . date('Y-m-d', strtotime($dataSelecionada . ' +1 day'))) ?>" 
                   class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-brand-600 transition-colors">
                    <i data-lucide="chevron-right" class="w-6 h-6"></i>
                </a>
            </div>

            <!-- Date Picker Jump -->
            <form action="<?= base_url('agenda') ?>" method="GET" class="flex items-center gap-2">
                <label for="data" class="text-sm font-medium text-slate-600 hidden md:block">Ir para:</label>
                <input type="date" name="data" value="<?= $dataSelecionada ?>" 
                       onchange="this.form.submit()"
                       class="border border-slate-200 rounded-lg px-3 py-2 text-sm text-slate-600 focus:outline-none focus:border-brand-500 focus:ring-1 focus:ring-brand-500">
            </form>
        </div>

        <!-- Filter/Tabs -->
        <div class="flex gap-4 mb-6 overflow-x-auto pb-2 animate-enter" style="animation-delay: 0.15s">
            <a href="<?= base_url('agenda?data=' . $dataSelecionada) ?>" 
               class="px-4 py-2 rounded-full text-sm font-bold shadow-sm whitespace-nowrap transition-colors
               <?= !$statusSelecionado ? 'bg-slate-800 text-white shadow-md' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                Todos
            </a>
            <a href="<?= base_url('agenda?data=' . $dataSelecionada . '&status=Pendente') ?>" 
               class="px-4 py-2 rounded-full text-sm font-bold shadow-sm whitespace-nowrap transition-colors
               <?= $statusSelecionado == 'Pendente' ? 'bg-amber-500 text-white shadow-md border-amber-500' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                Pendentes
            </a>
            <a href="<?= base_url('agenda?data=' . $dataSelecionada . '&status=Finalizado') ?>" 
               class="px-4 py-2 rounded-full text-sm font-bold shadow-sm whitespace-nowrap transition-colors
               <?= $statusSelecionado == 'Finalizado' ? 'bg-emerald-500 text-white shadow-md border-emerald-500' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' ?>">
                Finalizados
            </a>
        </div>

        <!-- Agenda List -->
        <div class="space-y-4 animate-enter" style="animation-delay: 0.2s">
            <?php if(empty($agendamentos)): ?>
                <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center">
                    <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i data-lucide="calendar-check" class="w-8 h-8 text-slate-300"></i>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900">Agenda Livre</h3>
                    <p class="text-slate-500">Nenhum agendamento para este dia.</p>
                </div>
            <?php else: ?>
                <?php foreach($agendamentos as $item): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 flex flex-col md:flex-row items-start md:items-center gap-4 hover:border-brand-300 transition-all group">
                        
                        <!-- Time Column -->
                        <div class="flex md:flex-col items-center gap-2 pr-4 border-b md:border-b-0 md:border-r border-slate-100 min-w-[100px] pb-4 md:pb-0">
                            <i data-lucide="clock" class="w-5 h-5 text-brand-500"></i>
                            <span class="text-2xl font-bold text-slate-800">
                                <?= date('H:i', strtotime($item['data_hora'])) ?>
                            </span>
                        </div>

                        <!-- Info Column -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-1">
                                <h3 class="text-lg font-bold text-slate-800"><?= $item['pet_nome'] ?></h3>
                                <span class="px-2 py-0.5 rounded text-xs font-bold uppercase tracking-wider
                                    <?= $item['status'] == 'Pendente' ? 'bg-amber-100 text-amber-700' : 
                                       ($item['status'] == 'Finalizado' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700') ?>">
                                    <?= $item['status'] ?>
                                </span>
                            </div>
                            <p class="text-slate-600 text-sm mb-1">
                                <span class="font-medium">Serviço:</span> <?= $item['servico_nome'] ?>
                            </p>
                            <div class="flex items-center gap-4 text-xs text-slate-400">
                                <span class="flex items-center gap-1">
                                    <i data-lucide="user" class="w-3 h-3"></i> <?= $item['tutor_nome'] ?>
                                </span>
                                <?php if($item['transporte']): ?>
                                    <span class="flex items-center gap-1 text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">
                                        <i data-lucide="car" class="w-3 h-3"></i> <?= $item['transporte'] ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php if($item['observacoes']): ?>
                                <p class="mt-2 text-sm text-slate-500 italic bg-amber-50 p-2 rounded-lg border border-amber-100 inline-block">
                                    "<?= $item['observacoes'] ?>"
                                </p>
                            <?php endif; ?>
                        </div>

                        <!-- Actions Column -->
                        <div class="flex gap-2 w-full md:w-auto mt-4 md:mt-0 pt-4 md:pt-0 border-t md:border-t-0 border-slate-100">
                            <?php if ($item['status'] != 'Finalizado' && $item['status'] != 'Cancelado'): ?>
                                <!-- Botão Editar (Futuro: Link para editar) -->
                                <button class="flex-1 md:flex-none p-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-50 hover:text-brand-600 transition-colors" title="Editar">
                                    <i data-lucide="edit-2" class="w-5 h-5 mx-auto"></i>
                                </button>
                                
                                <!-- Botão Concluir -->
                                <button onclick="openConfirmModal('<?= base_url('agenda/concluir/' . $item['id']) ?>', 'Concluir Serviço', 'Deseja marcar este agendamento como finalizado?', 'success', 'check-circle')" 
                                   class="flex-1 md:flex-none p-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-green-50 hover:text-green-600 transition-colors" title="Concluir">
                                    <i data-lucide="check-circle-2" class="w-5 h-5 mx-auto"></i>
                                </button>

                                <!-- Botão Cancelar -->
                                <button onclick="openConfirmModal('<?= base_url('agenda/cancelar/' . $item['id']) ?>', 'Cancelar Agendamento', 'Tem certeza que deseja cancelar este agendamento? Esta ação não pode ser desfeita.', 'danger', 'x-circle')"
                                   class="flex-1 md:flex-none p-2 rounded-lg border border-slate-200 text-slate-600 hover:bg-red-50 hover:text-red-600 transition-colors" title="Cancelar">
                                    <i data-lucide="x-circle" class="w-5 h-5 mx-auto"></i>
                                </button>
                            <?php else: ?>
                                <span class="text-xs text-slate-400 italic">Ações indisponíveis</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </main>
</div>
<?= view('components/modal_confirm') ?>
<?= $this->endSection() ?>
