<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Comprovante de Venda #<?= str_pad($venda['id'], 5, '0', STR_PAD_LEFT) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Removendo cores roxas para usar o padrão brand (Laranja) -->
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#fff7ed',
                            100: '#ffedd5',
                            200: '#fed7aa',
                            300: '#fdba74',
                            400: '#fb923c',
                            500: '#f97316',
                            600: '#ea580c',
                            700: '#c2410c',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .page {
            width: 21cm;
            min-height: 29.7cm;
            padding: 2cm;
            margin: 1cm auto;
            background: white;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
        }
        
        @media print {
            body { background: white; margin: 0; padding: 0; }
            .page { 
                margin: 0; 
                border: initial; 
                border-radius: initial; 
                width: initial; 
                min-height: initial; 
                box-shadow: initial; 
                background: initial; 
                page-break-after: always;
                padding: 1.5cm;
            }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="text-slate-800">

    <!-- Topbar Actions -->
    <div class="no-print fixed top-0 left-0 w-full bg-white border-b border-slate-200 shadow-sm z-50 py-3 px-6 flex justify-between items-center">
        <a href="<?= base_url('pdv') ?>" class="inline-flex items-center gap-2 text-slate-500 hover:text-slate-800 transition-colors font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            Voltar ao PDV
        </a>
        
        <button onclick="window.print()" class="inline-flex items-center gap-2 px-5 py-2 bg-brand-500 text-white rounded-lg hover:bg-brand-600 transition-colors shadow-sm font-medium">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            Imprimir Comprovante
        </button>
    </div>

    <!-- Espaçamento pra compensar a topbar -->
    <div class="h-16 no-print"></div>

    <div class="page rounded-xl">
        <!-- Cabeçalho -->
        <div class="flex justify-between items-start border-b-2 border-slate-100 pb-8 mb-8">
            <div class="flex items-center gap-4">
                <!-- Logo Mock -->
                <div class="w-16 h-16 bg-brand-50 rounded-2xl flex items-center justify-center">
                    <svg class="w-8 h-8 text-brand-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800">CereniaPet Clínica Veterinária</h1>
                    <p class="text-slate-500 text-sm mt-1">CNPJ: 00.000.000/0001-00</p>
                    <p class="text-slate-500 text-sm">Av. Principal, 123 - Centro, Cidade - UF</p>
                    <p class="text-slate-500 text-sm">(00) 90000-0000 | contato@cereniapet.com.br</p>
                </div>
            </div>
            <div class="text-right">
                <h2 class="text-3xl font-black text-slate-800 mb-2 tracking-tight">RECIBO</h2>
                <div class="inline-flex items-center justify-center px-3 py-1 bg-slate-100 rounded-md font-bold text-slate-700 font-mono tracking-widest text-sm mb-3">
                    Nº <?= str_pad($venda['id'], 6, '0', STR_PAD_LEFT) ?>
                </div>
                <p class="text-slate-500 font-medium text-sm">Data: <?= date('d/m/Y H:i', strtotime($venda['created_at'])) ?></p>
            </div>
        </div>

        <!-- Info do Cliente -->
        <div class="bg-slate-50 rounded-xl p-5 mb-8 border border-slate-100">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                Dados do Cliente
            </h3>
            
            <?php if($tutor): ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Nome / Razão Social</p>
                        <p class="font-semibold text-slate-800"><?= esc($tutor['nome']) ?></p>
                    </div>
                    <div>
                        <p class="text-sm text-slate-500 mb-1">Telefone</p>
                        <p class="font-semibold text-slate-800"><?= esc($tutor['telefone'] ?? 'Não informado') ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div class="text-slate-600 font-medium">Consumidor Final (Venda Balcão)</div>
            <?php endif; ?>
        </div>

        <!-- Itens -->
        <div class="mb-8">
            <table class="w-full text-left">
                <thead>
                    <tr class="border-b-2 border-slate-200">
                        <th class="py-3 px-2 font-bold text-slate-700 text-sm">Cód</th>
                        <th class="py-3 px-2 font-bold text-slate-700 text-sm">Descrição</th>
                        <th class="py-3 px-2 font-bold text-slate-700 text-sm text-center">Tipo</th>
                        <th class="py-3 px-2 font-bold text-slate-700 text-sm text-center">Qtd</th>
                        <th class="py-3 px-2 font-bold text-slate-700 text-sm text-right">V. Unitário</th>
                        <th class="py-3 px-2 font-bold text-slate-700 text-sm text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php foreach($itens as $item): ?>
                    <tr>
                        <td class="py-4 px-2 text-sm text-slate-500 font-mono"><?= str_pad($item['item_id'], 4, '0', STR_PAD_LEFT) ?></td>
                        <td class="py-4 px-2 text-sm font-medium text-slate-800"><?= esc($item['nome_item_snapshot']) ?></td>
                        <td class="py-4 px-2 text-xs text-center">
                            <?php if($item['tipo_item'] == 'servico'): ?>
                                <span class="bg-brand-50 text-brand-600 px-2 py-1 rounded font-medium">Serviço</span>
                            <?php else: ?>
                                <span class="bg-slate-100 text-slate-600 px-2 py-1 rounded font-medium">Produto</span>
                            <?php endif; ?>
                        </td>
                        <td class="py-4 px-2 text-sm text-slate-700 text-center"><?= $item['quantidade'] ?></td>
                        <td class="py-4 px-2 text-sm text-slate-700 text-right">R$ <?= number_format($item['preco_unitario'], 2, ',', '.') ?></td>
                        <td class="py-4 px-2 text-sm font-bold text-slate-800 text-right">R$ <?= number_format($item['subtotal'], 2, ',', '.') ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Totais -->
        <div class="flex justify-end">
            <div class="w-72 bg-slate-50 rounded-xl p-5 border border-slate-100">
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-sm text-slate-600">
                        <span>Subtotal</span>
                        <span class="font-medium">R$ <?= number_format($venda['valor_total'], 2, ',', '.') ?></span>
                    </div>
                    <?php if($venda['desconto'] > 0): ?>
                    <div class="flex justify-between items-center text-sm text-red-500">
                        <span>Desconto</span>
                        <span class="font-medium">- R$ <?= number_format($venda['desconto'], 2, ',', '.') ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="pt-3 mt-3 border-t border-slate-200">
                        <div class="flex justify-between items-end">
                            <span class="font-bold text-slate-700 text-sm">Total a Pagar</span>
                            <span class="text-2xl font-black text-brand-600 tracking-tight">R$ <?= number_format($venda['valor_final'], 2, ',', '.') ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Info Pagamento -->
        <div class="mt-8 pt-8 border-t border-slate-200">
            <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2">Detalhes do Pagamento</h3>
            <div class="flex gap-8">
                <div>
                    <p class="text-sm text-slate-500 mb-1">Forma de Pagamento</p>
                    <p class="font-semibold text-slate-800 capitalize flex items-center gap-2">
                        <?php 
                            $icones = [
                                'dinheiro' => '<svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
                                'pix' => '<svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"></path></svg>',
                                'credito' => '<svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>',
                                'debito' => '<svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>'
                            ];
                            echo ($icones[$venda['forma_pagamento']] ?? '') . $venda['forma_pagamento'];
                        ?>
                    </p>
                </div>
                <div>
                    <p class="text-sm text-slate-500 mb-1">Atendente</p>
                    <p class="font-semibold text-slate-800"><?= esc($venda['vendedor_nome']) ?></p>
                </div>
            </div>
        </div>

        <div class="mt-12 text-center">
            <p class="text-sm font-medium text-slate-800 mb-1">Agradecemos a preferência!</p>
            <p class="text-xs text-slate-400">Documento sem valor fiscal.</p>
        </div>
    </div>
    
</body>
</html>
