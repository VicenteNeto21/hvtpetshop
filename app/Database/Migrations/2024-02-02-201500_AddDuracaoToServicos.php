<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDuracaoToServicos extends Migration
{
    public function up()
    {
        $fields = [
            'duracao_estimada' => [ // Em minutos
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 30,
                'after'      => 'preco'
            ],
            // Check if others are missing too?
            // Let's assume description/nome/preco exist based on row count 4 (id, nome, preco, desc?)
        ];
        
        // Check if column exists before adding to avoid error? 
        // Forge doesn't have explicit 'hasColumn'.
        // But we know it's missing based on error.
        
        $this->forge->addColumn('servicos', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('servicos', 'duracao_estimada');
    }
}
