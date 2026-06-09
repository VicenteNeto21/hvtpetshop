<?php

namespace App\Models;

use CodeIgniter\Model;

class ProdutoModel extends Model
{
    protected $table            = 'produtos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true; // Usar lixeira segura

    protected $allowedFields    = [
        'nome',
        'descricao',
        'codigo_barras',
        'preco_venda',
        'preco_custo',
        'estoque_atual',
        'estoque_minimo',
        'status',
        'deleted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation rules (opcional para reforçar no backend)
    protected $validationRules = [
        'nome' => 'required|min_length[3]|max_length[150]',
        'preco_venda' => 'required|numeric',
        'codigo_barras' => 'permit_empty|is_unique[produtos.codigo_barras,id,{id}]'
    ];
    
    protected $validationMessages = [
        'codigo_barras' => [
            'is_unique' => 'Este código de barras já está cadastrado em outro produto.'
        ]
    ];
}
