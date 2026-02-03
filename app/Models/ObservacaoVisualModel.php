<?php

namespace App\Models;

use CodeIgniter\Model;

class ObservacaoVisualModel extends Model
{
    protected $table            = 'observacoes_visuais';
    protected $primaryKey       = 'id';
    protected $returnType       = 'array';
    protected $allowedFields    = ['descricao'];
}
