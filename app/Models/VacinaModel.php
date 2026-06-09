<?php

namespace App\Models;

use CodeIgniter\Model;

class VacinaModel extends Model
{
    protected $table            = 'vacinas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = true;
    protected $deletedField     = 'deleted_at';
    protected $allowedFields    = [
        'pet_id', 
        'nome_vacina', 
        'tipo_registro',
        'data_aplicacao', 
        'data_proxima_dose', 
        'lote', 
        'veterinario', 
        'status',
        'recorrencia',
        'dose_atual',
        'doses_totais'
    ];
    
    // Dates
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Retorna vacinas que vão vencer nos próximos X dias ou que já venceram e estão Pendentes
     */
    public function getVacinasVencendo($dias = 15)
    {
        $dataLimite = date('Y-m-d', strtotime("+$dias days"));
        
        return $this->select('vacinas.*, pets.nome as pet_nome, tutores.nome as tutor_nome, tutores.telefone as tutor_telefone')
                    ->join('pets', 'pets.id = vacinas.pet_id')
                    ->join('tutores', 'tutores.id = pets.tutor_id')
                    ->where('vacinas.status', 'Pendente')
                    ->where('vacinas.data_proxima_dose <=', $dataLimite)
                    ->where('vacinas.data_proxima_dose IS NOT NULL')
                    ->orderBy('vacinas.data_proxima_dose', 'ASC')
                    ->findAll();
    }
}
