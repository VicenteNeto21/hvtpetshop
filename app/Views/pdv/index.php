<?= $this->extend('layouts/main') ?>

<?= $this->section('title') ?>
Frente de Caixa (PDV)
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<!-- Layout do PDV: Lado Esquerdo (Busca e Grid) / Lado Direito (Carrinho) -->
<div class="flex flex-col lg:flex-row gap-6 h-[calc(100vh-140px)] -mx-2 lg:mx-0">
    
    <!-- LADO ESQUERDO: Produtos e Busca -->
    <div class="flex-1 flex flex-col h-full bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        
        <!-- Topo da Esquerda: Busca gigante -->
        <div class="p-6 border-b border-slate-100 bg-slate-50/50">
            <h2 class="text-xl font-bold text-slate-800 mb-4 tracking-tight flex items-center gap-2">
                <i data-lucide="scan-barcode" class="w-6 h-6 text-brand-500"></i>
                Adicionar Itens
            </h2>
            <div class="relative">
                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                    <i data-lucide="search" class="w-6 h-6 text-slate-400"></i>
                </div>
                <!-- Input com autofocus nativo para o leitor de barras -->
                <input type="text" id="pdv_search" autofocus autocomplete="off"
                       class="w-full pl-12 pr-4 py-4 bg-white border-2 border-slate-200 rounded-xl text-lg shadow-sm focus:ring-4 focus:ring-brand-500/20 focus:border-brand-500 transition-all font-medium placeholder-slate-400"
                       placeholder="Bipe o código de barras ou busque por nome (produto/serviço)...">
                
                <!-- Dropdown de resultados da busca -->
                <div id="search_results" class="absolute z-50 w-full mt-2 bg-white rounded-xl shadow-xl border border-slate-100 max-h-80 overflow-y-auto hidden">
                    <!-- Preenchido via JS -->
                </div>
            </div>
            <p class="text-xs text-slate-400 mt-3 font-medium flex items-center gap-1.5">
                <i data-lucide="info" class="w-4 h-4"></i>
                Dica: O leitor de código de barras USB dá "Enter" automaticamente e adiciona o item direto no carrinho.
            </p>
        </div>

        <!-- Meio da Esquerda: Grid de Produtos Rápidos -->
        <div class="p-6 flex-1 overflow-y-auto bg-slate-50/30">
            <h3 class="text-sm font-semibold text-slate-500 uppercase tracking-wider mb-4">Adição Rápida</h3>
            
            <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4">
                <?php if(!empty($produtos)): ?>
                    <?php foreach($produtos as $prod): ?>
                    <button type="button" onclick="adicionarAoCarrinho(<?= $prod['id'] ?>, 'produto', '<?= esc(addslashes($prod['nome'])) ?>', <?= $prod['preco_venda'] ?>)" 
                            class="bg-white p-4 rounded-xl border border-slate-200 shadow-sm hover:border-brand-400 hover:shadow-md hover:-translate-y-0.5 transition-all text-left group flex flex-col h-full">
                        <div class="w-10 h-10 rounded-full bg-orange-50 text-brand-500 flex items-center justify-center mb-3 group-hover:bg-brand-500 group-hover:text-white transition-colors">
                            <i data-lucide="package" class="w-5 h-5"></i>
                        </div>
                        <h4 class="font-medium text-slate-800 text-sm mb-1 line-clamp-2 flex-1"><?= esc($prod['nome']) ?></h4>
                        <div class="flex justify-between items-center mt-2 w-full">
                            <span class="text-emerald-600 font-bold">R$ <?= number_format($prod['preco_venda'], 2, ',', '.') ?></span>
                            <span class="text-xs text-slate-400 bg-slate-100 px-2 py-0.5 rounded-md font-medium"><?= $prod['estoque_atual'] ?> un</span>
                        </div>
                    </button>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-span-full text-center py-8 text-slate-500">
                        Nenhum produto ativo encontrado para adição rápida.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- LADO DIREITO: Carrinho -->
    <div class="w-full lg:w-[400px] xl:w-[450px] flex flex-col h-full bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden relative">
        <div class="absolute inset-0 bg-gradient-to-b from-slate-800/50 to-transparent pointer-events-none"></div>
        
        <div class="p-6 border-b border-slate-700/50 relative z-10">
            <h2 class="text-xl font-bold text-white tracking-tight flex items-center gap-2">
                <i data-lucide="shopping-cart" class="w-6 h-6 text-brand-400"></i>
                Carrinho Atual
            </h2>
        </div>

        <!-- Lista de Itens do Carrinho -->
        <div class="flex-1 overflow-y-auto p-4 space-y-3 relative z-10" id="cart_items">
            <!-- Vazio State -->
            <div id="cart_empty" class="h-full flex flex-col items-center justify-center text-slate-500">
                <i data-lucide="shopping-basket" class="w-16 h-16 mb-4 text-slate-700"></i>
                <p class="font-medium text-slate-400">O carrinho está vazio</p>
                <p class="text-sm mt-1 text-slate-500 text-center px-4">Bipe um código de barras ou pesquise um produto para iniciar a venda.</p>
            </div>
            <!-- Itens serão renderizados via JS -->
        </div>

        <!-- Rodapé do Carrinho (Totais e Botão) -->
        <div class="p-6 bg-slate-950 border-t border-slate-800 relative z-10">
            <div class="space-y-3 mb-6">
                <div class="flex justify-between items-center text-slate-400">
                    <span class="text-sm font-medium">Subtotal</span>
                    <span class="font-medium" id="txt_subtotal">R$ 0,00</span>
                </div>
                <div class="flex justify-between items-center text-slate-400">
                    <span class="text-sm font-medium flex items-center gap-1 cursor-pointer hover:text-white transition-colors" onclick="abrirModalDesconto()">
                        Desconto <i data-lucide="edit-3" class="w-3 h-3"></i>
                    </span>
                    <span class="font-medium text-red-400" id="txt_desconto">- R$ 0,00</span>
                </div>
                <div class="pt-3 border-t border-slate-800/80 flex justify-between items-end">
                    <span class="text-slate-300 font-semibold uppercase tracking-wider text-sm">Total a Pagar</span>
                    <span class="text-3xl font-black text-emerald-400 tracking-tight" id="txt_total">R$ 0,00</span>
                </div>
            </div>

            <button type="button" id="btn_checkout" onclick="abrirCheckout()" disabled
                    class="w-full py-4 bg-brand-500 disabled:bg-slate-800 disabled:text-slate-500 text-white font-bold rounded-xl text-lg shadow-[0_0_20px_rgba(249,115,22,0.3)] disabled:shadow-none hover:bg-brand-600 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                <i data-lucide="check-circle-2" class="w-6 h-6"></i>
                Finalizar Venda
            </button>
        </div>
    </div>
