<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UserModel;
use CodeIgniter\API\ResponseTrait;

class Auth extends BaseController
{
    use ResponseTrait;

    protected bool $skipAuth = true;

    public function index()
    {
        // Se já estiver logado, redireciona para dashboard
        if (session()->get('usuario_id')) {
            return redirect()->to('/inicio');
        }
        return view('auth/login');
    }

    public function login()
    {
        $userModel = new \App\Models\UsuarioModel();
        $email = $this->request->getPost('email');
        $senha = $this->request->getPost('senha');

        if (!$email || !$senha) {
            return $this->response->setJSON(['success' => false, 'message' => 'E-mail e senha são obrigatórios.']);
        }

        $usuario = $userModel->where('email', $email)->first();

        if (!$usuario) {
            return $this->response->setJSON(['success' => false, 'message' => 'E-mail ou senha incorretos.']);
        }

        // Verifica bloqueio
        if ($usuario['bloqueado_ate'] && new \DateTime() < new \DateTime($usuario['bloqueado_ate'])) {
            return $this->response->setJSON(['success' => false, 'message' => 'Conta temporariamente bloqueada por excesso de tentativas.']);
        }

        // Verifica senha
        if (password_verify($senha, $usuario['senha'])) {
            // Verifica status
            if ($usuario['status'] === 'pendente') {
                return $this->response->setJSON(['success' => false, 'message' => 'Seu cadastro ainda está aguardando aprovação.']);
            }
            if ($usuario['status'] === 'rejeitado') {
                return $this->response->setJSON(['success' => false, 'message' => 'Seu acesso foi negado.']);
            }

            // Sucesso
            $userModel->update($usuario['id'], ['tentativas_login_falhas' => 0, 'bloqueado_ate' => null]);
            
            // Set session
            session()->set([
                'usuario_id' => $usuario['id'],
                'usuario_nome' => $usuario['nome'],
                'usuario_tipo' => $usuario['tipo'],
                'isLoggedIn' => true
            ]);

            return $this->response->setJSON(['success' => true]);
        } else {
            // Falha
            $tentativas = $usuario['tentativas_login_falhas'] + 1;
            $bloqueadoAte = $usuario['bloqueado_ate'];
            
            if ($tentativas >= 5) {
                $bloqueadoAte = (new \DateTime('+15 minutes'))->format('Y-m-d H:i:s');
            }

            $userModel->update($usuario['id'], ['tentativas_login_falhas' => $tentativas, 'bloqueado_ate' => $bloqueadoAte]);
            
            $msg = $bloqueadoAte ? 'Conta bloqueada por 15 minutos.' : 'E-mail ou senha incorretos.';
            return $this->response->setJSON(['success' => false, 'message' => $msg]);
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login?status=logout');
    }

    public function cadastro()
    {
        // Se já estiver logado, redireciona para dashboard
        if (session()->get('usuario_id')) {
            return redirect()->to('/inicio');
        }
        return view('auth/register');
    }

    public function processarCadastro()
    {
        $userModel = new \App\Models\UsuarioModel();
        
        $nome = $this->request->getPost('nome');
        $email = $this->request->getPost('email');
        $senha = $this->request->getPost('senha');
        $confirmarSenha = $this->request->getPost('confirmar_senha');

        if (!$nome || !$email || !$senha || !$confirmarSenha) {
            return $this->response->setJSON(['success' => false, 'message' => 'Todos os campos são obrigatórios.']);
        }

        if ($senha !== $confirmarSenha) {
            return $this->response->setJSON(['success' => false, 'message' => 'As senhas não coincidem.']);
        }

        // Verifica se e-mail já existe
        if ($userModel->where('email', $email)->first()) {
            return $this->response->setJSON(['success' => false, 'message' => 'Este e-mail já está cadastrado.']);
        }

        $dados = [
            'nome' => $nome,
            'email' => $email,
            'senha' => password_hash($senha, PASSWORD_DEFAULT),
            'status' => 'pendente',
            'tipo' => 'funcionario', // Por padrão, novos cadastros são funcionários
            'criado_em' => date('Y-m-d H:i:s')
        ];

        if ($userModel->insert($dados)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Cadastro realizado com sucesso! Aguarde a aprovação do administrador.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Erro ao realizar cadastro. Tente novamente.']);
    }
}
