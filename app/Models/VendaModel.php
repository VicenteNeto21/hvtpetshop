<?php

namespace App\Models;

use CodeIgniter\Model;

class VendaModel extends Model
{
    protected $table            = 'vendas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;

    protected $allowedFields    = [
        'tutor_id',
        'usuario_id',
        'valor_total',
        'desconto',
        'valor_final',
        'forma_pagamento',
        'status',
        'deleted_at'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    public function getVendasComDetalhes($data_inicio = null, $data_fim = null)
    {
        $builder = $this->select('vendas.*, tutores.nome as tutor_nome, usuarios.nome as vendedor_nome')
                        ->join('tutores', 'tutores.id = vendas.tutor_id', 'left')
                        ->join('usuarios', 'usuarios.id = vendas.usuario_id', 'left');

        if ($data_inicio && $data_fim) {
            $builder->where('vendas.created_at >=', $data_inicio . ' 00:00:00')
                    ->where('vendas.created_at <=', $data_fim . ' 23:59:59');
        }

        return $builder->orderBy('vendas.created_at', 'DESC')->findAll();
    }
}
