<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Início<?= $this->endSection() ?>

<?= $this->section('content') ?>
<!-- Topbar -->
<header class="mb-6 animate-enter">
    <h1 class="text-xl sm:text-2xl font-bold text-slate-900">Início</h1>
    <p class="text-slate-500 text-sm">Resumo de Atendimentos do Dia</p>
</header>

<!-- Stats Grid -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6 animate-enter" style="animation-delay: 0.1s">
    <!-- Card 1 -->
    <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-16 h-16 sm:w-24 sm:h-24 bg-brand-50 rounded-bl-full -mr-2 -mt-2 sm:-mr-4 sm:-mt-4 transition-transform group-hover:scale-110"></div>
        <div class="relative">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 mb-3 sm:mb-4 border border-slate-200">
                 <i data-lucide="calendar" class="w-5 h-5 sm:w-6 sm:h-6"></i>
            </div>
            <p class="text-slate-500 text-xs sm:text-sm font-medium">Agend. Hoje</p>
            <h3 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1"><?= $stats['agendamentos_hoje'] ?></h3>
        </div>
    </div>

    <!-- Card 2 -->
     <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-16 h-16 sm:w-24 sm:h-24 bg-orange-50 rounded-bl-full -mr-2 -mt-2 sm:-mr-4 sm:-mt-4 transition-transform group-hover:scale-110"></div>
        <div class="relative">
             <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 mb-3 sm:mb-4 border border-slate-200">
                  <i data-lucide="clock" class="w-5 h-5 sm:w-6 sm:h-6"></i>
             </div>
             <p class="text-slate-500 text-xs sm:text-sm font-medium">Pendentes</p>
             <h3 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1"><?= $stats['pendentes'] ?></h3>
             <a href="<?= base_url('agenda?status=Pendente') ?>" class="absolute inset-0 z-10" title="Ver Pendentes"></a>
         </div>
    </div>

    <!-- Card 3 -->
     <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-16 h-16 sm:w-24 sm:h-24 bg-violet-50 rounded-bl-full -mr-2 -mt-2 sm:-mr-4 sm:-mt-4 transition-transform group-hover:scale-110"></div>
        <div class="relative">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 mb-3 sm:mb-4 border border-slate-200">
                 <i data-lucide="dog" class="w-5 h-5 sm:w-6 sm:h-6"></i>
            </div>
            <p class="text-slate-500 text-xs sm:text-sm font-medium">Total Pets</p>
            <h3 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1"><?= $stats['total_pets'] ?></h3>
        </div>
    </div>

    <!-- Card 4 -->
     <div class="bg-white p-4 sm:p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute right-0 top-0 w-16 h-16 sm:w-24 sm:h-24 bg-blue-50 rounded-bl-full -mr-2 -mt-2 sm:-mr-4 sm:-mt-4 transition-transform group-hover:scale-110"></div>
        <div class="relative">
            <div class="w-10 h-10 sm:w-12 sm:h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 mb-3 sm:mb-4 border border-slate-200">
                 <i data-lucide="users" class="w-5 h-5 sm:w-6 sm:h-6"></i>
            </div>
            <p class="text-slate-500 text-xs sm:text-sm font-medium">Tutores</p>
            <h3 class="text-2xl sm:text-3xl font-bold text-slate-800 mt-1"><?= $stats['total_tutores'] ?></h3>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 sm:gap-8 animate-enter" style="animation-delay: 0.15s">
            <!-- Agenda Table (Col Span 2) -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-4 sm:p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-4">
                    <div class="flex items-center gap-2 sm:gap-4 w-full sm:w-auto justify-between sm:justify-start">
                        <button onclick="navegarData(-1)" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" title="Dia anterior">
                            <i data-lucide="chevron-left" class="w-5 h-5"></i>
                        </button>
                        
                        <div class="text-center flex-1 sm:flex-none sm:min-w-[140px]">
                            <h2 class="text-base sm:text-lg font-bold text-slate-800 flex items-center justify-center gap-2">
                                <i data-lucide="calendar-check" class="w-4 h-4 sm:w-5 sm:h-5 text-brand-500"></i>
                                <span id="data-dia"><?php 
                                    $diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
                                    echo $diasSemana[(int)date('w', strtotime($dataSelecionada))] . ', ' . date('d/m', strtotime($dataSelecionada)); 
                                ?></span>
                            </h2>
                            <?php if($dataSelecionada == date('Y-m-d')): ?>
                                <span class="text-xs text-brand-600 font-medium">Hoje</span>
                            <?php elseif($dataSelecionada == date('Y-m-d', strtotime('+1 day'))): ?>
                                <span class="text-xs text-amber-600 font-medium">Amanhã</span>
                            <?php elseif($dataSelecionada == date('Y-m-d', strtotime('-1 day'))): ?>
                                <span class="text-xs text-slate-500 font-medium">Ontem</span>
                            <?php endif; ?>
                        </div>
                        
                        <button onclick="navegarData(1)" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" title="Próximo dia">
                            <i data-lucide="chevron-right" class="w-5 h-5"></i>
                        </button>
                        
                        <button onclick="irParaHoje()" class="px-3 py-1.5 rounded-lg text-xs font-medium <?= $dataSelecionada == date('Y-m-d') ? 'bg-brand-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?> transition-colors hidden sm:block" id="btn-hoje">
                            Hoje
                        </button>
                    </div>
                    
                    <a href="<?= base_url('agenda/novo') ?>" class="flex items-center justify-center gap-2 bg-brand-500 hover:bg-brand-600 text-white px-4 py-2.5 rounded-xl text-sm font-medium shadow-sm shadow-brand-500/20 transition-colors w-full sm:w-auto">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        <span class="sm:hidden">Agendar</span>
                        <span class="hidden sm:inline">Novo Agendamento</span>
                    </a>
                </div>

                <!-- Desktop Table (hidden on mobile) -->
                <div class="hidden sm:block">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-100">
                                <th class="p-3 text-xs font-semibold text-slate-500 uppercase">Horário</th>
                                <th class="p-3 text-xs font-semibold text-slate-500 uppercase">Pet/Tutor</th>
                                <th class="p-3 text-xs font-semibold text-slate-500 uppercase">Serviço</th>
                                <th class="p-3 text-xs font-semibold text-slate-500 uppercase text-center">Status</th>
                                <th class="p-3 text-xs font-semibold text-slate-500 uppercase text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody id="agenda-tbody-desktop" class="divide-y divide-slate-50">
                            <?php if(empty($agenda)): ?>
                                <tr>
                                    <td colspan="5" class="p-12 text-center">
                                        <div class="flex flex-col items-center justify-center text-slate-400">
                                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                                                <i data-lucide="sun" class="w-8 h-8 text-slate-300"></i>
                                            </div>
                                            <p class="text-sm font-medium text-slate-500">A agenda está livre por enquanto.</p>
                                            <p class="text-xs mt-1">Aproveite para organizar o espaço!</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($agenda as $ag): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors group">
                                        <td class="p-3 font-semibold text-slate-700"><?= date('H:i', strtotime($ag['data_hora'])) ?></td>
                                        <td class="p-3">
                                            <div class="flex items-center gap-3">
                                                <?php
                                                    $inicial = strtoupper(substr($ag['pet_nome'], 0, 1));
                                                    $cores = ['bg-indigo-100 text-indigo-600', 'bg-pink-100 text-pink-600', 'bg-amber-100 text-amber-600', 'bg-emerald-100 text-emerald-600', 'bg-cyan-100 text-cyan-600'];
                                                    $corIdx = hexdec(substr(md5($ag['pet_nome']), 0, 1)) % count($cores);
                                                    $corClasse = $cores[$corIdx];
                                                ?>
                                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg shrink-0 <?= $corClasse ?>">
                                                    <?= $inicial ?>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-slate-800"><?= $ag['pet_nome'] ?></div>
                                                    <div class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                                        <i data-lucide="user" class="w-3 h-3"></i> <?= $ag['tutor_nome'] ?>
                                                        <?php if($ag['tutor_telefone']): ?>
                                                            <?php 
                                                                $fone = preg_replace('/[^0-9]/', '', $ag['tutor_telefone']);
                                                                if(strlen($fone) >= 10 && substr($fone, 0, 2) !== '55') $fone = '55'.$fone;
                                                                
                                                                $hora = date('H:i', strtotime($ag['data_hora']));
                                                                if ($ag['status'] === 'Finalizado') {
                                                                    $msg = urlencode("Olá {$ag['tutor_nome']}! O(a) {$ag['pet_nome']} já finalizou o serviço de {$ag['servico_nome']} e está pronto(a) para ir para casa. Pode vir buscar! 🐾");
                                                                } else {
                                                                    $msg = urlencode("Olá {$ag['tutor_nome']}! Tudo bem? Passando para lembrar do nosso horário para o(a) {$ag['pet_nome']} hoje às {$hora}. Qualquer dúvida estamos à disposição! 🐶");
                                                                }
                                                            ?>
                                                            <a href="https://wa.me/<?= $fone ?>?text=<?= $msg ?>" target="_blank" class="ml-1 inline-flex text-[#25D366] hover:bg-[#25D366]/10 p-0.5 rounded transition-colors" title="Avisar pelo WhatsApp">
                                                                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                                            </a>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="p-3 text-sm text-slate-600">
                                            <span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-md text-xs font-semibold whitespace-nowrap">
                                                <?= $ag['servico_nome'] ?>
                                            </span>
                                        </td>
                                        <td class="p-3 text-center">
                                            <?php
                                                $statusColors = [
                                                    'Pendente' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                    'Em Atendimento' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                                    'Finalizado' => 'bg-brand-500 text-white border-brand-600',
                                                    'Cancelado' => 'bg-red-100 text-red-700 border-red-200'
                                                ];
                                                $colorClass = $statusColors[$ag['status']] ?? 'bg-slate-100 text-slate-700';
                                            ?>
                                            <span class="inline-block px-2.5 py-1 rounded-full text-[11px] uppercase tracking-wider font-bold border <?= $colorClass ?>">
                                                <?= $ag['status'] ?>
                                            </span>
                                        </td>
                                        <td class="p-3 text-center">
                                            <div class="flex justify-center gap-1">
                                                <?php if($ag['status'] == 'Pendente'): ?>
                                                    <a href="<?= base_url('agenda/concluir/' . $ag['id']) ?>" 
                                                       class="p-1.5 rounded-lg bg-brand-100 text-brand-600 hover:bg-brand-200 transition-colors" 
                                                       title="Iniciar Atendimento">
                                                        <i data-lucide="play" class="w-4 h-4"></i>
                                                    </a>
                                                    <button onclick="openConfirmModal('<?= base_url('agenda/cancelar/' . $ag['id']) ?>', 'Cancelar Agendamento', 'Tem certeza que deseja cancelar este agendamento?', 'danger', 'x-circle')"
                                                            class="p-1.5 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors" 
                                                            title="Cancelar">
                                                        <i data-lucide="x" class="w-4 h-4"></i>
                                                    </button>
                                                    <button onclick="openConfirmModal('<?= base_url('agenda/excluir/' . $ag['id']) ?>', 'EXCLUIR Agendamento', 'Deseja excluir permanentemente este agendamento?', 'danger', 'trash-2')"
                                                            class="p-1.5 rounded-lg bg-slate-100 text-slate-500 hover:bg-red-100 hover:text-red-700 transition-colors" 
                                                            title="Excluir Permanentemente">
                                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                                    </button>
                                                <?php elseif($ag['status'] == 'Finalizado'): ?>
                                                    <a href="<?= base_url('agenda/ficha/' . $ag['id']) ?>" 
                                                       class="p-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors" 
                                                       title="Ver Ficha Clínica">
                                                        <i data-lucide="file-text" class="w-4 h-4"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards (hidden on desktop) -->
                <div id="agenda-tbody" class="sm:hidden space-y-3">
                    <?php if(empty($agenda)): ?>
                        <div class="p-6 text-center text-slate-400 text-sm italic">
                            Nenhum agendamento para hoje.
                        </div>
                    <?php else: ?>
                        <?php foreach($agenda as $ag): ?>
                            <?php
                                $statusColors = [
                                    'Pendente' => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'Em Atendimento' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                    'Finalizado' => 'bg-brand-500 text-white border-brand-600',
                                    'Cancelado' => 'bg-red-100 text-red-700 border-red-200'
                                ];
                                $colorClass = $statusColors[$ag['status']] ?? 'bg-slate-100 text-slate-700';
                            ?>
                            <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                                <!-- Top row: Time + Status -->
                                <div class="flex justify-between items-center mb-3">
                                    <span class="text-lg font-bold text-slate-800"><?= date('H:i', strtotime($ag['data_hora'])) ?></span>
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold border <?= $colorClass ?>">
                                        <?= $ag['status'] ?>
                                    </span>
                                </div>
                                
                                <!-- Pet and Tutor -->
                                <div class="mb-2">
                                    <p class="font-semibold text-slate-800"><?= $ag['pet_nome'] ?></p>
                                    <p class="text-xs text-slate-500"><?= $ag['tutor_nome'] ?></p>
                                </div>
                                
                                <!-- Service -->
                                <div class="mb-3">
                                    <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-xs font-medium">
                                        <?= $ag['servico_nome'] ?>
                                    </span>
                                </div>
                                
                                <!-- Actions -->
                                <div class="flex gap-2 pt-2 border-t border-slate-200">
                                    <?php if($ag['status'] == 'Pendente'): ?>
                                        <a href="<?= base_url('agenda/concluir/' . $ag['id']) ?>" 
                                           class="flex-1 flex items-center justify-center gap-1 py-2 rounded-lg bg-brand-500 text-white text-xs font-medium hover:bg-brand-600 transition-colors">
                                            <i data-lucide="play" class="w-3.5 h-3.5"></i>
                                            Iniciar
                                        </a>
                                        <button onclick="openConfirmModal('<?= base_url('agenda/cancelar/' . $ag['id']) ?>', 'Cancelar Agendamento', 'Tem certeza que deseja cancelar este agendamento?', 'danger', 'x-circle')"
                                                class="flex items-center justify-center gap-1 px-4 py-2 rounded-lg bg-red-100 text-red-600 text-xs font-medium hover:bg-red-200 transition-colors">
                                            <i data-lucide="x" class="w-3.5 h-3.5"></i>
                                        </button>
                                        <button onclick="openConfirmModal('<?= base_url('agenda/excluir/' . $ag['id']) ?>', 'EXCLUIR Agendamento', 'Deseja excluir permanentemente este agendamento?', 'danger', 'trash-2')"
                                                class="flex items-center justify-center gap-1 px-4 py-2 rounded-lg bg-slate-200 text-slate-600 text-xs font-medium hover:bg-red-100 hover:text-red-700 transition-colors">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    <?php elseif($ag['status'] == 'Finalizado'): ?>
                                        <a href="<?= base_url('agenda/ficha/' . $ag['id']) ?>" 
                                           class="flex-1 flex items-center justify-center gap-1 py-2 rounded-lg bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition-colors">
                                            <i data-lucide="file-text" class="w-3.5 h-3.5"></i>
                                            Ver Ficha
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-slate-400 italic">Sem ações disponíveis</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Aniversariantes & Quick Actions -->
            <div class="space-y-6">
                <!-- Vacinas Vencendo Alerta -->
                 <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden <?= !empty($vacinasVencendo) ? 'border-amber-200 bg-amber-50/30' : '' ?>">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <i data-lucide="shield-alert" class="w-16 h-16 <?= !empty($vacinasVencendo) ? 'text-amber-500' : 'text-slate-400' ?>"></i>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 relative z-10">
                        <i data-lucide="syringe" class="w-5 h-5 <?= !empty($vacinasVencendo) ? 'text-amber-500' : 'text-brand-500' ?>"></i>
                         Lembretes de Vacinas
                    </h2>
                    
                    <div class="space-y-3 relative z-10 max-h-64 overflow-y-auto pr-2">
                         <?php if(empty($vacinasVencendo)): ?>
                            <p class="text-sm text-slate-400 italic">Tudo em dia! Nenhuma vacina pendente para os próximos 15 dias.</p>
                         <?php else: ?>
                            <?php foreach($vacinasVencendo as $vacina): ?>
                                <?php 
                                    $dias = (strtotime($vacina['data_proxima_dose']) - strtotime('today')) / (60 * 60 * 24);
                                    $isAtrasada = $dias < 0;
                                ?>
                                <div class="flex items-center gap-3 bg-white p-3 rounded-xl border <?= $isAtrasada ? 'border-red-200' : 'border-amber-100' ?>">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center shrink-0 <?= $isAtrasada ? 'bg-red-100 text-red-500' : 'bg-amber-100 text-amber-500' ?>">
                                        <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex justify-between items-start">
                                            <p class="font-bold text-slate-800 text-sm truncate w-full flex items-center gap-1">
                                                <a href="<?= base_url('pets/ver/'.$vacina['pet_id']) ?>" class="hover:text-brand-600 transition-colors" title="Ver ficha de <?= $vacina['pet_nome'] ?>"><?= $vacina['pet_nome'] ?></a>
                                                <span class="text-[10px] bg-slate-100 text-slate-500 px-1.5 py-0.5 rounded ml-1"><?= $vacina['nome_vacina'] ?></span>
                                            </p>
                                        </div>
                                        <p class="text-xs text-slate-500 truncate flex items-center gap-1 mt-0.5">
                                            <i data-lucide="phone" class="w-3 h-3 text-slate-400"></i>
                                            <?= $vacina['tutor_nome'] ?> (<?= $vacina['tutor_telefone'] ?: 'S/N' ?>)
                                            <?php if($vacina['tutor_telefone']): ?>
                                                <?php 
                                                    $foneLimpo = preg_replace('/[^0-9]/', '', $vacina['tutor_telefone']);
                                                    if (strlen($foneLimpo) >= 10 && substr($foneLimpo, 0, 2) !== '55') {
                                                        $foneLimpo = '55' . $foneLimpo;
                                                    }
                                                    $textoWhats = urlencode("Olá {$vacina['tutor_nome']}! Aqui é da HVT Petshop. Vimos que o registro de {$vacina['nome_vacina']} do(a) {$vacina['pet_nome']} vence dia " . date('d/m/Y', strtotime($vacina['data_proxima_dose'])) . ". Podemos agendar um horário para mantermos a saúde dele em dia?");
                                                ?>
                                                <a href="https://wa.me/<?= $foneLimpo ?>?text=<?= $textoWhats ?>" target="_blank" class="ml-1 inline-flex items-center justify-center bg-[#25D366]/10 text-[#25D366] hover:bg-[#25D366]/20 p-1 rounded-full transition-colors" title="Enviar WhatsApp">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                                </a>
                                            <?php endif; ?>
                                        </p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <?php if($isAtrasada): ?>
                                            <span class="text-[10px] font-bold text-red-600 uppercase bg-red-50 px-1.5 py-0.5 rounded block text-center">Atrasada</span>
                                            <span class="text-xs text-red-500 font-medium"><?= abs(floor($dias)) ?> dias</span>
                                        <?php elseif($dias == 0): ?>
                                            <span class="text-[10px] font-bold text-amber-600 uppercase bg-amber-50 px-1.5 py-0.5 rounded block text-center">Hoje!</span>
                                        <?php else: ?>
                                            <span class="text-[10px] font-bold text-slate-500 uppercase bg-slate-100 px-1.5 py-0.5 rounded block text-center">Em</span>
                                            <span class="text-xs text-slate-600 font-medium"><?= floor($dias) ?> dias</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                         <?php endif; ?>
                    </div>
                 </div>

                <!-- Aniversariantes -->
                 <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden">
                    <div class="absolute top-0 right-0 p-4 opacity-10">
                        <i data-lucide="cake" class="w-16 h-16 text-pink-500"></i>
                    </div>
                    <h2 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2 relative z-10">
                        <i data-lucide="cake" class="w-5 h-5 text-pink-500"></i>
                         Aniversariantes
                    </h2>
                    
                    <div id="aniversariantes-lista" class="space-y-3 relative z-10">
                         <?php if(empty($aniversariantes)): ?>
                            <p class="text-sm text-slate-400 italic">Nenhum pet fazendo festa hoje.</p>
                         <?php else: ?>
                            <?php foreach($aniversariantes as $niver): ?>
                                <div class="flex items-center gap-3 bg-pink-50/50 p-3 rounded-xl border border-pink-100">
                                    <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-500">
                                        <i data-lucide="paw-print" class="w-4 h-4"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-800 text-sm"><?= $niver['pet_nome'] ?></p>
                                        <p class="text-xs text-slate-500">Tutor: <?= $niver['tutor_nome'] ?></p>
                                    </div>
                                    <i data-lucide="gift" class="w-5 h-5 text-pink-400 ml-auto"></i>
                                </div>
                            <?php endforeach; ?>
                         <?php endif; ?>
                    </div>
                 </div>
                 
                 <?php if($isAdmin): ?>
                 <!-- Admin Quick Access -->
                 <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 relative overflow-hidden">
                    <div class="flex justify-between items-start mb-4">
                        <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                            <i data-lucide="shield-check" class="w-5 h-5 text-indigo-500"></i>
                            Administração
                        </h2>
                        <?php if($stats['usuarios_pendentes'] > 0): ?>
                            <span class="flex h-2 w-2 relative">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                            </span>
                        <?php endif; ?>
                    </div>
                    
                    <div class="space-y-3">
                        <a href="<?= base_url('usuarios?status=pendente') ?>" class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition-colors group">
                            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center text-indigo-600 group-hover:bg-indigo-100 transition-colors">
                                <i data-lucide="users" class="w-5 h-5"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-slate-700">Usuários Pendentes</p>
                                <p class="text-xs text-slate-500"><?= $stats['usuarios_pendentes'] ?> aguardando aprovação</p>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
                        </a>

                        <a href="<?= base_url('admin') ?>" class="flex items-center gap-3 p-3 rounded-xl border border-slate-100 hover:bg-slate-50 transition-colors group">
                            <div class="w-10 h-10 rounded-lg bg-slate-50 flex items-center justify-center text-slate-600 group-hover:bg-slate-100 transition-colors">
                                <i data-lucide="bar-chart-2" class="w-5 h-5"></i>
                            </div>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-slate-700">Relatórios Gerais</p>
                                <p class="text-xs text-slate-500">Visão técnica do sistema</p>
                            </div>
                            <i data-lucide="chevron-right" class="w-4 h-4 text-slate-300"></i>
                        </a>
                    </div>
                 </div>
                 <?php endif; ?>
                 
                 <!-- Quick Actions -->
                 <div class="bg-gradient-to-br from-brand-500 to-brand-600 rounded-2xl shadow-lg shadow-brand-500/20 p-6 text-white">
                     <h3 class="font-bold text-lg mb-2">Acesso Rápido</h3>
                     <p class="text-brand-100 text-sm mb-4">Atalhos para as funções mais usadas.</p>
                     
                     <div class="grid grid-cols-2 gap-3">
                         <a href="<?= base_url('pets/novo') ?>" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm p-3 rounded-xl text-center transition-colors">
                             <div class="flex justify-center mb-1"><i data-lucide="plus" class="w-6 h-6"></i></div>
                             <span class="text-xs font-medium">Novo Pet</span>
                         </a>
                         <a href="<?= base_url('tutores/novo') ?>" class="bg-white/10 hover:bg-white/20 backdrop-blur-sm p-3 rounded-xl text-center transition-colors">
                             <div class="flex justify-center mb-1"><i data-lucide="user-plus" class="w-6 h-6"></i></div>
                             <span class="text-xs font-medium">Novo Tutor</span>
                         </a>
                     </div>
                 </div>
            </div>
        </div>


