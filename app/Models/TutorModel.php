<?php

namespace App\Models;

use CodeIgniter\Model;

class TutorModel extends Model
{
    protected $table            = 'tutores';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $allowedFields    = [
        'nome', 'telefone', 'telefone_is_whatsapp', 'email', 
        'cep', 'rua', 'numero', 'bairro', 'cidade', 'uf', 'observacoes'
    ]; 
    // Ajustar campos conforme o banco real se necessário (baseado em leituras anteriores, esses parecem ser os padrões)
}
