<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // Tem que estar logado primeiro
        if (!session()->get('usuario_id')) {
            return redirect()->to('login')->with('error', 'Acesso restrito.');
        }

        // E tem que ser admin
        if (session()->get('usuario_tipo') !== 'admin') {
            return redirect()->to('inicio')->with('error', 'Acesso negado. Apenas administradores podem acessar esta área.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        //
    }
}