<script>
    // Data atual selecionada
    let dataSelecionada = '<?= $dataSelecionada ?>';
    const hoje = '<?= date('Y-m-d') ?>';
    const diasSemana = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sáb'];
    
    // Navegar para dia anterior/próximo
    function navegarData(direcao) {
        const dataAtual = new Date(dataSelecionada + 'T00:00:00');
        dataAtual.setDate(dataAtual.getDate() + direcao);
        const novaData = dataAtual.toISOString().split('T')[0];
        carregarAgenda(novaData);
    }
    
    // Ir para hoje
    function irParaHoje() {
        carregarAgenda(hoje);
    }
    
    // Função para carregar dados via AJAX
    async function carregarAgenda(data) {
        dataSelecionada = data;
        
        // Atualizar URL sem recarregar
        history.pushState({}, '', '?data=' + data);
        
        // Mostrar loading
        const loadingDesktop = `<tr><td colspan="5" class="p-8 text-center text-slate-400 text-sm"><i data-lucide="loader-2" class="w-6 h-6 animate-spin inline-block"></i> Carregando...</td></tr>`;
        const loadingMobile = `<div class="p-8 text-center text-slate-400 text-sm"><i data-lucide="loader-2" class="w-6 h-6 animate-spin inline-block"></i> Carregando...</div>`;
        
        document.getElementById('agenda-tbody-desktop').innerHTML = loadingDesktop;
        document.getElementById('agenda-tbody').innerHTML = loadingMobile;
        
        try {
            const response = await fetch('<?= base_url('inicio/agenda-data') ?>?data=' + data);
            const dados = await response.json();
            
            // Atualizar display de data no header
            const dataObj = new Date(data + 'T00:00:00');
            const diaFormatado = diasSemana[dataObj.getDay()] + ', ' + dados.dataFormatada.dia + '/' + String(dataObj.getMonth() + 1).padStart(2, '0');
            document.getElementById('data-dia').textContent = diaFormatado;
            
            // Atualizar botão Hoje
            const btnHoje = document.getElementById('btn-hoje');
            if (data === hoje) {
                btnHoje.className = 'px-3 py-1.5 rounded-lg text-xs font-medium bg-brand-500 text-white transition-colors';
            } else {
                btnHoje.className = 'px-3 py-1.5 rounded-lg text-xs font-medium bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors';
            }
            
            // Atualizar tabela de agenda (Desktop e Mobile)
            if (dados.agenda.length === 0) {
                const emptyDesktop = `<tr><td colspan="5" class="p-12 text-center"><div class="flex flex-col items-center justify-center text-slate-400"><div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3"><i data-lucide="sun" class="w-8 h-8 text-slate-300"></i></div><p class="text-sm font-medium text-slate-500">A agenda está livre por enquanto.</p><p class="text-xs mt-1">Aproveite para organizar o espaço!</p></div></td></tr>`;
                const emptyMobile = `<div class="p-12 text-center flex flex-col items-center"><div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3"><i data-lucide="sun" class="w-8 h-8 text-slate-300"></i></div><p class="text-sm font-medium text-slate-500">A agenda está livre.</p></div>`;
                
                document.getElementById('agenda-tbody-desktop').innerHTML = emptyDesktop;
                document.getElementById('agenda-tbody').innerHTML = emptyMobile;
            } else {
                let htmlDesktop = '';
                let htmlMobile = '';
                
                dados.agenda.forEach(ag => {
                    const dataObj = new Date(ag.data_hora);
                    const horario = dataObj.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
                    const base_url = '<?= base_url() ?>';
                    
                    const statusColors = {
                        'Pendente': 'bg-amber-100 text-amber-700 border-amber-200',
                        'Em Atendimento': 'bg-indigo-100 text-indigo-700 border-indigo-200',
                        'Finalizado': 'bg-brand-500 text-white border-brand-600',
                        'Cancelado': 'bg-red-100 text-red-700 border-red-200'
                    };
                    const colorClass = statusColors[ag.status] || 'bg-slate-100 text-slate-700';
                    
                    // Logic for Pet Avatar
                    const inicial = ag.pet_nome.substring(0, 1).toUpperCase();
                    const bgColors = ['bg-indigo-100 text-indigo-600', 'bg-pink-100 text-pink-600', 'bg-amber-100 text-amber-600', 'bg-emerald-100 text-emerald-600', 'bg-cyan-100 text-cyan-600'];
                    // Simple hash for index
                    let hash = 0;
                    for (let i = 0; i < ag.pet_nome.length; i++) hash += ag.pet_nome.charCodeAt(i);
                    const avatarClass = bgColors[hash % bgColors.length];
                    
                    // Logic for WhatsApp
                    let whatsHtml = '';
                    if (ag.tutor_telefone) {
                        let fone = ag.tutor_telefone.replace(/[^0-9]/g, '');
                        if(fone.length >= 10 && !fone.startsWith('55')) fone = '55' + fone;
                        let msg = '';
                        if (ag.status === 'Finalizado') {
                            msg = encodeURIComponent(`Olá ${ag.tutor_nome}! O(a) ${ag.pet_nome} já finalizou o serviço de ${ag.servico_nome} e está pronto(a) para ir para casa. Pode vir buscar! 🐾`);
                        } else {
                            msg = encodeURIComponent(`Olá ${ag.tutor_nome}! Tudo bem? Passando para lembrar do nosso horário para o(a) ${ag.pet_nome} hoje às ${horario}. Qualquer dúvida estamos à disposição! 🐶`);
                        }
                        whatsHtml = `<a href="https://wa.me/${fone}?text=${msg}" target="_blank" class="ml-1 inline-flex text-[#25D366] hover:bg-[#25D366]/10 p-0.5 rounded transition-colors" title="Avisar pelo WhatsApp">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                    </a>`;
                    }
                    
                    let acoes = '';
                    let acoesMobile = '';
                    
                    if (ag.status === 'Pendente') {
                        acoes = `
                            <a href="${base_url}/agenda/concluir/${ag.id}" class="p-1.5 rounded-lg bg-brand-100 text-brand-600 hover:bg-brand-200 transition-colors" title="Iniciar Atendimento">
                                <i data-lucide="play" class="w-4 h-4"></i>
                            </a>
                            <button onclick="openConfirmModal('${base_url}/agenda/cancelar/${ag.id}', 'Cancelar Agendamento', 'Tem certeza que deseja cancelar este agendamento?', 'danger', 'x-circle')"
                                    class="p-1.5 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors" title="Cancelar">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </button>
                            <button onclick="openConfirmModal('${base_url}/agenda/excluir/${ag.id}', 'EXCLUIR Agendamento', 'Deseja excluir permanentemente este agendamento?', 'danger', 'trash-2')"
                                    class="p-1.5 rounded-lg bg-slate-100 text-slate-500 hover:bg-red-100 hover:text-red-700 transition-colors" title="Excluir Permanentemente">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        `;
                        acoesMobile = `
                            <a href="${base_url}/agenda/concluir/${ag.id}" class="flex-1 flex items-center justify-center gap-1 py-2 rounded-lg bg-brand-500 text-white text-xs font-medium hover:bg-brand-600 transition-colors">
                                <i data-lucide="play" class="w-3.5 h-3.5"></i> Iniciar
                            </a>
                        `;
                    } else if (ag.status === 'Finalizado') {
                        acoes = `
                            <a href="${base_url}/agenda/ficha/${ag.id}" class="p-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors" title="Ver Ficha">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                            </a>
                        `;
                        acoesMobile = `
                            <a href="${base_url}/agenda/ficha/${ag.id}" class="flex-1 flex items-center justify-center gap-1 py-2 rounded-lg bg-slate-100 text-slate-600 text-xs font-medium hover:bg-slate-200 transition-colors">
                                <i data-lucide="file-text" class="w-3.5 h-3.5"></i> Ver Ficha
                            </a>
                        `;
                    }

                    // Desktop Row
                    htmlDesktop += `
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="p-3 font-semibold text-slate-700">${horario}</td>
                            <td class="p-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg shrink-0 ${avatarClass}">
                                        ${inicial}
                                    </div>
                                    <div>
                                        <div class="font-bold text-slate-800">${ag.pet_nome}</div>
                                        <div class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                                            <i data-lucide="user" class="w-3 h-3"></i> ${ag.tutor_nome} ${whatsHtml}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="p-3 text-sm text-slate-600">
                                <span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-md text-xs font-semibold whitespace-nowrap">
                                    ${ag.servico_nome}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="inline-block px-2.5 py-1 rounded-full text-[11px] uppercase tracking-wider font-bold border ${colorClass}">
                                    ${ag.status}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex justify-center gap-1">${acoes}</div>
                            </td>
                        </tr>
                    `;
                    
                    // Mobile Card
                    htmlMobile += `
                        <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                            <div class="flex justify-between items-center mb-3">
                                <span class="text-lg font-bold text-slate-800">${horario}</span>
                                <span class="px-2.5 py-1 rounded-full text-[10px] uppercase tracking-wider font-bold border ${colorClass}">${ag.status}</span>
                            </div>
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-lg shrink-0 ${avatarClass}">
                                    ${inicial}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-800">${ag.pet_nome}</p>
                                    <p class="text-xs text-slate-500 flex items-center gap-1"><i data-lucide="user" class="w-3 h-3"></i> ${ag.tutor_nome} ${whatsHtml}</p>
                                </div>
                            </div>
                            <div class="mb-3">
                                <span class="bg-indigo-50 text-indigo-700 px-2.5 py-1 rounded-md text-xs font-semibold">${ag.servico_nome}</span>
                            </div>
                            <div class="flex gap-2 pt-2 border-t border-slate-200">${acoesMobile || '<span class="text-xs text-slate-400 italic">Sem ações disponíveis</span>'}</div>
                        </div>
                    `;
                });
                
                document.getElementById('agenda-tbody-desktop').innerHTML = htmlDesktop;
                document.getElementById('agenda-tbody').innerHTML = htmlMobile;
            }
            
            // Atualizar aniversariantes
            if (dados.aniversariantes.length === 0) {
                document.getElementById('aniversariantes-lista').innerHTML = `
                    <p class="text-sm text-slate-400 italic">Nenhum pet fazendo festa neste dia.</p>
                `;
            } else {
                let htmlNiver = '';
                dados.aniversariantes.forEach(niver => {
                    htmlNiver += `
                        <div class="flex items-center gap-3 bg-pink-50/50 p-3 rounded-xl border border-pink-100">
                            <div class="w-10 h-10 rounded-full bg-pink-100 flex items-center justify-center text-pink-500">
                                <i data-lucide="paw-print" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="font-bold text-slate-800 text-sm">${niver.pet_nome}</p>
                                <p class="text-xs text-slate-500">Tutor: ${niver.tutor_nome}</p>
                            </div>
                            <i data-lucide="gift" class="w-5 h-5 text-pink-400 ml-auto"></i>
                        </div>
                    `;
                });
                document.getElementById('aniversariantes-lista').innerHTML = htmlNiver;
            }
            
            // Recriar ícones Lucide
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
            
        } catch (error) {
            console.error('Erro ao carregar agenda:', error);
        }
    }
    
    // Auto-refresh a cada 5 minutos para recepção
    setInterval(() => {
        if (dataSelecionada === hoje) {
            carregarAgenda(hoje);
        }
    }, 5 * 60 * 1000);
</script>
<?= view('components/modal_confirm') ?>
<?= $this->endSection() ?>
