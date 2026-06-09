<?php

namespace App\Models;

use CodeIgniter\Model;

class VendaItemModel extends Model
{
    protected $table            = 'vendas_itens';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';

    protected $allowedFields    = [
        'venda_id',
        'tipo_item',
        'item_id',
        'quantidade',
        'preco_unitario',
        'subtotal',
        'nome_item_snapshot'
    ];

    // No timestamps for pivot/detail tables needed, but can be added if wanted.
    protected $useTimestamps = false;
}
