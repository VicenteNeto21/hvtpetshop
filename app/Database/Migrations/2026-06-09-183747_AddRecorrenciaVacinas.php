<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddRecorrenciaVacinas extends Migration
{
    public function up()
    {
        $fields = [
            'recorrencia' => [
                'type'       => 'VARCHAR',
                'constraint' => '50',
                'default'    => 'nenhuma', // nenhuma, anual, serie
                'after'      => 'status'
            ],
            'dose_atual' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'recorrencia'
            ],
            'doses_totais' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'after'      => 'dose_atual'
            ],
        ];
        $this->forge->addColumn('vacinas', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('vacinas', ['recorrencia', 'dose_atual', 'doses_totais']);
    }
}