</div>

<!-- MODAL DESCONTO -->
<div id="modalDesconto" class="fixed inset-0 z-[100] flex items-center justify-center hidden bg-slate-900/50 backdrop-blur-sm">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden transform scale-95 opacity-0 transition-all duration-200" id="modalDescontoContent">
        <div class="p-5 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                <i data-lucide="percent" class="w-5 h-5 text-brand-500"></i>
                Aplicar Desconto
            </h3>
            <button type="button" onclick="fecharModalDesconto()" class="text-slate-400 hover:text-slate-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
        <div class="p-6">
            <label class="block text-sm font-medium text-slate-700 mb-2">Valor do Desconto (R$)</label>
            <input type="text" id="input_desconto" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl text-lg font-bold text-center focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 money-mask" placeholder="0,00">
            
            <button type="button" onclick="aplicarDesconto()" class="w-full mt-4 py-3 bg-brand-500 text-white font-semibold rounded-xl hover:bg-brand-600 transition-colors">
                Confirmar Desconto
            </button>
        </div>
    </div>
</div>

<!-- MODAL CHECKOUT -->
<div id="modalCheckout" class="fixed inset-0 z-[100] flex items-center justify-center hidden bg-slate-900/60 backdrop-blur-md">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col md:flex-row transform scale-95 opacity-0 transition-all duration-200" id="modalCheckoutContent">
        
        <!-- Esquerda: Resumo -->
        <div class="bg-slate-50 p-8 md:w-5/12 flex flex-col border-r border-slate-100">
            <h3 class="font-bold text-slate-800 mb-6 flex items-center gap-2">
                <i data-lucide="receipt" class="w-5 h-5 text-slate-400"></i>
                Resumo
            </h3>
            
            <div class="flex-1 space-y-4">
                <div>
                    <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1">Total</p>
                    <p class="text-3xl font-black text-emerald-600" id="checkout_total">R$ 0,00</p>
                </div>
                
                <div>
                    <label class="text-xs font-semibold text-slate-400 uppercase tracking-wider block mb-2">Vincular Cliente (Opcional)</label>
                    <select id="checkout_tutor_id" class="w-full px-3 py-2 text-sm bg-white border border-slate-200 rounded-lg focus:ring-2 focus:ring-brand-500 focus:border-brand-500">
                        <option value="">Cliente Balcão (Sem cadastro)</option>
                        <!-- Via PHP a gente listaria os tutores, pra agilizar o mockup não coloquei foreach mas ideal é via ajax ou dump -->
                    </select>
                </div>
            </div>
        </div>

        <!-- Direita: Pagamento -->
        <div class="p-8 md:w-7/12 relative">
            <button type="button" onclick="fecharCheckout()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 bg-slate-50 hover:bg-slate-100 p-2 rounded-full transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>

            <h3 class="font-bold text-slate-800 text-lg mb-6">Forma de Pagamento</h3>
            
            <div class="grid grid-cols-2 gap-3 mb-6" id="payment_methods">
                <button type="button" onclick="selecionarPagamento('dinheiro')" class="payment-btn active border-2 border-brand-500 bg-brand-50 text-brand-700 p-4 rounded-xl flex flex-col items-center gap-2 font-medium transition-all" data-method="dinheiro">
                    <i data-lucide="banknote" class="w-6 h-6"></i> Dinheiro
                </button>
                <button type="button" onclick="selecionarPagamento('pix')" class="payment-btn border border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50 p-4 rounded-xl flex flex-col items-center gap-2 font-medium transition-all" data-method="pix">
                    <i data-lucide="qr-code" class="w-6 h-6"></i> PIX
                </button>
                <button type="button" onclick="selecionarPagamento('credito')" class="payment-btn border border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50 p-4 rounded-xl flex flex-col items-center gap-2 font-medium transition-all" data-method="credito">
                    <i data-lucide="credit-card" class="w-6 h-6"></i> Cartão Crédito
                </button>
                <button type="button" onclick="selecionarPagamento('debito')" class="payment-btn border border-slate-200 text-slate-600 hover:border-slate-300 hover:bg-slate-50 p-4 rounded-xl flex flex-col items-center gap-2 font-medium transition-all" data-method="debito">
                    <i data-lucide="smartphone-nfc" class="w-6 h-6"></i> Cartão Débito
                </button>
            </div>

            <!-- Area de Troco para Dinheiro -->
            <div id="area_troco" class="mb-6 bg-slate-50 p-4 rounded-xl border border-slate-100">
                <label class="block text-sm font-medium text-slate-700 mb-2">Valor Recebido (R$)</label>
                <input type="text" id="valor_recebido" class="w-full px-4 py-3 bg-white border border-slate-200 rounded-xl font-bold text-lg focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 money-mask" placeholder="0,00" onkeyup="calcularTroco()">
                <div class="mt-3 flex justify-between items-center">
                    <span class="text-sm font-medium text-slate-500">Troco:</span>
                    <span class="text-lg font-bold text-slate-800" id="valor_troco">R$ 0,00</span>
                </div>
            </div>

            <button type="button" id="btn_confirmar_venda" onclick="processarVenda()" class="w-full py-4 bg-emerald-500 text-white font-bold rounded-xl text-lg hover:bg-emerald-600 hover:shadow-lg transition-all flex items-center justify-center gap-2">
                <i data-lucide="check" class="w-5 h-5"></i>
                Confirmar Pagamento
            </button>
        </div>
    </div>
