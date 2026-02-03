<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Dashboard<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex min-h-screen bg-slate-50">
    <!-- Sidebar -->
    <?= view('components/sidebar') ?>

    <!-- Main Content -->
    <main class="flex-1 md:ml-64 p-4 md:p-8 overflow-x-hidden">
        <!-- Topbar -->
        <header class="flex justify-between items-center mb-8 animate-enter">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">Visão Geral</h1>
                <p class="text-slate-500">Bem-vindo, <?= session()->get('usuario_nome') ?>!</p>
            </div>
            <div class="flex items-center gap-4">
               <button class="md:hidden p-2 text-slate-600 rounded-lg hover:bg-slate-100">
                    <i data-lucide="menu" class="w-6 h-6"></i>
               </button>
               <div class="hidden md:flex items-center gap-3 bg-white px-4 py-2 rounded-full border border-slate-200 shadow-sm">
                   <div class="w-8 h-8 rounded-full bg-brand-100 flex items-center justify-center text-brand-600 font-bold text-sm">
                       <?= substr(session()->get('usuario_nome'), 0, 1) ?>
                   </div>
                   <span class="text-sm font-medium text-slate-700"><?= session()->get('usuario_nome') ?></span>
               </div>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 animate-enter" style="animation-delay: 0.1s">
            <!-- Card 1 -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 bg-brand-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 mb-4 border border-slate-200">
                         <i data-lucide="calendar" class="w-6 h-6"></i>
                    </div>
                    <p class="text-slate-500 text-sm font-medium">Agendamentos Hoje</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= $stats['agendamentos_hoje'] ?></h3>
                </div>
            </div>

            <!-- Card 2 -->
             <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 bg-orange-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative">
                     <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 mb-4 border border-slate-200">
                          <i data-lucide="clock" class="w-6 h-6"></i>
                     </div>
                     <p class="text-slate-500 text-sm font-medium">Pendentes</p>
                     <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= $stats['pendentes'] ?></h3>
                     <a href="<?= base_url('agenda?status=Pendente') ?>" class="absolute inset-0 z-10" title="Ver Pendentes"></a>
                 </div>
            </div>

            <!-- Card 3 -->
             <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 bg-violet-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 mb-4 border border-slate-200">
                         <i data-lucide="dog" class="w-6 h-6"></i>
                    </div>
                    <p class="text-slate-500 text-sm font-medium">Total Pets</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= $stats['total_pets'] ?></h3>
                </div>
            </div>

            <!-- Card 4 -->
             <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-shadow relative overflow-hidden group">
                <div class="absolute right-0 top-0 w-24 h-24 bg-blue-50 rounded-bl-full -mr-4 -mt-4 transition-transform group-hover:scale-110"></div>
                <div class="relative">
                    <div class="w-12 h-12 rounded-xl bg-slate-100 flex items-center justify-center text-slate-600 mb-4 border border-slate-200">
                         <i data-lucide="users" class="w-6 h-6"></i>
                    </div>
                    <p class="text-slate-500 text-sm font-medium">Tutores</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-1"><?= $stats['total_tutores'] ?></h3>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 animate-enter" style="animation-delay: 0.15s">
            <!-- Agenda Table (Col Span 2) -->
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
                <div class="flex justify-between items-center mb-6">
                    <div class="flex items-center gap-4">
                        <button onclick="navegarData(-1)" class="p-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors" title="Dia anterior">
                            <i data-lucide="chevron-left" class="w-5 h-5"></i>
                        </button>
                        
                        <div class="text-center min-w-[140px]">
                            <h2 class="text-lg font-bold text-slate-800 flex items-center justify-center gap-2">
                                <i data-lucide="calendar-check" class="w-5 h-5 text-brand-500"></i>
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
                        
                        <button onclick="irParaHoje()" class="px-3 py-1.5 rounded-lg text-xs font-medium <?= $dataSelecionada == date('Y-m-d') ? 'bg-brand-500 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' ?> transition-colors" id="btn-hoje">
                            Hoje
                        </button>
                    </div>
                    
                    <a href="<?= base_url('agenda/novo') ?>" class="flex items-center gap-2 bg-brand-500 hover:bg-brand-600 text-white px-4 py-2 rounded-xl text-sm font-medium shadow-sm shadow-brand-500/20 transition-colors">
                        <i data-lucide="plus" class="w-4 h-4"></i>
                        Novo Agendamento
                    </a>
                </div>

                <div class="overflow-x-auto">
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
                        <tbody id="agenda-tbody" class="divide-y divide-slate-50">
                            <?php if(empty($agenda)): ?>
                                <tr>
                                    <td colspan="5" class="p-8 text-center text-slate-400 text-sm italic">
                                        Nenhum agendamento para hoje.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach($agenda as $ag): ?>
                                    <tr class="hover:bg-slate-50/80 transition-colors group">
                                        <td class="p-3 font-semibold text-slate-700"><?= date('H:i', strtotime($ag['data_hora'])) ?></td>
                                        <td class="p-3">
                                            <div class="font-medium text-slate-800"><?= $ag['pet_nome'] ?></div>
                                            <div class="text-xs text-slate-400"><?= $ag['tutor_nome'] ?></div>
                                        </td>
                                        <td class="p-3 text-sm text-slate-600">
                                            <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-xs font-medium">
                                                <?= $ag['servico_nome'] ?>
                                            </span>
                                        </td>
                                        <td class="p-3 text-center">
                                            <?php
                                                $statusColors = [
                                                    'Pendente' => 'bg-amber-100 text-amber-700 border-amber-200',
                                                    'Em Atendimento' => 'bg-blue-100 text-blue-700 border-blue-200',
                                                    'Finalizado' => 'bg-green-100 text-green-700 border-green-200',
                                                    'Cancelado' => 'bg-red-100 text-red-700 border-red-200'
                                                ];
                                                $colorClass = $statusColors[$ag['status']] ?? 'bg-slate-100 text-slate-700';
                                            ?>
                                            <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold border <?= $colorClass ?>">
                                                <?= $ag['status'] ?>
                                            </span>
                                        </td>
                                        <td class="p-3 text-center">
                                            <div class="flex justify-center gap-1">
                                                <?php if($ag['status'] == 'Pendente'): ?>
                                                    <a href="<?= base_url('agenda/concluir/' . $ag['id']) ?>" 
                                                       class="p-1.5 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition-colors" 
                                                       title="Iniciar Atendimento">
                                                        <i data-lucide="play" class="w-4 h-4"></i>
                                                    </a>
                                                    <a href="<?= base_url('agenda/cancelar/' . $ag['id']) ?>" 
                                                       class="p-1.5 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors" 
                                                       title="Cancelar"
                                                       onclick="return confirm('Cancelar este agendamento?')">
                                                        <i data-lucide="x" class="w-4 h-4"></i>
                                                    </a>
                                                <?php elseif($ag['status'] == 'Finalizado'): ?>
                                                    <a href="<?= base_url('agenda/concluir/' . $ag['id']) ?>" 
                                                       class="p-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors" 
                                                       title="Ver Ficha">
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
            </div>

            <!-- Aniversariantes & Quick Actions -->
            <div class="space-y-6">
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

    </main>
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
        document.getElementById('agenda-tbody').innerHTML = `
            <tr>
                <td colspan="4" class="p-8 text-center text-slate-400 text-sm">
                    <i data-lucide="loader-2" class="w-6 h-6 animate-spin inline-block"></i>
                    Carregando...
                </td>
            </tr>
        `;
        
        try {
            const response = await fetch('<?= base_url('dashboard/agenda-data') ?>?data=' + data);
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
            
            // Atualizar tabela de agenda
            if (dados.agenda.length === 0) {
                document.getElementById('agenda-tbody').innerHTML = `
                    <tr>
                        <td colspan="4" class="p-8 text-center text-slate-400 text-sm italic">
                            Nenhum agendamento para este dia.
                        </td>
                    </tr>
                `;
            } else {
                let html = '';
                dados.agenda.forEach(ag => {
                    const dataObj = new Date(ag.data_hora);
                    const horario = dataObj.toLocaleTimeString('pt-BR', {hour: '2-digit', minute: '2-digit'});
                    const base_url = '<?= base_url() ?>';
                    
                    const statusColors = {
                        'Pendente': 'bg-amber-100 text-amber-700 border-amber-200',
                        'Em Atendimento': 'bg-blue-100 text-blue-700 border-blue-200',
                        'Finalizado': 'bg-green-100 text-green-700 border-green-200',
                        'Cancelado': 'bg-red-100 text-red-700 border-red-200'
                    };
                    const colorClass = statusColors[ag.status] || 'bg-slate-100 text-slate-700';
                    
                    let acoes = '';
                    if (ag.status === 'Pendente') {
                        acoes = `
                            <a href="${base_url}/agenda/concluir/${ag.id}" class="p-1.5 rounded-lg bg-green-100 text-green-600 hover:bg-green-200 transition-colors" title="Iniciar Atendimento">
                                <i data-lucide="play" class="w-4 h-4"></i>
                            </a>
                            <a href="${base_url}/agenda/cancelar/${ag.id}" class="p-1.5 rounded-lg bg-red-100 text-red-600 hover:bg-red-200 transition-colors" title="Cancelar" onclick="return confirm('Cancelar este agendamento?')">
                                <i data-lucide="x" class="w-4 h-4"></i>
                            </a>
                        `;
                    } else if (ag.status === 'Finalizado') {
                        acoes = `
                            <a href="${base_url}/agenda/concluir/${ag.id}" class="p-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors" title="Ver Ficha">
                                <i data-lucide="file-text" class="w-4 h-4"></i>
                            </a>
                        `;
                    }

                    html += `
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="p-3 font-semibold text-slate-700">${horario}</td>
                            <td class="p-3">
                                <div class="font-medium text-slate-800">${ag.pet_nome}</div>
                                <div class="text-xs text-slate-400">${ag.tutor_nome}</div>
                            </td>
                            <td class="p-3 text-sm text-slate-600">
                                <span class="bg-indigo-50 text-indigo-700 px-2 py-0.5 rounded text-xs font-medium">
                                    ${ag.servico_nome}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <span class="inline-block px-2.5 py-1 rounded-full text-xs font-semibold border ${colorClass}">
                                    ${ag.status}
                                </span>
                            </td>
                            <td class="p-3 text-center">
                                <div class="flex justify-center gap-1">
                                    ${acoes}
                                </div>
                            </td>
                        </tr>
                    `;
                });
                document.getElementById('agenda-tbody').innerHTML = html;
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
</script>
<?= $this->endSection() ?>
