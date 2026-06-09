<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVendasTable extends Migration
{
    public function up()
    {
        // Tabela Vendas
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'tutor_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true, // Venda pode ser balcão sem cadastro
            ],
            'usuario_id' => [
                'type'       => 'INT',
                'constraint' => 11, // Quem realizou a venda
            ],
            'valor_total' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'desconto' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'valor_final' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'forma_pagamento' => [
                'type'       => 'ENUM',
                'constraint' => ['dinheiro', 'pix', 'credito', 'debito'],
                'default'    => 'dinheiro',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['concluida', 'cancelada'],
                'default'    => 'concluida',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->createTable('vendas');

        // Tabela Vendas Itens (Itens da venda)
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'venda_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'tipo_item' => [
                'type'       => 'ENUM',
                'constraint' => ['produto', 'servico'],
                'default'    => 'produto',
            ],
            'item_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'quantidade' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
            ],
            'preco_unitario' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'subtotal' => [
                'type'       => 'DECIMAL',
                'constraint' => '10,2',
                'default'    => 0.00,
            ],
            'nome_item_snapshot' => [
                'type'       => 'VARCHAR',
                'constraint' => '150', // Salva o nome na hora da compra caso o nome mude depois
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('venda_id', 'vendas', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('vendas_itens');
    }

    public function down()
    {
        $this->forge->dropTable('vendas_itens');
        $this->forge->dropTable('vendas');
    }
}
