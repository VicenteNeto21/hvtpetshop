<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>Visualizar Ficha<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="w-full animate-enter">
    <!-- Header de Ações (Apenas Web) -->
    <div class="mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 no-print">
        <div>
            <a href="<?= base_url('agenda') ?>" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-brand-600 mb-4 transition-colors">
                <i data-lucide="arrow-left" class="w-4 h-4"></i>
                Voltar para Agenda
            </a>
            <h1 class="text-3xl font-bold text-slate-900">Ficha de Atendimento</h1>
            <p class="text-slate-500 mt-1">Dados técnicos registrados no sistema.</p>
        </div>
        <button onclick="window.print()" class="px-6 py-3 bg-brand-600 text-white rounded-xl font-bold hover:bg-brand-700 transition-all flex items-center gap-2 shadow-lg shadow-brand-600/20 relative z-10">
            <i data-lucide="printer" class="w-5 h-5"></i>
            Imprimir Ficha
        </button>
    </div>

    <!-- Página do Documento -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 p-8 md:p-12 mb-12 w-full ficha-documento">
        
        <!-- Cabeçalho Estilo Relatório -->
        <div class="flex justify-between items-start mb-10 pb-6 border-b-2 border-brand-500">
            <div>
                <h2 class="text-2xl font-black text-slate-900 leading-none">Cerenia<span>Pet</span></h2>
                <p class="text-[10px] text-slate-400 uppercase tracking-widest mt-1">Sistema de Gestão Petshop</p>
                <div class="mt-4">
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold uppercase tracking-widest border border-green-200">
                        Atendimento Finalizado
                    </span>
                </div>
            </div>
            <div class="text-right">
                <div class="bg-slate-50 p-4 rounded-xl border border-slate-100">
                    <label class="block text-[9px] text-slate-400 uppercase tracking-widest leading-none mb-1">Data do Registro</label>
                    <span class="text-sm font-bold text-slate-800"><?= date('d/m/Y \à\s H:i', strtotime($agendamento['data_hora'])) ?></span>
                </div>
            </div>
        </div>

        <!-- Seção: Identificação -->
        <div class="grid grid-cols-2 gap-8 mb-10">
            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                <h4 class="text-[10px] font-bold text-brand-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <i data-lucide="dog" class="w-3 h-3"></i> Dados do Paciente
                </h4>
                <div class="space-y-2">
                    <p class="text-lg font-black text-slate-800"><?= $agendamento['pet_nome'] ?></p>
                    <p class="text-sm text-slate-500 font-medium"><?= $agendamento['raca'] ?> • <?= $agendamento['especie'] ?></p>
                    <p class="text-xs text-slate-400"><?= $agendamento['sexo'] == 'M' ? 'Macho' : 'Fêmea' ?></p>
                </div>
            </div>
            <div class="bg-slate-50 p-6 rounded-2xl border border-slate-100">
                <h4 class="text-[10px] font-bold text-brand-600 uppercase tracking-widest mb-3 flex items-center gap-2">
                    <i data-lucide="user" class="w-3 h-3"></i> Tutor Responsável
                </h4>
                <div class="space-y-2">
                    <p class="text-lg font-bold text-slate-800"><?= $agendamento['tutor_nome'] ?></p>
                    <p class="text-sm text-slate-500 flex items-center gap-2">
                        <i data-lucide="phone" class="w-3 h-3"></i>
                        <?= $agendamento['tutor_telefone'] ?: 'Não informado' ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Seção: Avaliação Visual -->
        <div class="mb-10">
            <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-4 pb-2 border-b border-slate-100">Avaliação Visual</h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
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
                    <div class="flex items-center gap-2 p-3 <?= $isMarcado ? 'bg-brand-50 border-brand-200' : 'bg-slate-50 border-slate-100' ?> rounded-xl border transition-all">
                        <div class="w-4 h-4 flex items-center justify-center rounded <?= $isMarcado ? 'bg-brand-500 text-white' : 'bg-slate-200 text-slate-400' ?>">
                            <i data-lucide="<?= $isMarcado ? 'check' : 'minus' ?>" class="w-2.5 h-2.5"></i>
                        </div>
                        <span class="font-bold text-[11px] <?= $isMarcado ? 'text-brand-700' : 'text-slate-400' ?>"><?= $obs['descricao'] ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php if($outrosDetalhes): ?>
                <div class="mt-4 p-4 bg-slate-50 rounded-xl border border-slate-100 italic text-sm text-slate-600">
                    <strong>Outros Detalhes:</strong> <?= $outrosDetalhes ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Seção: Serviços e Saúde -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-4 pb-2 border-b border-slate-100">Serviços Executados</h3>
                <div class="flex flex-wrap gap-2">
                    <?php 
                    $realizadosIds = array_column($servicos_realizados, 'servico_id');
                    foreach($servicos as $servico): 
                        if(in_array($servico['id'], $realizadosIds)):
                    ?>
                        <div class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-brand-500 text-white rounded-lg text-xs font-bold">
                            <i data-lucide="check" class="w-3 h-3"></i>
                            <?= $servico['nome'] ?>
                        </div>
                    <?php 
                        endif;
                    endforeach; ?>
                </div>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-4 pb-2 border-b border-slate-100">Informações de Saúde</h3>
                <div class="space-y-4">
                    <div class="flex justify-between items-center text-xs border-b border-slate-50 pb-2">
                        <span class="text-slate-400 font-bold uppercase tracking-tighter">Doença Pré-existente:</span>
                        <span class="text-slate-800 font-bold"><?= !empty($ficha['doenca_pre_existente']) ? $ficha['doenca_pre_existente'] : 'Nenhuma' ?></span>
                    </div>
                    <div class="flex justify-between items-center text-xs border-b border-slate-50 pb-2">
                        <span class="text-slate-400 font-bold uppercase tracking-tighter">Pele:</span>
                        <span class="text-slate-800 font-bold"><?= !empty($ficha['doenca_pele']) ? $ficha['doenca_pele'] : 'Nenhum problema' ?></span>
                    </div>
                    <div class="flex justify-between items-center text-xs border-b border-slate-50 pb-2">
                        <span class="text-slate-400 font-bold uppercase tracking-tighter">Ouvidos:</span>
                        <span class="text-slate-800 font-bold"><?= !empty($ficha['doenca_ouvido']) ? $ficha['doenca_ouvido'] : 'Nenhum problema' ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seção: Comentários Técnicos -->
        <div class="space-y-6">
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-3">Comportamento do Animal</h3>
                <p class="text-sm text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-100"><?= !empty($ficha['comportamento_pet']) ? nl2br($ficha['comportamento_pet']) : 'Sem observações registradas.' ?></p>
            </div>
            <div>
                <h3 class="text-xs font-bold text-slate-900 uppercase tracking-widest mb-3">Observações Gerais</h3>
                <p class="text-sm text-slate-700 bg-slate-50 p-4 rounded-xl border border-slate-100"><?= !empty($ficha['observacoes']) ? nl2br($ficha['observacoes']) : 'Nenhuma observação extra registrada.' ?></p>
            </div>
        </div>

        <!-- Rodapé do Documento (Apenas para Impressão) -->
        <div class="hidden print-footer mt-12 pt-6 border-t border-slate-200 flex justify-between items-center text-[9px] text-slate-400">
            <div>Ficha gerada via <strong>CereniaPet v3.2.0</strong> em <?= date('d/m/Y') ?></div>
            <div>Assinatura do Responsável: ____________________________________</div>
        </div>

    </div>
