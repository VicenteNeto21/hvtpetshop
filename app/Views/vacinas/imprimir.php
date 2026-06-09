<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carteira de Vacinação - <?= $pet['nome'] ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; line-height: 1.5; }
        
        .page { width: 210mm; min-height: 297mm; margin: 20px auto; background: white; padding: 20mm; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        
        .header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 30px; padding-bottom: 15px; border-bottom: 3px solid #0ea5e9; }
        .logo h1 { font-size: 24px; font-weight: 800; color: #0f172a; }
        .logo h1 span { color: #0ea5e9; }
        .logo p { font-size: 10px; color: #64748b; text-transform: uppercase; letter-spacing: 2px; margin-top: 2px; }
        .document-title { text-align: right; background: #f0fdf4; padding: 10px 16px; border-radius: 8px; border: 1px solid #bbf7d0; }
        .document-title h2 { font-size: 14px; font-weight: 700; color: #166534; text-transform: uppercase; letter-spacing: 1px; }
        .document-title p { font-size: 10px; color: #15803d; margin-top: 2px; }
        
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px; }
        .info-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 15px; }
        .info-box h3 { font-size: 11px; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; }
        .info-row { display: flex; margin-bottom: 6px; font-size: 12px; }
        .info-row:last-child { margin-bottom: 0; }
        .info-label { font-weight: 600; color: #475569; width: 90px; }
        .info-value { color: #0f172a; font-weight: 500; }
        
        .section-title { font-size: 14px; font-weight: 700; color: #0f172a; margin-bottom: 15px; display: flex; align-items: center; gap: 8px; }
        .section-title span { background: #e0f2fe; color: #0284c7; padding: 2px 8px; border-radius: 12px; font-size: 11px; }
        
        table { width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 30px; }
        th { background: #f1f5f9; padding: 10px; text-align: left; font-weight: 600; color: #475569; text-transform: uppercase; font-size: 10px; letter-spacing: 0.5px; border-bottom: 2px solid #cbd5e1; }
        td { padding: 12px 10px; border-bottom: 1px solid #e2e8f0; vertical-align: middle; }
        
        .status-badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 9px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-aplicada { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .status-pendente { background: #fef3c7; color: #92400e; border: 1px solid #fbbf24; }
        
        .tipo-badge { font-size: 9px; font-weight: 700; color: #64748b; background: #e2e8f0; padding: 2px 6px; border-radius: 4px; text-transform: uppercase; }
        .tipo-vacina { background: #e0f2fe; color: #0284c7; }
        .tipo-medicamento { background: #ffedd5; color: #c2410c; }
        
        .footer { margin-top: 50px; padding-top: 15px; border-top: 2px solid #e2e8f0; text-align: center; font-size: 10px; color: #64748b; }
        .assinatura-box { display: flex; justify-content: space-between; margin-top: 60px; }
        .assinatura-linha { width: 250px; border-top: 1px solid #94a3b8; text-align: center; padding-top: 8px; font-size: 11px; color: #475569; }
        
        .no-print { text-align: center; margin: 20px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); max-width: 800px; display: flex; justify-content: center; gap: 15px; }
        .btn { padding: 12px 24px; font-size: 14px; font-weight: 600; border-radius: 8px; cursor: pointer; text-decoration: none; border: none; display: inline-flex; align-items: center; gap: 8px; }
        .btn-print { background: #0ea5e9; color: white; }
        .btn-print:hover { background: #0284c7; }
        .btn-voltar { background: #f1f5f9; color: #475569; }
        .btn-voltar:hover { background: #e2e8f0; }
        
        @media screen and (max-width: 768px) {
            .page { width: 100%; margin: 10px auto; padding: 15px; }
            .header { flex-direction: column; gap: 15px; }
            .document-title { text-align: left; width: 100%; }
            .info-grid { grid-template-columns: 1fr; gap: 15px; }
            table { display: block; overflow-x: auto; white-space: nowrap; }
            .assinatura-box { flex-direction: column; gap: 30px; align-items: center; margin-top: 30px; }
            .assinatura-linha { width: 100%; max-width: 250px; }
            .no-print { flex-direction: column; padding: 15px; gap: 10px; }
            .btn { width: 100%; justify-content: center; margin-left: 0 !important; }
        }

        @media print {
            body { background: white; }
            .page { margin: 0; box-shadow: none; width: 100%; padding: 10mm; }
            .no-print { display: none !important; }
            table { page-break-inside: auto; }
            tr { page-break-inside: avoid; }
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        }
        @page { size: A4; margin: 10mm; }
    </style>
</head>
<body>

<div class="no-print">
    <button class="btn btn-print" onclick="window.print()">
        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"></polyline><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"></path><rect x="6" y="14" width="12" height="8"></rect></svg>
        Imprimir Carteira
    </button>
    <a href="<?= base_url('pets/ver/' . $pet['id']) ?>" class="btn btn-voltar">
        Voltar para a Ficha
    </a>
</div>

<div class="page">
    <div class="header">
        <div class="logo">
            <h1>HVT <span>PETSHOP</span></h1>
            <p>Clínica Veterinária</p>
        </div>
        <div class="document-title">
            <h2>Carteira de Vacinação</h2>
            <p>Documento Oficial do Paciente</p>
        </div>
    </div>
    
    <div class="info-grid">
        <div class="info-box">
            <h3>Dados do Paciente</h3>
            <div class="info-row">
                <span class="info-label">Nome:</span>
                <span class="info-value"><?= $pet['nome'] ?> (Id: #<?= str_pad($pet['id'], 4, '0', STR_PAD_LEFT) ?>)</span>
            </div>
            <div class="info-row">
                <span class="info-label">Espécie/Raça:</span>
                <span class="info-value"><?= $pet['especie'] ?> - <?= $pet['raca'] ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Sexo:</span>
                <span class="info-value"><?= $pet['sexo'] == 'M' ? 'Macho' : 'Fêmea' ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Nascimento:</span>
                <span class="info-value">
                    <?= (isset($pet['nascimento']) && strtotime($pet['nascimento']) > 0) ? date('d/m/Y', strtotime($pet['nascimento'])) : 'Não informado' ?>
                </span>
            </div>
        </div>
        
        <div class="info-box">
            <h3>Tutor Responsável</h3>
            <div class="info-row">
                <span class="info-label">Nome:</span>
                <span class="info-value"><?= $pet['tutor_nome'] ?></span>
            </div>
            <div class="info-row">
                <span class="info-label">Telefone:</span>
                <span class="info-value"><?= $pet['tutor_telefone'] ?: 'Não informado' ?></span>
            </div>
        </div>
    </div>
    
    <h2 class="section-title">
        Histórico de Imunização e Profilaxia
        <span><?= count($vacinas) ?> Registros</span>
    </h2>
    
    <table>
        <thead>
            <tr>
                <th>Data</th>
                <th>Tipo</th>
                <th>Vacina / Medicamento</th>
                <th>Dose</th>
                <th>Lote</th>
                <th>Veterinário</th>
                <th>Status</th>
                <th>Próxima Dose</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($vacinas)): ?>
            <tr>
                <td colspan="8" style="text-align: center; padding: 20px; color: #64748b;">Nenhum registro de vacina ou medicamento encontrado para este paciente.</td>
            </tr>
            <?php else: ?>
                <?php foreach($vacinas as $v): ?>
                <tr>
                    <td style="font-weight: 600;">
                        <?= $v['data_aplicacao'] ? date('d/m/Y', strtotime($v['data_aplicacao'])) : '-' ?>
                    </td>
                    <td>
                        <span class="tipo-badge <?= ($v['tipo_registro'] ?? 'vacina') == 'medicamento' ? 'tipo-medicamento' : 'tipo-vacina' ?>">
                            <?= ($v['tipo_registro'] ?? 'vacina') == 'medicamento' ? 'Medicamento' : 'Vacina' ?>
                        </span>
                    </td>
                    <td style="font-weight: 700; color: #0f172a;"><?= $v['nome_vacina'] ?></td>
                    <td>
                        <?php if($v['recorrencia'] == 'serie'): ?>
                            <?= $v['dose_atual'] ?>/<?= $v['doses_totais'] ?>
                        <?php elseif($v['recorrencia'] == 'anual'): ?>
                            Anual
                        <?php elseif(strpos(($v['recorrencia'] ?? ''), 'personalizado:') === 0): ?>
                            <?php 
                                list($p, $n, $per) = explode(':', $v['recorrencia']);
                                $pd = $per;
                                if ($n == 1 && $per == 'meses') $pd = 'mês';
                                elseif ($n == 1 && $per == 'anos') $pd = 'ano';
                                elseif ($n == 1 && $per == 'dias') $pd = 'dia';
                                echo "A cada {$n} {$pd}";
                            ?>
                        <?php else: ?>
                            Única
                        <?php endif; ?>
                    </td>
                    <td><?= $v['lote'] ?: '-' ?></td>
                    <td><?= $v['veterinario'] ?: '-' ?></td>
                    <td>
                        <span class="status-badge <?= $v['status'] == 'Aplicada' ? 'status-aplicada' : 'status-pendente' ?>">
                            <?= $v['status'] ?>
                        </span>
                    </td>
                    <td style="font-weight: 600; color: <?= $v['status'] == 'Pendente' ? '#b45309' : '#475569' ?>;">
                        <?= $v['data_proxima_dose'] ? date('d/m/Y', strtotime($v['data_proxima_dose'])) : '-' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    
    <div class="assinatura-box">
        <div class="assinatura-linha">
            Assinatura do Responsável (Clínica)
        </div>
        <div class="assinatura-linha">
            Assinatura do Tutor
        </div>
    </div>
    
    <div class="footer">
        Gerado pelo sistema HVT Petshop em <?= date('d/m/Y H:i') ?>.<br>
        Este documento atesta os registros de vacinas e medicamentos administrados ou recomendados pela clínica.
    </div>
</div>

</body>
</html>
