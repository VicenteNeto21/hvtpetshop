<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Usuarios extends BaseController
{
    protected $usuarioModel;

    public function __construct()
    {
        $this->usuarioModel = new \App\Models\UsuarioModel();
    }

    public function index()
    {
        if (!session()->get('usuario_id')) {
            return redirect()->to('/login');
        }

        // Verificar se é admin
        $usuarioLogado = $this->usuarioModel->find(session()->get('usuario_id'));
        if ($usuarioLogado['tipo'] !== 'admin') {
            return redirect()->to('/dashboard')->with('error', 'Acesso negado.');
        }

        $status = $this->request->getGet('status') ?? 'todos';
        
        $query = $this->usuarioModel->orderBy('criado_em', 'DESC');
        
        if ($status !== 'todos') {
            $query->where('status', $status);
        }
        
        $usuarios = $query->findAll();
        
        // Contadores
        $contadores = [
            'pendentes' => $this->usuarioModel->where('status', 'pendente')->countAllResults(),
            'aprovados' => $this->usuarioModel->where('status', 'aprovado')->countAllResults(),
            'rejeitados' => $this->usuarioModel->where('status', 'rejeitado')->countAllResults(),
        ];

        return view('usuarios/index', [
            'usuarios' => $usuarios,
            'statusSelecionado' => $status,
            'contadores' => $contadores
        ]);
    }

    public function aprovar($id)
    {
        if (!session()->get('usuario_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Não autorizado'])->setStatusCode(401);
        }

        // Verificar se é admin
        $usuarioLogado = $this->usuarioModel->find(session()->get('usuario_id'));
        if ($usuarioLogado['tipo'] !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Acesso negado'])->setStatusCode(403);
        }

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            return $this->response->setJSON(['success' => false, 'message' => 'Usuário não encontrado'])->setStatusCode(404);
        }

        $this->usuarioModel->update($id, ['status' => 'aprovado']);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Usuário aprovado com sucesso!']);
        }

        return redirect()->to('/usuarios')->with('success', 'Usuário aprovado com sucesso!');
    }

    public function rejeitar($id)
    {
        if (!session()->get('usuario_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Não autorizado'])->setStatusCode(401);
        }

        // Verificar se é admin
        $usuarioLogado = $this->usuarioModel->find(session()->get('usuario_id'));
        if ($usuarioLogado['tipo'] !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Acesso negado'])->setStatusCode(403);
        }

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            return $this->response->setJSON(['success' => false, 'message' => 'Usuário não encontrado'])->setStatusCode(404);
        }

        $this->usuarioModel->update($id, ['status' => 'rejeitado']);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Usuário rejeitado.']);
        }

        return redirect()->to('/usuarios')->with('success', 'Usuário rejeitado.');
    }

    public function excluir($id)
    {
        if (!session()->get('usuario_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Não autorizado'])->setStatusCode(401);
        }

        // Verificar se é admin
        $usuarioLogado = $this->usuarioModel->find(session()->get('usuario_id'));
        if ($usuarioLogado['tipo'] !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Acesso negado'])->setStatusCode(403);
        }

        // Não permitir excluir a si mesmo
        if ($id == session()->get('usuario_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Você não pode excluir sua própria conta'])->setStatusCode(400);
        }

        $this->usuarioModel->delete($id);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON(['success' => true, 'message' => 'Usuário excluído.']);
        }

        return redirect()->to('/usuarios')->with('success', 'Usuário excluído.');
    }

    public function alternarTipo($id)
    {
        if (!session()->get('usuario_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Não autorizado'])->setStatusCode(401);
        }

        // Verificar se é admin
        $usuarioLogado = $this->usuarioModel->find(session()->get('usuario_id'));
        if ($usuarioLogado['tipo'] !== 'admin') {
            return $this->response->setJSON(['success' => false, 'message' => 'Acesso negado'])->setStatusCode(403);
        }

        // Não permitir alterar a si mesmo
        if ($id == session()->get('usuario_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Você não pode alterar seu próprio tipo'])->setStatusCode(400);
        }

        $usuario = $this->usuarioModel->find($id);
        if (!$usuario) {
            return $this->response->setJSON(['success' => false, 'message' => 'Usuário não encontrado'])->setStatusCode(404);
        }

        // Alternar tipo
        $novoTipo = $usuario['tipo'] === 'admin' ? 'funcionario' : 'admin';
        $this->usuarioModel->update($id, ['tipo' => $novoTipo]);

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => true, 
                'message' => 'Tipo alterado para ' . ucfirst($novoTipo),
                'novoTipo' => $novoTipo
            ]);
        }

        return redirect()->to('/usuarios')->with('success', 'Tipo alterado para ' . ucfirst($novoTipo));
    }
}
