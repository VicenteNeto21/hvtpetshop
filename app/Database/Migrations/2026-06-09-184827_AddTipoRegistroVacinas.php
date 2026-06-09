<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddTipoRegistroVacinas extends Migration
{
    public function up()
    {
        $fields = [
            'tipo_registro' => [
                'type'       => 'ENUM',
                'constraint' => ['vacina', 'medicamento'],
                'default'    => 'vacina',
                'after'      => 'nome_vacina'
            ],
        ];
        $this->forge->addColumn('vacinas', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('vacinas', 'tipo_registro');
    }
}
