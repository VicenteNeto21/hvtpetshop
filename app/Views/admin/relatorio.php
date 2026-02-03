<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Gerencial - HVT Petshop</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; line-height: 1.5; }
        
        .page { width: 210mm; min-height: 297mm; margin: 20px auto; background: white; padding: 20mm; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid #0ea5e9; }
        .logo h1 { font-size: 24px; font-weight: 800; color: #0f172a; }
        .logo h1 span { color: #0ea5e9; }
        .logo p { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 2px; margin-top: 2px; }
        .periodo { text-align: right; background: #f1f5f9; padding: 10px 16px; border-radius: 8px; }
        .periodo label { font-size: 9px; color: #64748b; text-transform: uppercase; letter-spacing: 1px; display: block; margin-bottom: 2px; }
        .periodo span { font-size: 12px; font-weight: 700; color: #0f172a; }
        
        /* KPIs Grid */
        .kpis { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; margin-bottom: 25px; }
        .kpi { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; }
        .kpi.destaque { background: #0ea5e9; border-color: #0ea5e9; color: white; }
        .kpi.verde { background: #10b981; border-color: #10b981; color: white; }
        .kpi.amarelo { background: #f59e0b; border-color: #f59e0b; color: white; }
        .kpi label { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; display: block; margin-bottom: 6px; }
        .kpi.destaque label, .kpi.verde label, .kpi.amarelo label { color: rgba(255,255,255,0.8); }
        .kpi .valor { font-size: 26px; font-weight: 800; }
        .kpi .unidade { font-size: 10px; color: #94a3b8; margin-left: 2px; }
        .kpi.destaque .unidade, .kpi.verde .unidade, .kpi.amarelo .unidade { color: rgba(255,255,255,0.7); }
        
        /* Variação */
        .variacao { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 10px; font-weight: 700; margin-left: 6px; }
        .variacao.positiva { background: #d1fae5; color: #059669; }
        .variacao.negativa { background: #fee2e2; color: #dc2626; }
        .variacao.neutra { background: #e2e8f0; color: #64748b; }
        
        /* Sections */
        .section { margin-bottom: 20px; }
        .section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; color: #0f172a; margin-bottom: 12px; padding-bottom: 6px; border-bottom: 2px solid #e2e8f0; }
        
        /* Status Grid */
        .status-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 20px; }
        .status-item { padding: 12px; border-radius: 8px; text-align: center; }
        .status-item.finalizado { background: #d1fae5; border: 1px solid #a7f3d0; }
        .status-item.cancelado { background: #fee2e2; border: 1px solid #fecaca; }
        .status-item.pendente { background: #fef3c7; border: 1px solid #fde68a; }
        .status-item .numero { font-size: 28px; font-weight: 800; }
        .status-item.finalizado .numero { color: #059669; }
        .status-item.cancelado .numero { color: #dc2626; }
        .status-item.pendente .numero { color: #d97706; }
        .status-item .rotulo { font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; color: #64748b; margin-top: 2px; }
        
        /* Mini Cards */
        .mini-cards { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 20px; }
        .mini-card { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; }
        .mini-card .titulo { font-size: 9px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 6px; }
        .mini-card .conteudo { display: flex; gap: 16px; }
        .mini-card .item { }
        .mini-card .item .numero { font-size: 22px; font-weight: 800; color: #0f172a; }
        .mini-card .item .rotulo { font-size: 9px; color: #94a3b8; }
        
        /* Tables */
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th { background: #f1f5f9; padding: 8px 10px; text-align: left; font-weight: 600; color: #475569; text-transform: uppercase; font-size: 9px; letter-spacing: 0.5px; }
        th:last-child { text-align: right; }
        td { padding: 8px 10px; border-bottom: 1px solid #f1f5f9; }
        td:last-child { text-align: right; font-weight: 600; }
        tr:nth-child(-n+5) td { background: #fffbeb; }
        .rank { display: inline-block; width: 22px; height: 22px; line-height: 22px; text-align: center; border-radius: 5px; font-weight: 700; font-size: 10px; background: #e2e8f0; color: #64748b; }
        tr:nth-child(-n+3) .rank { background: #fbbf24; color: #78350f; }
        .tag-recorrente { display: inline-block; padding: 2px 6px; border-radius: 4px; font-size: 8px; font-weight: 700; background: #dbeafe; color: #1d4ed8; margin-left: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        .footer { margin-top: 30px; padding-top: 15px; border-top: 2px solid #e2e8f0; display: flex; justify-content: space-between; font-size: 9px; color: #94a3b8; }
        .footer strong { color: #64748b; }
        
        .no-print { text-align: center; margin: 20px auto; }
        .btn-print { background: #0ea5e9; color: white; border: none; padding: 12px 32px; font-size: 14px; font-weight: 600; border-radius: 8px; cursor: pointer; }
        .btn-print:hover { background: #0284c7; }
        .btn-voltar { background: #64748b; color: white; border: none; padding: 12px 32px; font-size: 14px; font-weight: 600; border-radius: 8px; cursor: pointer; text-decoration: none; margin-left: 10px; }
        
        @media print {
            body { background: white; }
            .page { margin: 0; box-shadow: none; width: 100%; padding: 12mm; }
            .no-print { display: none !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
        @page { size: A4; margin: 8mm; }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimir Relatório</button>
    <a href="<?= base_url('admin') ?>" class="btn-voltar">← Voltar</a>
</div>

<div class="page">
    <!-- Header -->
    <div class="header">
        <div class="logo">
            <h1>HVT <span>PETSHOP</span></h1>
            <p>Relatório Gerencial</p>
        </div>
        <div class="periodo">
            <label>Período</label>
            <span><?= date('d/m/Y', strtotime($dataInicial)) ?> a <?= date('d/m/Y', strtotime($dataFinal)) ?></span>
        </div>
    </div>
    
    <!-- KPIs Principais -->
    <div class="kpis" style="grid-template-columns: repeat(3, 1fr);">
        <div class="kpi">
            <label>Total Atendimentos</label>
            <span class="valor"><?= $stats['total'] ?></span>
            <?php if($comparativo['variacao_atendimentos'] != 0): ?>
                <span class="variacao <?= $comparativo['variacao_atendimentos'] > 0 ? 'positiva' : 'negativa' ?>">
                    <?= $comparativo['variacao_atendimentos'] > 0 ? '↑' : '↓' ?> <?= number_format(abs($comparativo['variacao_atendimentos']), 1) ?>%
                </span>
            <?php endif; ?>
        </div>
        <div class="kpi destaque">
            <label>Taxa Conversão</label>
            <span class="valor"><?= number_format($stats['conversao'], 1, ',', '.') ?></span>
            <span class="unidade">%</span>
        </div>
        <div class="kpi verde">
            <label>Taxa de Retorno</label>
            <span class="valor"><?= number_format($retorno['taxa'], 1, ',', '.') ?></span>
            <span class="unidade">%</span>
        </div>
    </div>
    
    <!-- Distribuição por Status -->
    <div class="section">
        <h2 class="section-title">Distribuição por Status</h2>
        <div class="status-grid">
            <div class="status-item finalizado">
                <div class="numero"><?= $stats['finalizados'] ?></div>
                <div class="rotulo">Finalizados</div>
            </div>
            <div class="status-item cancelado">
                <div class="numero"><?= $stats['cancelados'] ?></div>
                <div class="rotulo">Cancelados</div>
            </div>
            <div class="status-item pendente">
                <div class="numero"><?= $stats['pendentes'] ?></div>
                <div class="rotulo">Pendentes</div>
            </div>
        </div>
    </div>
    
    <!-- Cards: Novos Cadastros e Clientes Recorrentes -->
    <div class="mini-cards">
        <div class="mini-card">
            <div class="titulo">Novos Cadastros no Período</div>
            <div class="conteudo">
                <div class="item">
                    <div class="numero"><?= $novos['tutores'] ?></div>
                    <div class="rotulo">Tutores</div>
                </div>
                <div class="item">
                    <div class="numero"><?= $novos['pets'] ?></div>
                    <div class="rotulo">Pets</div>
                </div>
            </div>
        </div>
        <div class="mini-card">
            <div class="titulo">Fidelização de Clientes</div>
            <div class="conteudo">
                <div class="item">
                    <div class="numero"><?= $retorno['clientes_recorrentes'] ?></div>
                    <div class="rotulo">Recorrentes</div>
                </div>
                <div class="item">
                    <div class="numero"><?= $retorno['clientes_unicos'] ?></div>
                    <div class="rotulo">Únicos</div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Top Serviços -->
    <div class="section">
        <h2 class="section-title">Top Serviços</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px">#</th>
                    <th>Serviço</th>
                    <th>Execuções</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach(array_slice($top_servicos, 0, 5) as $i => $s): ?>
                <tr>
                    <td><span class="rank"><?= $i + 1 ?></span></td>
                    <td><?= $s['nome'] ?></td>
                    <td><?= $s['total_realizados'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Ranking de Clientes -->
    <div class="section">
        <h2 class="section-title">Ranking de Clientes - Lista Completa</h2>
        <table>
            <thead>
                <tr>
                    <th style="width: 40px">#</th>
                    <th>Cliente / Tutor</th>
                    <th>Visitas</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($top_tutores as $i => $t): ?>
                <tr>
                    <td><span class="rank"><?= $i + 1 ?></span></td>
                    <td>
                        <?= $t['nome'] ?>
                        <?php if($t['total_atendimentos'] > 1): ?>
                            <span class="tag-recorrente">⟳ Recorrente</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $t['total_atendimentos'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Comparativo com Período Anterior -->
    <div class="section">
        <h2 class="section-title">Comparativo com Período Anterior</h2>
        <p style="font-size: 10px; color: #64748b; margin-bottom: 10px;">
            Comparando com: <?= date('d/m', strtotime(explode(' a ', $comparativo['periodo_anterior'])[0])) ?> a <?= date('d/m/Y', strtotime(explode(' a ', $comparativo['periodo_anterior'])[1])) ?>
        </p>
        <div class="status-grid">
            <div class="status-item" style="background: #f1f5f9; border-color: #e2e8f0;">
                <div class="numero" style="color: #0f172a;"><?= $comparativo['atendimentos_anterior'] ?></div>
                <div class="rotulo">Atendimentos Anterior</div>
            </div>
            <div class="status-item finalizado">
                <div class="numero"><?= $stats['finalizados'] ?></div>
                <div class="rotulo">Atendimentos Atual</div>
            </div>
            <div class="status-item" style="background: <?= $comparativo['variacao_atendimentos'] >= 0 ? '#d1fae5' : '#fee2e2' ?>; border-color: <?= $comparativo['variacao_atendimentos'] >= 0 ? '#a7f3d0' : '#fecaca' ?>;">
                <div class="numero" style="color: <?= $comparativo['variacao_atendimentos'] >= 0 ? '#059669' : '#dc2626' ?>;">
                    <?= $comparativo['variacao_atendimentos'] >= 0 ? '+' : '' ?><?= number_format($comparativo['variacao_atendimentos'], 1) ?>%
                </div>
                <div class="rotulo">Variação</div>
            </div>
        </div>
    </div>
    
    <!-- Footer -->
    <div class="footer">
        <div><strong>HVT Petshop</strong> - Sistema de Gestão</div>
        <div>Gerado em <?= date('d/m/Y H:i') ?></div>
    </div>
</div>

</body>
</html>
