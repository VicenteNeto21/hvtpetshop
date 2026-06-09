<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Se não estiver logado, redireciona para login
        if (!session()->get('usuario_id')) {
            return redirect()->to('login')->with('error', 'Você precisa fazer login para acessar o sistema.');
        }

        // Se o usuário estiver bloqueado pelo admin, também barra
        // Verifica no banco (opcional para maior segurança) ou confia na sessão (vamos confiar na sessão por enquanto)
        if (session()->get('usuario_status') === 'Pendente') {
            return redirect()->to('auth/login')->with('error', 'Sua conta ainda não foi aprovada pelo Administrador.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
