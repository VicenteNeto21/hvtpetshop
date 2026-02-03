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
    protected $allowedFields    = ['pet_id', 'servico_id', 'data_hora', 'status', 'observacoes', 'transporte', 'usuario_id', 'criado_em', 'recorrencia_grupo_id', 'recorrencia_tipo'];

    // Relacionamentos manuais (Joins) serão feitos no Controller ou via métodos aqui
    
    public function getAgendamentosDoDia($data)
    {
        return $this->select('MIN(agendamentos.id) as id, pets.nome as pet_nome, pets.especie as pet_especie, tutores.nome as tutor_nome, GROUP_CONCAT(servicos.nome SEPARATOR ", ") as servico_nome, agendamentos.data_hora, agendamentos.status, agendamentos.transporte, agendamentos.observacoes')
                    ->join('pets', 'pets.id = agendamentos.pet_id')
                    ->join('tutores', 'tutores.id = pets.tutor_id')
                    ->join('servicos', 'servicos.id = agendamentos.servico_id')
                    ->where("DATE(agendamentos.data_hora)", $data)
                    ->where('agendamentos.status !=', 'Cancelado')
                    ->groupBy('agendamentos.pet_id, agendamentos.data_hora, pets.nome, pets.especie, tutores.nome, agendamentos.status, agendamentos.transporte, agendamentos.observacoes')
                    ->orderBy('agendamentos.data_hora', 'ASC')
                    ->findAll();
    }

    public function getHistoricoPorPet($petId)
    {
        return $this->select('MIN(agendamentos.id) as id, GROUP_CONCAT(servicos.nome SEPARATOR ", ") as servico_nome, agendamentos.data_hora, agendamentos.status, agendamentos.observacoes')
                    ->join('servicos', 'servicos.id = agendamentos.servico_id')
                    ->where('agendamentos.pet_id', $petId)
                    ->groupBy('agendamentos.data_hora, agendamentos.status, agendamentos.observacoes')
                    ->orderBy('agendamentos.data_hora', 'DESC')
                    ->limit(50) 
                    ->findAll();
    }
}