</div>

<style>
    .ficha-documento h2 span { color: #2563eb; }
    
    @media print {
        @page { size: A4; margin: 0; }
        
        /* Reset Completo de HTML/Body para evitar loops de layout */
        html, body { 
            background: white !important; 
            font-family: 'Outfit', sans-serif; 
            opacity: 1 !important; 
            visibility: visible !important;
            height: auto !important;
            min-height: 0 !important;
            overflow: visible !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .no-print { display: none !important; }
        
        /* Forçar visibilidade e anular animações */
        .animate-enter, [class*="animate-"] { 
            opacity: 1 !important; 
            transform: none !important; 
            animation: none !important;
            visibility: visible !important;
        }
        .hidden { display: block !important; } /* Mostrar Rodapé no Print */
        .print-footer { display: flex !important; }
        
        /* Layout Global Print */
        .md\:ml-64, nav, aside { display: none !important; }
        main { margin: 0 !important; padding: 0 !important; width: 100% !important; }
        
        .ficha-documento {
            margin: 0 !important;
            padding: 15mm !important;
            width: 100% !important;
            max-width: none !important;
            border: none !important;
            box-shadow: none !important;
        }
        
        .bg-slate-50 { background-color: #f8fafc !important; -webkit-print-color-adjust: exact !important; }
        .bg-brand-500 { background-color: #2563eb !important; color: white !important; -webkit-print-color-adjust: exact !important; }
        .border-brand-500 { border-color: #2563eb !important; -webkit-print-color-adjust: exact !important; }
        .text-brand-600 { color: #2563eb !important; }

        /* Garantir que o conteúdo não seja cortado */
        main, .ficha-documento {
            overflow: visible !important;
            position: static !important;
        }
    }
</style>
<?= $this->endSection() ?>
