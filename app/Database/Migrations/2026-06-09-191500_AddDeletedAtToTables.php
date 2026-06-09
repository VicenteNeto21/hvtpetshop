<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToTables extends Migration
{
    public function up()
    {
        $fields = [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ]
        ];

        $tables = ['tutores', 'pets', 'agendamentos', 'vacinas', 'servicos', 'usuarios'];
        
        foreach ($tables as $table) {
            $this->forge->addColumn($table, $fields);
        }
    }

    public function down()
    {
        $tables = ['tutores', 'pets', 'agendamentos', 'vacinas', 'servicos', 'usuarios'];
        
        foreach ($tables as $table) {
            $this->forge->dropColumn($table, 'deleted_at');
        }
    }
}
