<?php

namespace App\Models;

use CodeIgniter\Model;

class FichaModel extends Model
{
    protected $table            = 'fichas_petshop';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'agendamento_id', 'funcionario_id', 'altura_pelos', 
        'doenca_pre_existente', 'doenca_ouvido', 'doenca_pele', 
        'observacoes', 'comportamento_pet', 'recomendacoes_tutor'
    ];
}
