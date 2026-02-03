<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ServicoModel;

class Servicos extends BaseController
{
    public function index()
    {
        $servicoModel = new ServicoModel();
        
        $search = $this->request->getGet('search');
        
        if ($search) {
            $servicoModel->groupStart()
                ->like('nome', $search)
                ->orLike('descricao', $search)
                ->groupEnd();
        }
        
        $servicos = $servicoModel->orderBy('nome', 'ASC')->paginate(10);
        
        return view('servicos/index', [
            'servicos' => $servicos,
            'pager' => $servicoModel->pager,
            'search' => $search
        ]);
    }

    public function novo()
    {
        return view('servicos/novo');
    }

    public function salvar()
    {
        $rules = [
            'nome' => 'required|min_length[3]',
            'preco' => 'required|numeric',
            'duracao_estimada' => 'required|numeric' // em minutos
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $servicoModel = new ServicoModel();
        
        $data = $this->request->getPost();
        
        // Remove formatação de moeda se vier (ex: R$ 100,00 -> 100.00)
        // Mas assumiremos input number por enquanto ou trataremos viz view
        
        if ($servicoModel->save($data)) {
            return redirect()->to('servicos')->with('success', 'Serviço salvo com sucesso!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erro ao salvar serviço.');
        }
    }

    public function editar($id)
    {
        $servicoModel = new ServicoModel();
        $servico = $servicoModel->find($id);

        if (!$servico) {
            return redirect()->to('servicos')->with('error', 'Serviço não encontrado.');
        }

        return view('servicos/novo', ['servico' => $servico]);
    }

    public function excluir($id)
    {
        $servicoModel = new ServicoModel();
        
        // TODO: Verificar dependências (agendamentos futuros?)
        
        if ($servicoModel->delete($id)) {
            return redirect()->back()->with('success', 'Serviço removido com sucesso.');
        } else {
            return redirect()->back()->with('error', 'Erro ao remover serviço.');
        }
    }
}
