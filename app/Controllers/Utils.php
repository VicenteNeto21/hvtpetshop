<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Utils extends BaseController
{
    public function marcarAvisoVisto()
    {
        if (!session()->get('usuario_id')) {
            return $this->response->setJSON(['success' => false, 'message' => 'Não autorizado'])->setStatusCode(401);
        }

        $versaoAtual = '3.1.0-PRO';
        
        $usuarioModel = new \App\Models\UsuarioModel();
        $usuarioModel->update(session()->get('usuario_id'), [
            'versao_aviso_visto' => $versaoAtual
        ]);

        session()->set('aviso_visto_versao', $versaoAtual);

        return $this->response->setJSON(['success' => true]);
    }
}
