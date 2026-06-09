<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProdutoModel;
use App\Models\ServicoModel;
use App\Models\TutorModel;
use App\Models\VendaModel;
use App\Models\VendaItemModel;

class Pdv extends BaseController
{
    public function index()
    {
        $produtoModel = new ProdutoModel();
        
        // Puxar alguns produtos pro grid rápido (ex: mais vendidos ou mais recentes)
        $produtos = $produtoModel->where('status', 'ativo')
                                 ->orderBy('id', 'DESC')
                                 ->limit(20)
                                 ->findAll();

        return view('pdv/index', [
            'produtos' => $produtos
        ]);
    }

    /**
     * API para buscar itens via Ajax (Código de Barras ou Nome)
     */
    public function buscar_item()
    {
        $termo = $this->request->getGet('q');
        
        $produtoModel = new ProdutoModel();
        $servicoModel = new ServicoModel();

        $resultados = [];

        if ($termo) {
            // Busca produtos (Prioriza Código de Barras primeiro)
            $produtoExato = $produtoModel->where('codigo_barras', $termo)->where('status', 'ativo')->first();
            
            if ($produtoExato) {
                // Se achou pelo código de barras, retorna ele como match exato
                return $this->response->setJSON([
                    'success' => true,
                    'exact_match' => true,
                    'item' => [
                        'id' => $produtoExato['id'],
                        'tipo' => 'produto',
                        'nome' => $produtoExato['nome'],
                        'preco' => $produtoExato['preco_venda'],
                        'estoque' => $produtoExato['estoque_atual']
                    ]
                ]);
            }

            // Busca geral (nome)
            $produtos = $produtoModel->like('nome', $termo)
                                     ->where('status', 'ativo')
                                     ->limit(10)
                                     ->findAll();
                                     
            foreach ($produtos as $p) {
                $resultados[] = [
                    'id' => $p['id'],
                    'tipo' => 'produto',
                    'nome' => $p['nome'],
                    'preco' => $p['preco_venda'],
                    'estoque' => $p['estoque_atual'],
                    'icone' => 'box'
                ];
            }

            // Busca serviços
            $servicos = $servicoModel->like('nome', $termo)
                                     ->limit(5)
                                     ->findAll();
                                     
            foreach ($servicos as $s) {
                $resultados[] = [
                    'id' => $s['id'],
                    'tipo' => 'servico',
                    'nome' => $s['nome'],
                    'preco' => $s['preco'],
                    'estoque' => null, // Serviço não tem estoque
                    'icone' => 'scissors'
                ];
            }
        }

        return $this->response->setJSON([
            'success' => true,
            'exact_match' => false,
            'items' => $resultados
        ]);
    }

    /**
     * Finaliza a venda recebendo os dados do FrontEnd (JSON)
     */
    public function finalizar()
    {
        $json = $this->request->getJSON();

        if (empty($json->itens)) {
            return $this->response->setJSON(['success' => false, 'message' => 'O carrinho está vazio.']);
        }

        $vendaModel = new VendaModel();
        $vendaItemModel = new VendaItemModel();
        $produtoModel = new ProdutoModel();

        $db = \Config\Database::connect();
        $db->transStart(); // Inicia transação

        // Cria a venda principal
        $dadosVenda = [
            'tutor_id' => !empty($json->tutor_id) ? $json->tutor_id : null,
            'usuario_id' => session()->get('usuario_id'),
            'valor_total' => $json->valor_total,
            'desconto' => $json->desconto ?? 0,
            'valor_final' => $json->valor_final,
            'forma_pagamento' => $json->forma_pagamento ?? 'dinheiro',
            'status' => 'concluida'
        ];

        $venda_id = $vendaModel->insert($dadosVenda);

        // Processa os itens e abate estoque
        foreach ($json->itens as $item) {
            $vendaItemModel->insert([
                'venda_id' => $venda_id,
                'tipo_item' => $item->tipo,
                'item_id' => $item->id,
                'quantidade' => $item->quantidade,
                'preco_unitario' => $item->preco,
                'subtotal' => $item->subtotal,
                'nome_item_snapshot' => $item->nome
            ]);

            // Se for produto, abate do estoque
            if ($item->tipo == 'produto') {
                $produto = $produtoModel->find($item->id);
                if ($produto) {
                    $novoEstoque = $produto['estoque_atual'] - $item->quantidade;
                    // Se o estoque for menor que zero, podemos bloquear ou apenas permitir e avisar.
                    // Para um PDV ágil, muitos preferem deixar o estoque negativo e ajustar depois.
                    $produtoModel->update($item->id, ['estoque_atual' => $novoEstoque]);
                }
            }
        }

        $db->transComplete();

        if ($db->transStatus() === false) {
            return $this->response->setJSON(['success' => false, 'message' => 'Erro ao finalizar a venda.']);
        }

        return $this->response->setJSON([
            'success' => true, 
            'venda_id' => $venda_id,
            'message' => 'Venda finalizada com sucesso!'
        ]);
    }
    
    public function comprovante($id)
    {
        $vendaModel = new VendaModel();
        $vendaItemModel = new VendaItemModel();
        $tutorModel = new TutorModel();
        
        $venda = $vendaModel->select('vendas.*, usuarios.nome as vendedor_nome')
                            ->join('usuarios', 'usuarios.id = vendas.usuario_id')
                            ->find($id);
                            
        if (!$venda) {
            return redirect()->to('/pdv')->with('error', 'Venda não encontrada.');
        }
        
        $itens = $vendaItemModel->where('venda_id', $id)->findAll();
        
        $tutor = null;
        if ($venda['tutor_id']) {
            $tutor = $tutorModel->find($venda['tutor_id']);
        }

        return view('pdv/comprovante', [
            'venda' => $venda,
            'itens' => $itens,
            'tutor' => $tutor
        ]);
    }
}