</div>


<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
<script>
    // ESTADO DO CARRINHO
    let carrinho = [];
    let subtotalGeral = 0;
    let descontoFinal = 0;
    let totalFinal = 0;
    let formaPagamento = 'dinheiro';

    $(document).ready(function(){
        $('.money-mask').mask('#.##0,00', {reverse: true});
        lucide.createIcons();
        atualizarUI();

        // Lógica de Busca/Leitor de Barras
        let searchTimeout;
        $('#pdv_search').on('keyup', function(e) {
            let termo = $(this).val();
            
            // Se apertou Enter
            if(e.key === 'Enter') {
                e.preventDefault();
                buscarItemApi(termo, true); // true = force add if exact match
                return;
            }

            clearTimeout(searchTimeout);
            
            if(termo.length < 2) {
                $('#search_results').addClass('hidden');
                return;
            }

            searchTimeout = setTimeout(() => {
                buscarItemApi(termo, false);
            }, 300); // debounce curto
        });
        
        // Foca no input sempre que clicar fora de inputs
        $(document).on('click', function(e){
            if(!$(e.target).is('input, select, button, textarea, .lucide')) {
                if(!$('#modalCheckout').hasClass('flex')) {
                    $('#pdv_search').focus();
                }
            }
        });
    });

    function buscarItemApi(termo, isEnterPress) {
        $.ajax({
            url: '<?= base_url('pdv/buscar_item') ?>',
            data: { q: termo },
            dataType: 'json',
            success: function(res) {
                if(res.success) {
                    if(res.exact_match && isEnterPress) {
                        // Leitor de código de barras achou um único item exato
                        let item = res.item;
                        adicionarAoCarrinho(item.id, item.tipo, item.nome, item.preco);
                        $('#pdv_search').val('');
                        $('#search_results').addClass('hidden');
                    } else if (!res.exact_match) {
                        // Busca genérica, renderiza dropdown
                        renderizarBusca(res.items);
                    } else {
                        // exact_match mas não apertou enter (só digitou o EAN completo sem dar enter)
                        renderizarBusca([res.item]);
                    }
                }
            }
        });
    }

    function renderizarBusca(items) {
        let box = $('#search_results');
        box.empty();
        
        if(items.length === 0) {
            box.append('<div class="p-4 text-center text-sm text-slate-500">Nenhum item encontrado</div>');
        } else {
            items.forEach(item => {
                let icone = item.tipo === 'servico' ? 'scissors' : 'package';
                let precoFormat = formatMoney(item.preco);
                
                box.append(`
                    <div onclick="cliqueBusca(${item.id}, '${item.tipo}', '${addslashes(item.nome)}', ${item.preco})" 
                         class="p-3 border-b border-slate-50 hover:bg-slate-50 cursor-pointer flex justify-between items-center transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded bg-slate-100 flex items-center justify-center text-slate-500">
                                <i data-lucide="${icone}" class="w-4 h-4"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-800">${item.nome}</p>
                                ${item.tipo === 'produto' ? `<p class="text-xs text-slate-400">Estoque: ${item.estoque}</p>` : `<p class="text-xs text-brand-500">Serviço</p>`}
                            </div>
                        </div>
                        <span class="font-bold text-emerald-600">R$ ${precoFormat}</span>
                    </div>
                `);
            });
            lucide.createIcons();
        }
        box.removeClass('hidden');
    }

    function cliqueBusca(id, tipo, nome, preco) {
        adicionarAoCarrinho(id, tipo, nome, preco);
        $('#pdv_search').val('').focus();
        $('#search_results').addClass('hidden');
    }

    function adicionarAoCarrinho(id, tipo, nome, preco) {
        preco = parseFloat(preco);
        // Verifica se já tem no carrinho
        let existente = carrinho.find(i => i.id === id && i.tipo === tipo);
        
        if(existente) {
            existente.quantidade++;
            existente.subtotal = existente.quantidade * existente.preco;
        } else {
            carrinho.push({
                uid: Math.random().toString(36).substr(2, 9),
                id: id,
                tipo: tipo,
                nome: nome,
                preco: preco,
                quantidade: 1,
                subtotal: preco
            });
        }
        atualizarUI();
    }

    function removerDoCarrinho(uid) {
        carrinho = carrinho.filter(i => i.uid !== uid);
        atualizarUI();
    }

    function alterarQuantidade(uid, delta) {
        let item = carrinho.find(i => i.uid === uid);
        if(item) {
            item.quantidade += delta;
            if(item.quantidade <= 0) {
                removerDoCarrinho(uid);
            } else {
                item.subtotal = item.quantidade * item.preco;
                atualizarUI();
            }
        }
    }

    function atualizarUI() {
        let cartBox = $('#cart_items');
        
        if(carrinho.length === 0) {
            $('#cart_empty').show();
            $('.cart-item-row').remove();
            subtotalGeral = 0;
            descontoFinal = 0;
            $('#btn_checkout').prop('disabled', true);
        } else {
            $('#cart_empty').hide();
            $('.cart-item-row').remove();
            
            subtotalGeral = 0;
            carrinho.forEach(item => {
                subtotalGeral += parseFloat(item.subtotal);
                
                let iconColor = item.tipo === 'servico' ? 'text-brand-400 bg-brand-400/10' : 'text-slate-300 bg-slate-800';
                let iconName = item.tipo === 'servico' ? 'scissors' : 'package';

                cartBox.append(`
                    <div class="cart-item-row bg-slate-800/50 p-3 rounded-xl border border-slate-700/50 flex flex-col gap-3 group transition-colors hover:bg-slate-800 hover:border-slate-600">
                        <div class="flex justify-between items-start gap-2">
                            <div class="flex gap-3 overflow-hidden">
                                <div class="w-10 h-10 rounded-lg ${iconColor} flex items-center justify-center shrink-0">
                                    <i data-lucide="${iconName}" class="w-5 h-5"></i>
                                </div>
                                <div class="truncate">
                                    <h4 class="text-sm font-medium text-slate-200 truncate" title="${item.nome}">${item.nome}</h4>
                                    <p class="text-xs text-slate-500">R$ ${formatMoney(item.preco)} unit</p>
                                </div>
                            </div>
                            <button onclick="removerDoCarrinho('${item.uid}')" class="text-slate-600 hover:text-red-400 p-1 shrink-0 transition-colors">
                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                            </button>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <!-- Input Quantidade Customizado -->
                            <div class="flex items-center bg-slate-900 rounded-lg p-0.5 border border-slate-700">
                                <button onclick="alterarQuantidade('${item.uid}', -1)" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-800 rounded-md transition-colors"><i data-lucide="minus" class="w-3 h-3"></i></button>
                                <span class="w-8 text-center text-sm font-medium text-white">${item.quantidade}</span>
                                <button onclick="alterarQuantidade('${item.uid}', 1)" class="w-7 h-7 flex items-center justify-center text-slate-400 hover:text-white hover:bg-slate-800 rounded-md transition-colors"><i data-lucide="plus" class="w-3 h-3"></i></button>
                            </div>
                            
                            <span class="font-bold text-emerald-400 text-sm">R$ ${formatMoney(item.subtotal)}</span>
                        </div>
                    </div>
                `);
            });
            $('#btn_checkout').prop('disabled', false);
        }

        totalFinal = subtotalGeral - descontoFinal;
        if(totalFinal < 0) totalFinal = 0;

        $('#txt_subtotal').text('R$ ' + formatMoney(subtotalGeral));
        $('#txt_desconto').text('- R$ ' + formatMoney(descontoFinal));
        $('#txt_total').text('R$ ' + formatMoney(totalFinal));
        $('#checkout_total').text('R$ ' + formatMoney(totalFinal));

        lucide.createIcons();
    }

    // DESCONTO
    function abrirModalDesconto() {
        if(carrinho.length === 0) return;
        $('#modalDesconto').removeClass('hidden').addClass('flex');
        setTimeout(() => {
            $('#modalDescontoContent').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
            $('#input_desconto').val(formatMoney(descontoFinal)).focus();
        }, 10);
    }

    function fecharModalDesconto() {
        $('#modalDescontoContent').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => {
            $('#modalDesconto').removeClass('flex').addClass('hidden');
            $('#pdv_search').focus();
        }, 200);
    }

    function aplicarDesconto() {
        let val = $('#input_desconto').val();
        if(!val) val = '0,00';
        
        let numerico = parseFloat(val.replace(/\./g, '').replace(',', '.'));
        if(numerico > subtotalGeral) {
            alert('O desconto não pode ser maior que o subtotal!');
            return;
        }
        
        descontoFinal = numerico;
        atualizarUI();
        fecharModalDesconto();
    }

    // CHECKOUT
    function abrirCheckout() {
        $('#modalCheckout').removeClass('hidden').addClass('flex');
        setTimeout(() => {
            $('#modalCheckoutContent').removeClass('scale-95 opacity-0').addClass('scale-100 opacity-100');
            selecionarPagamento('dinheiro'); // Reset pro padrão
            $('#valor_recebido').val('');
            $('#valor_troco').text('R$ 0,00');
        }, 10);
    }

    function fecharCheckout() {
        $('#modalCheckoutContent').removeClass('scale-100 opacity-100').addClass('scale-95 opacity-0');
        setTimeout(() => {
            $('#modalCheckout').removeClass('flex').addClass('hidden');
            $('#pdv_search').focus();
        }, 200);
    }

    function selecionarPagamento(metodo) {
        formaPagamento = metodo;
        $('.payment-btn').removeClass('active border-2 border-brand-500 bg-brand-50 text-brand-700').addClass('border border-slate-200 text-slate-600');
        $(`button[data-method="${metodo}"]`).addClass('active border-2 border-brand-500 bg-brand-50 text-brand-700').removeClass('border border-slate-200 text-slate-600');

        if(metodo === 'dinheiro') {
            $('#area_troco').slideDown(200);
            setTimeout(() => { $('#valor_recebido').focus(); }, 200);
        } else {
            $('#area_troco').slideUp(200);
        }
    }

    function calcularTroco() {
        let valRec = $('#valor_recebido').val();
        if(!valRec) {
            $('#valor_troco').text('R$ 0,00');
            return;
        }
        let recebido = parseFloat(valRec.replace(/\./g, '').replace(',', '.'));
        let troco = recebido - totalFinal;
        if(troco < 0) troco = 0;
        $('#valor_troco').text('R$ ' + formatMoney(troco));
    }

    function processarVenda() {
        let btn = $('#btn_confirmar_venda');
        btn.prop('disabled', true).html('<i data-lucide="loader-2" class="w-5 h-5 animate-spin"></i> Processando...');
        lucide.createIcons();

        let tutor_id = $('#checkout_tutor_id').val();

        let payload = {
            tutor_id: tutor_id,
            valor_total: subtotalGeral,
            desconto: descontoFinal,
            valor_final: totalFinal,
            forma_pagamento: formaPagamento,
            itens: carrinho
        };

        $.ajax({
            url: '<?= base_url('pdv/finalizar') ?>',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(payload),
            success: function(res) {
                if(res.success) {
                    // Redireciona pro comprovante
                    window.location.href = '<?= base_url('pdv/comprovante/') ?>' + res.venda_id;
                } else {
                    alert(res.message || 'Erro ao finalizar venda.');
                    btn.prop('disabled', false).html('<i data-lucide="check" class="w-5 h-5"></i> Confirmar Pagamento');
                    lucide.createIcons();
                }
            },
            error: function() {
                alert('Erro de comunicação com o servidor.');
                btn.prop('disabled', false).html('<i data-lucide="check" class="w-5 h-5"></i> Confirmar Pagamento');
                lucide.createIcons();
            }
        });
    }

    // Utils
    function formatMoney(amount) {
        return parseFloat(amount).toLocaleString('pt-BR', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    }
    
    function addslashes(string) {
        return string.replace(/\\/g, '\\\\').
            replace(/\u0008/g, '\\b').
            replace(/\t/g, '\\t').
            replace(/\n/g, '\\n').
            replace(/\f/g, '\\f').
            replace(/\r/g, '\\r').
            replace(/'/g, '\\\'').
            replace(/"/g, '\\"');
    }
</script>

<?= $this->endSection() ?>
