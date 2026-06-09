<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProdutoModel;

class Produtos extends BaseController
{
    protected $produtoModel;

    public function __construct()
    {
        $this->produtoModel = new ProdutoModel();
    }

    public function index()
    {
        // Buscar produtos (paginação básica ou todos)
        // Por se tratar de PDV, é melhor paginação ou busca dinâmica. Aqui vamos listar todos por enquanto.
        $data['produtos'] = $this->produtoModel->orderBy('nome', 'ASC')->findAll();
        return view('produtos/index', $data);
    }

    public function cadastrar()
    {
        return view('produtos/form');
    }

    public function salvar()
    {
        $id = $this->request->getPost('id');
        $rules = [
            'nome' => 'required|min_length[3]|max_length[150]',
            'preco_venda' => 'required|numeric',
        ];

        // Se tiver ID, a validação is_unique ignora o ID atual. Se não, verifica geral.
        $codigo_barras = $this->request->getPost('codigo_barras');
        if (!empty($codigo_barras)) {
            $rules['codigo_barras'] = $id ? "is_unique[produtos.codigo_barras,id,{$id}]" : 'is_unique[produtos.codigo_barras]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $dados = [
            'nome' => $this->request->getPost('nome'),
            'descricao' => $this->request->getPost('descricao'),
            'codigo_barras' => empty($codigo_barras) ? null : $codigo_barras,
            'preco_venda' => formatarPrecoParaBanco($this->request->getPost('preco_venda')),
            'preco_custo' => formatarPrecoParaBanco($this->request->getPost('preco_custo')),
            'estoque_atual' => $this->request->getPost('estoque_atual') ?: 0,
            'estoque_minimo' => $this->request->getPost('estoque_minimo') ?: 0,
            'status' => $this->request->getPost('status') ?? 'ativo',
        ];

        if ($id) {
            $this->produtoModel->update($id, $dados);
            $msg = 'Produto atualizado com sucesso.';
        } else {
            $this->produtoModel->insert($dados);
            $msg = 'Produto cadastrado com sucesso.';
        }

        return redirect()->to('/produtos')->with('success', $msg);
    }

    public function editar($id)
    {
        $produto = $this->produtoModel->find($id);
        if (!$produto) {
            return redirect()->to('/produtos')->with('error', 'Produto não encontrado.');
        }

        return view('produtos/form', ['produto' => $produto]);
    }

    public function excluir($id)
    {
        if ($this->produtoModel->delete($id)) {
            return redirect()->to('/produtos')->with('success', 'Produto removido com sucesso.');
        }
        return redirect()->to('/produtos')->with('error', 'Erro ao remover produto.');
    }
}

// Helpers para formato de moeda
if (!function_exists('formatarPrecoParaBanco')) {
    function formatarPrecoParaBanco($valor) {
        if(empty($valor)) return 0;
        $valor = str_replace('.', '', $valor); // Remove separador de milhar
        $valor = str_replace(',', '.', $valor); // Troca vírgula por ponto
        return (float) $valor;
    }
}
