<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\UsuarioModel;

class Perfil extends BaseController
{
    public function index()
    {
        if (!session()->get('usuario_id')) {
            return redirect()->to('/login');
        }

        $usuarioModel = new UsuarioModel();
        $user = $usuarioModel->find(session()->get('usuario_id'));

        if (!$user) {
            return redirect()->to('/logout');
        }

        return view('usuarios/perfil', [
            'user' => $user
        ]);
    }
}
