<?php

namespace App\Models;

use CodeIgniter\Model;

class PetModel extends Model
{
    protected $table            = 'pets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $allowedFields    = ['nome', 'tutor_id', 'especie', 'raca', 'idade', 'sexo', 'nascimento', 'peso', 'cor', 'observacoes'];
    
    // Dates
    protected $useTimestamps = false;
}
