<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddVacinasTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'pet_id' => [
                'type'       => 'INT',
                'constraint' => 11,
            ],
            'nome_vacina' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
            ],
            'data_aplicacao' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'data_proxima_dose' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'lote' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'null'       => true,
            ],
            'veterinario' => [
                'type'       => 'VARCHAR',
                'constraint' => '100',
                'null'       => true,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['Aplicada', 'Pendente'],
                'default'    => 'Aplicada',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('pet_id', 'pets', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('vacinas');
    }

    public function down()
    {
        $this->forge->dropTable('vacinas');
    }
}
