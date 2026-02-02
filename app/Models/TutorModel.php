<?php

namespace App\Models;

use CodeIgniter\Model;

class TutorModel extends Model
{
    protected $table            = 'tutores';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['nome', 'telefone', 'endereco', 'cpf', 'cidade', 'observacoes']; 
    // Ajustar campos conforme o banco real se necessário (baseado em leituras anteriores, esses parecem ser os padrões)
}
