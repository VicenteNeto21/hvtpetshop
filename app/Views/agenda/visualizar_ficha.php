<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Visualizar Ficha<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="flex min-h-screen bg-slate-50">
    <?= view('components/sidebar') ?>

    <main class="flex-1 md:ml-64 p-4 md:p-8">
        <div class="w-full animate-enter">
            <!-- Header -->
            <div class="mb-8 flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <a href="<?= base_url('agenda') ?>" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-brand-600 mb-4 transition-colors">
                        <i data-lucide="arrow-left" class="w-4 h-4"></i>
                        Voltar para Agenda
                    </a>
                    <h1 class="text-3xl font-bold text-slate-900 flex items-center gap-3">
                        Ficha de Atendimento
                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-xs font-bold uppercase tracking-widest border border-green-200">
                            Finalizado
                        </span>
                    </h1>
                    <p class="text-slate-500 mt-1">Dados técnicos registrados em <?= date('d/m/Y H:i', strtotime($agendamento['data_hora'])) ?></p>
                </div>
                <button onclick="window.print()" class="px-6 py-3 bg-white border border-slate-200 rounded-xl text-slate-600 font-bold hover:bg-slate-50 transition-all flex items-center gap-2 shadow-sm">
                    <i data-lucide="printer" class="w-5 h-5"></i>
                    Imprimir Ficha
                </button>
            </div>

            <div class="space-y-8">
                <!-- Info Card -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 flex flex-col lg:flex-row gap-8 items-center relative overflow-hidden">
                    <div class="absolute right-0 top-0 w-32 h-32 bg-slate-50 rounded-bl-full -mr-8 -mt-8 opacity-50"></div>
                    
                    <div class="flex-1 flex flex-col md:flex-row items-center md:items-start gap-6 relative z-10 w-full">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-600 shrink-0 border border-slate-200">
                            <i data-lucide="dog" class="w-8 h-8"></i>
                        </div>
                        <div class="text-center md:text-left flex-1">
                            <div class="flex flex-col md:flex-row md:items-center gap-2 mb-2">
                                <h2 class="text-2xl font-black text-slate-900"><?= $agendamento['pet_nome'] ?></h2>
                                <span class="px-3 py-1 bg-violet-100 text-violet-700 rounded-full text-xs font-bold uppercase tracking-wider">
                                    <?= $agendamento['especie'] ?>
                                </span>
                            </div>
                            <p class="text-slate-500 font-medium">
                                <?= $agendamento['raca'] ?> • <?= $agendamento['sexo'] == 'M' ? '♂ Macho' : '♀ Fêmea' ?>
                            </p>
                        </div>
                    </div>
                    
                    <div class="w-px h-24 bg-slate-100 hidden lg:block"></div>

                    <div class="flex-1 flex flex-col md:flex-row items-center md:items-start gap-6 relative z-10 w-full">
                        <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center text-slate-600 shrink-0 border border-slate-200">
                            <i data-lucide="user" class="w-8 h-8"></i>
                        </div>
                        <div class="text-center md:text-left">
                            <h2 class="text-xl font-bold text-slate-800"><?= $agendamento['tutor_nome'] ?></h2>
                            <p class="text-slate-500 flex items-center justify-center md:justify-start gap-2 mt-1">
                                <i data-lucide="phone" class="w-4 h-4 text-slate-400"></i>
                                <?= $agendamento['tutor_telefone'] ?>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- 1. Avaliação Visual -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center border border-slate-200">
                                <i data-lucide="eye" class="w-5 h-5"></i>
                            </div>
                            Avaliação Visual
                        </h3>
                    </div>
                    <div class="p-8">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <?php 
                            $marcadasIds = array_column($obs_marcadas, 'observacao_id');
                            $outrosDetalhes = '';
                            foreach($obs_marcadas as $m) {
                                if($m['observacao_id'] == 7) $outrosDetalhes = $m['outros_detalhes'];
                            }
                            ?>

                            <?php foreach($obs_visuais as $obs): 
                                $isMarcado = in_array($obs['id'], $marcadasIds);
                            ?>
                                <div class="flex items-center gap-3 p-4 <?= $isMarcado ? 'bg-brand-50 border-brand-200 border-2' : 'bg-slate-50 border-slate-100 border' ?> rounded-2xl transition-all">
                                    <div class="w-5 h-5 flex items-center justify-center rounded <?= $isMarcado ? 'bg-brand-500 text-white' : 'bg-slate-200 text-slate-400' ?>">
                                        <i data-lucide="<?= $isMarcado ? 'check' : 'minus' ?>" class="w-3.5 h-3.5"></i>
                                    </div>
                                    <span class="font-bold text-sm <?= $isMarcado ? 'text-brand-700' : 'text-slate-400' ?>"><?= $obs['descricao'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if($outrosDetalhes): ?>
                            <div class="mt-8 p-6 bg-slate-50 rounded-2xl border border-slate-100">
                                <label class="block text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Outros Detalhes</label>
                                <p class="text-slate-700 font-medium"><?= $outrosDetalhes ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- 2. Serviços Realizados -->
                <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-8 border-b border-slate-100 bg-slate-50/50 flex justify-between items-center">
                        <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center border border-slate-200">
                                <i data-lucide="scissors" class="w-5 h-5"></i>
                            </div>
                            Serviços Executados
                        </h3>
                    </div>
                    <div class="p-8">
                        <div class="flex flex-wrap gap-3">
                            <?php 
                            $realizadosIds = array_column($servicos_realizados, 'servico_id');
                            foreach($servicos as $servico): 
                                if(in_array($servico['id'], $realizadosIds)):
                            ?>
                                <div class="inline-flex items-center gap-2 px-4 py-2.5 bg-brand-500 text-white rounded-xl text-sm font-bold shadow-md shadow-brand-500/10">
                                    <i data-lucide="check" class="w-4 h-4"></i>
                                    <?= $servico['nome'] ?>
                                </div>
                            <?php 
                                endif;
                            endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- 3. Saúde e Detalhes -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center border border-slate-200">
                                    <i data-lucide="activity" class="w-5 h-5"></i>
                                </div>
                                Saúde
                            </h3>
                        </div>
                        <div class="p-8 space-y-6">
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-1">Doença Pré-Existente</label>
                                <p class="text-slate-800 font-bold"><?= !empty($ficha['doenca_pre_existente']) ? $ficha['doenca_pre_existente'] : 'Nenhuma registrada' ?></p>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-1">Problemas de Pele</label>
                                <p class="text-slate-800 font-bold"><?= !empty($ficha['doenca_pele']) ? $ficha['doenca_pele'] : 'Nenhum registrado' ?></p>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-1">Problemas de Ouvido</label>
                                <p class="text-slate-800 font-bold"><?= !empty($ficha['doenca_ouvido']) ? $ficha['doenca_ouvido'] : 'Nenhum registrado' ?></p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
                        <div class="p-8 border-b border-slate-100 bg-slate-50/50">
                            <h3 class="text-xl font-black text-slate-800 flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center border border-slate-200">
                                    <i data-lucide="file-text" class="w-5 h-5"></i>
                                </div>
                                Detalhes Técnicos
                            </h3>
                        </div>
                        <div class="p-8 space-y-6">
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-1">Altura dos Pelos</label>
                                <p class="text-slate-800 font-bold"><?= !empty($ficha['altura_pelos']) ? $ficha['altura_pelos'] : 'Não informado' ?></p>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-1">Comportamento</label>
                                <p class="text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-100"><?= !empty($ficha['comportamento_pet']) ? nl2br($ficha['comportamento_pet']) : 'Sem observações' ?></p>
                            </div>
                            <div>
                                <label class="block text-xs font-black uppercase text-slate-400 tracking-widest mb-1">Observações Gerais</label>
                                <p class="text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-100"><?= !empty($ficha['observacoes']) ? nl2br($ficha['observacoes']) : 'Nenhuma observação extra' ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
@media print {
    /* Configurações de Página A4 */
    @page {
        size: A4;
        margin: 1.5cm;
    }

    /* Reset de Layout para Impressão */
    body { background: white !important; font-size: 12pt; color: black !important; }
    .md\:ml-64, nav, .sidebar, button, a[href*="agenda"], .animate-enter { 
        margin-left: 0 !important; 
        display: none !important; 
    }
    
    main { padding: 0 !important; margin: 0 !important; width: 100% !important; }
    .bg-slate-50 { background: transparent !important; }
    
    /* Forçar visibilidade de elementos */
    .bg-white { background: white !important; }
    .border { border: 1px solid #ddd !important; }
    .rounded-3xl { border-radius: 12px !important; }
    .shadow-sm, .shadow-md { box-shadow: none !important; }

    /* Ajustes de Tipografia */
    h1 { font-size: 22pt !important; margin-bottom: 10pt !important; color: black !important; }
    h2 { font-size: 16pt !important; color: black !important; }
    h3 { font-size: 14pt !important; color: black !important; }
    label { color: #666 !important; font-size: 9pt !important; }
    p, span { color: black !important; }

    /* Grid e Layout Interno */
    .grid { display: block !important; }
    .grid > div { margin-bottom: 15pt !important; page-break-inside: avoid; }
    .flex { display: flex !important; }
    .lg\:flex-row { flex-direction: row !important; }
    
    /* Badge de Status e Serviços */
    .bg-green-100, .bg-violet-100, .bg-brand-500 { 
        background: #f0f0f0 !important; 
        color: black !important; 
        border: 1px solid #ccc !important;
        print-color-adjust: exact;
        -webkit-print-color-adjust: exact;
    }

    /* Ocultar elementos desnecessários */
    .absolute.right-0.top-0 { display: none !important; }
    .w-px.h-24 { display: none !important; }

    /* Forçar cores de ícones simples (preto) */
    [data-lucide] { stroke: black !important; }
}
</style>
<?= $this->endSection() ?>
