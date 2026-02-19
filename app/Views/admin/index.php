<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Administração<?= $this->endSection() ?>

<?= $this->section('styles') ?>
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800;900&display=swap" rel="stylesheet">
<style>
    /* Estilos do Relatório */
    #report-template {
        font-family: 'Outfit', sans-serif;
    }
    
    /* Estilos para Impressão */
    @media print {
        /* Esconde tudo exceto o relatório */
        body * {
            visibility: hidden;
        }
        
        #report-overlay,
        #report-overlay * {
            visibility: visible;
        }
        
        #report-overlay {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            background: white;
        }
        
        #report-template {
            width: 100% !important;
            min-height: auto !important;
            padding: 15mm !important;
            margin: 0 !important;
            box-shadow: none !important;
        }
        
        /* Esconder elementos específicos */
        .no-print,
        button[onclick="fecharRelatorio()"] {
            display: none !important;
        }
        
        /* Quebras de página */
        table { page-break-inside: auto; }
        tr { page-break-inside: avoid; }
        
        /* Preservar cores de fundo */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }
    
    /* Configuração de página */
    @page {
        size: A4;
        margin: 10mm;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="animate-enter print:bg-white print:ml-0 print:p-0 print:max-w-none">
        <!-- Header & Filter -->
        <div class="mb-8 animate-enter print:hidden">
            <h1 class="text-3xl font-bold text-slate-900 mb-2">Painel Administrativo</h1>
            <p class="text-slate-500 mb-6">Relatórios gerenciais e indicadores de desempenho.</p>

            <form action="<?= base_url('admin') ?>" method="GET" class="bg-white p-4 rounded-2xl shadow-sm border border-slate-200 flex flex-col md:flex-row gap-4 items-end md:items-center">
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Data Inicial</label>
                    <input type="date" name="data_inicial" value="<?= $dataInicial ?>" class="w-full p-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <div class="flex-1 w-full">
                    <label class="block text-xs font-bold text-slate-500 uppercase mb-1">Data Final</label>
                    <input type="date" name="data_final" value="<?= $dataFinal ?>" class="w-full p-2.5 rounded-xl border border-slate-200 focus:ring-2 focus:ring-brand-500 outline-none">
                </div>
                <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-brand-500 text-white font-bold rounded-xl hover:bg-brand-600 transition-colors shadow-lg shadow-brand-500/20 flex items-center justify-center gap-2">
                    <i data-lucide="filter" class="w-4 h-4"></i>
                    Filtrar Dados
                </button>
                <a href="<?= base_url('admin/relatorio') ?>?data_inicial=<?= $dataInicial ?>&data_final=<?= $dataFinal ?>" class="flex-1 md:flex-none px-6 py-2.5 bg-slate-800 text-white font-bold rounded-xl hover:bg-slate-700 transition-colors flex items-center justify-center gap-2">
                    <i data-lucide="printer" class="w-4 h-4"></i>
                    Gerar Relatório
                </a>
            </form>
        </div>

        <!-- Relatório Print Header -->
        <div class="hidden print:block mb-8 border-b border-slate-200 pb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-slate-900">Relatório Gerencial</h1>
                    <p class="text-slate-500 mt-1">HVT Petshop - Relatório de Desempenho</p>
                </div>
                <div class="text-right">
                    <p class="text-sm text-slate-500 font-bold uppercase tracking-wide">Período de Análise</p>
                    <p class="text-lg font-bold text-slate-800">
                        <?= date('d/m/Y', strtotime($dataInicial)) ?> a <?= date('d/m/Y', strtotime($dataFinal)) ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- 1. Cards KPI -->
        <div class="grid grid-cols-2 lg:grid-cols-7 gap-4 lg:gap-6 mb-8 print:grid-cols-7 print:gap-4 animate-enter" style="animation-delay: 0.1s">
            <!-- Faturamento -->
            <div class="col-span-2 lg:col-span-1 bg-white p-5 rounded-2xl shadow-sm border border-slate-100 relative overflow-hidden group print:border print:border-slate-300">
                 <div class="absolute right-0 top-0 w-20 h-20 bg-emerald-50 rounded-bl-full -mr-4 -mt-4 print:hidden"></div>
                 <div class="relative">
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wide mb-1">Faturamento Gerado</p>
                    <h3 class="text-2xl lg:text-3xl font-bold text-emerald-600">R$ <?= number_format($stats['faturamento'], 2, ',', '.') ?></h3>
                    <p class="text-xs text-slate-400 mt-1">Serviços finalizados</p>
                 </div>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 print:border print:border-slate-300">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wide mb-1">Atendimentos</p>
                <h3 class="text-2xl lg:text-3xl font-bold text-slate-800"><?= $stats['finalizados'] ?></h3>
                <p class="text-xs text-emerald-600 font-medium">Concluídos</p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 print:border print:border-slate-300">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wide mb-1">Aguardando</p>
                <h3 class="text-2xl lg:text-3xl font-bold text-amber-600"><?= $stats['pendentes'] ?></h3>
                <p class="text-xs text-amber-400 font-medium">Pendentes</p>
            </div>

             <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 print:border print:border-slate-300">
                 <p class="text-slate-500 text-xs font-bold uppercase tracking-wide mb-1">Cancelamentos</p>
                 <h3 class="text-2xl lg:text-3xl font-bold text-red-500"><?= $stats['cancelados'] ?></h3>
                 <p class="text-xs text-red-300">No período</p>
            </div>
            
            <!-- Total Pets -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 print:border print:border-slate-300">
                <div class="flex items-center gap-2 mb-1">
                    <i data-lucide="paw-print" class="w-4 h-4 text-brand-500"></i>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wide">Total Pets</p>
                </div>
                <h3 class="text-2xl lg:text-3xl font-bold text-slate-800"><?= $stats['total_pets'] ?></h3>
                <p class="text-xs text-slate-400">Cadastrados</p>
            </div>

            <!-- Tutores -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 print:border print:border-slate-300">
                <div class="flex items-center gap-2 mb-1">
                    <i data-lucide="users" class="w-4 h-4 text-brand-500"></i>
                    <p class="text-slate-500 text-xs font-bold uppercase tracking-wide">Tutores</p>
                </div>
                <h3 class="text-2xl lg:text-3xl font-bold text-slate-800"><?= $stats['total_tutores'] ?></h3>
                <p class="text-xs text-slate-400">Cadastrados</p>
            </div>

            <div class="bg-white p-5 rounded-2xl shadow-sm border border-slate-100 print:border print:border-slate-300">
                <p class="text-slate-500 text-xs font-bold uppercase tracking-wide mb-1">Taxa de Conversão</p>
                <h3 class="text-2xl lg:text-3xl font-bold text-slate-800"><?= number_format($stats['conversao'], 1) ?>%</h3>
                <p class="text-xs text-slate-400">Eficiência de Agendamento</p>
            </div>
        </div>

        <!-- 2. Charts Section (Oculto no Print padrão se necessário, mas bom ter) -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8 print:grid-cols-2 print:gap-8">
            <!-- Line Chart: Atendimentos por Dia -->
            <div class="lg:col-span-2 bg-white p-6 rounded-2xl shadow-sm border border-slate-100 print:border print:border-slate-300 print:break-inside-avoid">
                <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i data-lucide="trending-up" class="w-5 h-5 text-brand-500"></i>
                    Evolução Diária
                </h3>
                <div class="relative h-72 w-full">
                    <canvas id="chartTimeline"></canvas>
                </div>
            </div>

            <!-- Doughnut Chart: Status -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 print:border print:border-slate-300 print:break-inside-avoid">
                <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i data-lucide="pie-chart" class="w-5 h-5 text-brand-500"></i>
                    Distribuição de Status
                </h3>
                <div class="relative h-60 w-full flex justify-center">
                    <canvas id="chartStatus"></canvas>
                </div>
            </div>
        </div>

        <!-- 3. Lists Section -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8 print:grid-cols-2 print:gap-8">
            <!-- Top Clientes -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 print:border print:border-slate-300 print:break-inside-avoid">
                <div class="flex items-center justify-between mb-6">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                        <i data-lucide="trophy" class="w-5 h-5 text-amber-500"></i>
                        Clientes do Mês (Faturamento)
                    </h3>
                </div>
                
                <div class="space-y-4">
                    <?php if(empty($top_tutores)): ?>
                        <div class="text-center py-8 text-slate-400 italic">Nenhum dado financeiro no período.</div>
                    <?php else: ?>
                        <?php foreach($top_tutores as $index => $t): ?>
                            <?php 
                                $isTop5 = $index < 5; 
                                $isHidden = !$isTop5;
                            ?>
                            <div class="flex items-center gap-4 p-2 rounded-xl <?= $isTop5 ? 'bg-amber-50 border border-amber-100' : 'hidden client-item-extra' ?> transition-all duration-300">
                                <div class="w-8 h-8 rounded-full <?= $isTop5 ? 'bg-amber-500 text-white' : 'bg-slate-100 text-slate-500' ?> flex items-center justify-center font-bold text-xs shadow-sm">
                                    #<?= $index + 1 ?>
                                </div>
                                <div class="flex-1">
                                    <div class="flex justify-between mb-1">
                                        <span class="font-bold text-slate-700 text-sm"><?= $t['nome'] ?></span>
                                        <span class="font-bold <?= $isTop5 ? 'text-amber-600' : 'text-slate-600' ?> text-sm"><?= $t['total_atendimentos'] ?> Visitas</span>
                                    </div>
                                    <div class="w-full bg-slate-200 rounded-full h-1.5 overflow-hidden">
                                        <?php 
                                            // Calcula % relativo ao primeiro (maior) para a barra
                                            $percent = ($t['total_atendimentos'] / $top_tutores[0]['total_atendimentos']) * 100;
                                        ?>
                                        <div class="<?= $isTop5 ? 'bg-amber-500' : 'bg-slate-400' ?> h-1.5 rounded-full" style="width: <?= $percent ?>%"></div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <?php if(count($top_tutores) > 5): ?>
                            <button onclick="toggleClientes()" id="btn-toggle-clientes" class="w-full py-2 text-sm font-bold text-brand-600 hover:bg-brand-50 rounded-xl border border-dashed border-brand-200 transition-colors mt-2">
                                + Ver Todos (<?= count($top_tutores) - 5 ?> restantes)
                            </button>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Top Serviços -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 print:border print:border-slate-300 print:break-inside-avoid">
                 <h3 class="text-lg font-bold text-slate-800 mb-6 flex items-center gap-2">
                    <i data-lucide="scissor" class="w-5 h-5 text-blue-500"></i>
                    Serviços Mais Realizados
                </h3>
                
                <div class="space-y-3">
                    <?php if(empty($top_servicos)): ?>
                        <div class="text-center py-8 text-slate-400 italic">Nenhum serviço realizado no período.</div>
                    <?php else: ?>
                        <?php foreach($top_servicos as $index => $s): ?>
                            <div class="group flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-white border border-transparent hover:border-slate-100 hover:shadow-sm transition-all print:border-slate-100">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-xs">
                                        <?= $index + 1 ?>
                                    </div>
                                    <div>
                                        <span class="font-medium text-slate-700 block"><?= $s['nome'] ?></span>
                                        <span class="text-xs text-slate-400 font-bold uppercase tracking-tighter">Receita: R$ <?= number_format($s['faturamento_servico'], 2, ',', '.') ?></span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                     <span class="text-sm font-bold text-slate-800"><?= $s['total_realizados'] ?></span>
                                     <span class="text-xs text-slate-400 uppercase">Execuções</span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Footer Print -->
         <div class="hidden print:block text-center mt-12 border-t border-slate-200 pt-6">
            <p class="text-sm text-slate-400">Relatório gerado em <?= date('d/m/Y H:i') ?> pelo sistema HVT Petshop.</p>
        </div>
</div>

<!-- Template do Relatório PDF (Layout A4 Formal) -->
<div id="report-overlay" class="hidden fixed inset-0 z-50 bg-slate-900/80 overflow-y-auto flex justify-center py-4 md:py-8">
    <div class="relative w-full max-w-4xl px-4 md:px-0">
        <!-- Close Button for Preview -->
        <button onclick="fecharRelatorio()" class="fixed top-4 right-4 md:absolute md:-right-12 md:top-0 text-white hover:text-slate-300 p-2 bg-slate-800 md:bg-transparent rounded-full z-[60]">
            <i data-lucide="x" class="w-6 h-6 md:w-8 md:h-8"></i>
        </button>

        <div id="report-template" class="bg-white text-slate-800 mx-auto transform origin-top scale-[0.6] sm:scale-75 md:scale-100" style="width: 210mm; min-height: 297mm; padding: 25mm 20mm; position: relative; font-family: 'Outfit', sans-serif;">
            <!-- Header Border -->
            <div class="absolute top-0 left-0 w-full h-1.5 bg-brand-600 no-print"></div>

            <!-- Page Header -->
            <div class="flex justify-between items-start mb-16">
                <div>
                    <h1 class="text-5xl font-black text-slate-900 tracking-tighter leading-none mb-3">HVT <span class="text-brand-600">PET</span>SHOP</h1>
                    <div class="flex items-center gap-3">
                        <div class="h-1 w-8 bg-brand-500 rounded-full"></div>
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.3em]">Relatório Executivo Mensal</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="inline-block border-2 border-slate-100 rounded-3xl px-8 py-5">
                        <p class="text-[10px] font-black text-brand-500 uppercase tracking-widest mb-2">Período de Referência</p>
                        <p class="text-base font-black text-slate-900">
                            <?= date('d/m/Y', strtotime($dataInicial)) ?> — <?= date('d/m/Y', strtotime($dataFinal)) ?>
                        </p>
                    </div>
                </div>
            </div>

            <!-- Dashboard Style Metrics -->
            <div class="grid grid-cols-3 gap-8 mb-16">
                <div class="p-8 bg-slate-50 border border-slate-100 rounded-[2rem] flex flex-col justify-between">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Total Atendimentos</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-5xl font-black text-slate-900"><?= $stats['finalizados'] ?></span>
                        <span class="text-xs font-bold text-slate-400">Finalizados</span>
                    </div>
                </div>
                <div class="p-8 bg-slate-50 border border-slate-100 rounded-[2rem] flex flex-col justify-between">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-4">Ticket Médio</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-xs font-bold text-slate-400">R$</span>
                        <span class="text-5xl font-black text-slate-900"><?= number_format($stats['finalizados'] > 0 ? $stats['faturamento'] / $stats['finalizados'] : 0, 0, ',', '.') ?></span>
                    </div>
                </div>
                <div class="p-8 bg-brand-600 text-white rounded-[2.5rem] shadow-2xl shadow-brand-500/20 flex flex-col justify-between">
                    <p class="text-[10px] font-black text-brand-100 uppercase tracking-widest mb-4">Eficiência Comercial</p>
                    <div class="flex items-baseline gap-2">
                        <span class="text-5xl font-black"><?= number_format($stats['conversao'], 1) ?></span>
                        <span class="text-xs font-bold text-brand-100">%</span>
                    </div>
                </div>
            </div>

            <!-- Data Breakdown (Single Column) -->
            <div class="space-y-16">
                <!-- Section Header -->
                <div class="flex items-center gap-6">
                    <span class="px-4 py-1.5 bg-slate-900 text-white rounded-full text-[10px] font-black uppercase tracking-widest">Analytics</span>
                    <div class="flex-1 h-0.5 bg-slate-100"></div>
                </div>

                <!-- Daily Table -->
                <div class="w-full">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <i data-lucide="trending-up" class="w-4 h-4 text-brand-500"></i>
                        Evolução Diária
                    </h4>
                    <div class="overflow-hidden border border-slate-100 rounded-3xl">
                        <table class="w-full text-left text-[11px]">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-6 py-4 font-black uppercase">Data</th>
                                    <th class="px-6 py-4 text-right font-black uppercase">Volume de Atendimentos</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach($charts['timeline']['labels'] as $idx => $label): ?>
                                    <tr>
                                        <td class="px-6 py-3.5 text-slate-600 font-bold"><?= $label ?></td>
                                        <td class="px-6 py-3.5 text-right font-black text-slate-900"><?= $charts['timeline']['data'][$idx] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Status Table -->
                <div class="w-full">
                    <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-6 flex items-center gap-2">
                        <i data-lucide="pie-chart" class="w-4 h-4 text-brand-500"></i>
                        Mix de Atendimentos (Situacional)
                    </h4>
                    <div class="overflow-hidden border border-slate-100 rounded-3xl">
                        <table class="w-full text-left text-[11px]">
                            <thead class="bg-slate-50 text-slate-500">
                                <tr>
                                    <th class="px-6 py-4 font-black uppercase">Situação Operacional</th>
                                    <th class="px-6 py-4 text-right font-black uppercase">Total</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <?php foreach($charts['status']['labels'] as $idx => $label): ?>
                                    <tr>
                                        <td class="px-6 py-3.5 text-slate-600 font-bold"><?= $label ?></td>
                                        <td class="px-6 py-3.5 text-right font-black text-slate-900"><?= $charts['status']['data'][$idx] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Rankings Section -->
                <div class="space-y-16">
                    <!-- Servicos -->
                    <div class="w-full">
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-6">Top Serviços Mais Requisitados</h4>
                        <div class="space-y-4">
                            <?php foreach(array_slice($top_servicos, 0, 5) as $idx => $s): ?>
                                <div class="flex items-center justify-between p-5 bg-white border border-slate-100 rounded-3xl">
                                    <div class="flex items-center gap-4">
                                        <span class="w-8 h-8 rounded-2xl bg-slate-50 text-slate-400 font-black text-[10px] flex items-center justify-center">0<?= $idx+1 ?></span>
                                        <span class="font-black text-slate-800 text-xs uppercase tracking-tight"><?= $s['nome'] ?></span>
                                    </div>
                                    <span class="px-4 py-1.5 bg-brand-50 text-brand-600 rounded-full font-black text-[10px]"><?= $s['total_realizados'] ?> EXECUÇÕES</span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Clientes Full List -->
                    <div class="w-full">
                        <h4 class="text-xs font-black text-slate-900 uppercase tracking-widest mb-6">Lista Completa de Clientes do Período</h4>
                        <div class="overflow-hidden border border-slate-100 rounded-3xl">
                            <table class="w-full text-[11px]">
                                <thead class="bg-slate-900 text-white">
                                    <tr>
                                        <th class="px-6 py-4 text-left font-black uppercase tracking-tight">Posição</th>
                                        <th class="px-6 py-4 text-left font-black uppercase tracking-tight">Tutor / Cliente</th>
                                        <th class="px-6 py-4 text-right font-black uppercase tracking-tight">Frequência</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <?php foreach($top_tutores as $index => $t): ?>
                                        <tr class="<?= $index < 5 ? 'bg-amber-50/30' : '' ?>">
                                            <td class="px-6 py-4 w-20">
                                                <span class="font-black <?= $index < 5 ? 'text-amber-600' : 'text-slate-400' ?>">#<?= $index + 1 ?></span>
                                            </td>
                                            <td class="px-6 py-4 text-slate-900 font-bold uppercase tracking-tighter text-xs"><?= $t['nome'] ?></td>
                                            <td class="px-6 py-4 text-right font-black text-slate-600"><?= $t['total_atendimentos'] ?> VISITAS</td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Professional Footer -->
            <div class="report-footer border-t-2 border-slate-900 pt-10 flex justify-between items-center">
                <div>
                     <p class="text-[9px] font-black text-slate-900 uppercase tracking-widest mb-1">HVT MASTER CLOUD SYSTEM</p>
                     <p class="text-[8px] font-bold text-slate-400 uppercase tracking-widest">© <?= date('Y') ?> Gestão Corporativa Petshop</p>
                </div>
                <div class="flex gap-8 text-[9px] font-black text-slate-400 uppercase tracking-widest">
                    <div class="flex flex-col items-end">
                        <span class="text-slate-900">EMISSÃO</span>
                        <span><?= date('d/m/Y H:i') ?></span>
                    </div>
                    <div class="flex flex-col items-end">
                        <span class="text-slate-900">ORIGEM</span>
                        <span>IP: <?= $_SERVER['REMOTE_ADDR'] ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    // Configurações Comuns Chart.js
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#64748b';

    // 1. Chart Timeline
    const ctxTimeline = document.getElementById('chartTimeline');
    
    // Gradient fill
    const gradient = ctxTimeline.getContext('2d').createLinearGradient(0, 0, 0, 280);
    gradient.addColorStop(0, 'rgba(139, 92, 246, 0.25)');
    gradient.addColorStop(1, 'rgba(139, 92, 246, 0.01)');
    
    const chartTimeline = new Chart(ctxTimeline, {
        type: 'line',
        data: {
            labels: <?= json_encode($charts['timeline']['labels']) ?>,
            datasets: [{
                label: 'Atendimentos',
                data: <?= json_encode($charts['timeline']['data']) ?>,
                borderColor: '#8b5cf6',
                backgroundColor: gradient,
                borderWidth: 2.5,
                pointBackgroundColor: '#8b5cf6',
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 4,
                pointHoverRadius: 6,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { borderDash: [2, 4] } },
                x: { grid: { display: false } }
            }
        }
    });

    // 2. Chart Status
    const ctxStatus = document.getElementById('chartStatus');
    const chartStatus = new Chart(ctxStatus, {
        type: 'doughnut',
        data: {
            labels: <?= json_encode($charts['status']['labels']) ?>,
            datasets: [{
                data: <?= json_encode($charts['status']['data']) ?>,
                backgroundColor: ['#f59e0b', '#10b981', '#ef4444', '#3b82f6'],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'right', labels: { boxWidth: 12, usePointStyle: true } }
            },
            cutout: '70%'
        }
    });

    // Funções de Preview e PDF
    const overlay = document.getElementById('report-overlay');

    function visualizarRelatorio() {
        overlay.classList.remove('hidden');
        document.body.style.overflow = 'hidden'; // Evita scroll no fundo
    }

    function fecharRelatorio() {
        overlay.classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    function toggleClientes() {
        const extraItems = document.querySelectorAll('.client-item-extra');
        const btn = document.getElementById('btn-toggle-clientes');
        const isHidden = extraItems[0].classList.contains('hidden');

        extraItems.forEach(item => {
            if (isHidden) {
                item.classList.remove('hidden');
            } else {
                item.classList.add('hidden');
            }
        });

        if (isHidden) {
            btn.innerHTML = '↑ Mostrar Menos';
            btn.classList.replace('text-brand-600', 'text-slate-500');
            btn.classList.replace('hover:bg-brand-50', 'hover:bg-slate-50');
        } else {
            btn.innerHTML = '+ Ver Todos (<?= count($top_tutores) - 5 ?> restantes)';
            btn.classList.replace('text-slate-500', 'text-brand-600');
            btn.classList.replace('hover:bg-slate-50', 'hover:bg-brand-50');
        }
    }

    function imprimirRelatorio() {
        const overlay = document.getElementById('report-overlay');
        overlay.classList.remove('hidden');
        
        // Aguarda renderização e imprime
        setTimeout(() => window.print(), 300);
    }

    function gerarPDF() {
        const overlay = document.getElementById('report-overlay');
        const wasHidden = overlay.classList.contains('hidden');
        
        // Garante visibilidade para renderização
        if (wasHidden) {
            overlay.classList.remove('hidden');
            // Opcional: Adicionar classe para esconder o botão de fechar ou fundo se necessário, 
            // mas como vamos focar no #report-template, o fundo não importa tanto, 
            // desde que o elemento tenha dimensões.
        }

        const element = document.getElementById('report-template');
        
        const opt = {
            margin:       0,
            filename:     'relatorio-gestao-<?= date("Y-m-d") ?>.pdf',
            image:        { type: 'jpeg', quality: 1 },
            html2canvas:  { scale: 3, useCORS: true, letterRendering: true },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        // Feedback visual
        const btn = document.querySelector('button[onclick="gerarPDF()"]');
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i data-lucide="loader-2" class="w-4 h-4 animate-spin"></i> Gerando...';
        btn.disabled = true;

        html2pdf().set(opt).from(element).save().then(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
            // Se estava oculto antes, esconde de novo
            if (wasHidden) {
                overlay.classList.add('hidden');
            }
        }).catch(err => {
            console.error('PDF Error:', err);
            btn.innerHTML = originalText;
            btn.disabled = false;
            if (wasHidden) overlay.classList.add('hidden');
            alert('Erro ao gerar PDF. Verifique o console.');
        });
    }

    // Fecha ao clicar fora
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) fecharRelatorio();
    });

    // Inicializa ícones
    lucide.createIcons();
</script>
<?= $this->endSection() ?>
