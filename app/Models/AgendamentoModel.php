<?php

namespace App\Models;

use CodeIgniter\Model;

class AgendamentoModel extends Model
{
    protected $table            = 'agendamentos';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $allowedFields    = ['pet_id', 'servico_id', 'data_hora', 'status', 'observacoes', 'transporte', 'criado_por', 'criado_em'];

    // Relacionamentos manuais (Joins) serão feitos no Controller ou via métodos aqui
    
    public function getAgendamentosDoDia($data)
    {
        return $this->select('agendamentos.*, pets.nome as pet_nome, pets.especie as pet_especie, tutores.nome as tutor_nome, servicos.nome as servico_nome')
                    ->join('pets', 'pets.id = agendamentos.pet_id')
                    ->join('tutores', 'tutores.id = pets.tutor_id')
                    ->join('servicos', 'servicos.id = agendamentos.servico_id')
                    ->where("DATE(agendamentos.data_hora)", $data)
                    ->where('agendamentos.status !=', 'Cancelado')
                    ->orderBy('agendamentos.data_hora', 'ASC')
                    ->findAll();
    }

    public function getHistoricoPorPet($petId)
    {
        return $this->select('agendamentos.*, servicos.nome as servico_nome')
                    ->join('servicos', 'servicos.id = agendamentos.servico_id')
                    ->where('agendamentos.pet_id', $petId)
                    ->orderBy('agendamentos.data_hora', 'DESC')
                    ->limit(50) // Limite inicial de histórico
                    ->find();
    }
}
