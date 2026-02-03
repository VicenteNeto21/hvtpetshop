<?php

namespace App\Models;

/**
 * Alias para UserModel - mantém compatibilidade com nomenclatura em português
 */
class UsuarioModel extends UserModel
{
    // Herda tudo do UserModel
    // Adiciona campo tipo aos campos permitidos
    protected $allowedFields = ['nome', 'email', 'senha', 'data_cadastro', 'status', 'tipo', 'tentativas_login_falhas', 'bloqueado_ate', 'versao_aviso_visto'];
}
