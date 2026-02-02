<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PetModel;

class Pets extends BaseController
{
    public function index()
    {
        $petModel = new PetModel();
        // Carrega inicialmente os últimos 20 pets para performance
        $pets = $petModel->select('pets.*, tutores.nome as tutor_nome')
                         ->join('tutores', 'tutores.id = pets.tutor_id')
                         ->orderBy('pets.id', 'DESC')
                         ->paginate(12);

        return view('pets/index', [
            'pets' => $pets,
            'pager' => $petModel->pager
        ]);
    }

    public function search()
    {
        $term = $this->request->getGet('term');
        $petModel = new PetModel();
        
        $pets = $petModel->select('pets.*, tutores.nome as tutor_nome')
                         ->join('tutores', 'tutores.id = pets.tutor_id')
                         ->groupStart()
                            ->like('pets.nome', $term)
                            ->orLike('tutores.nome', $term)
                            ->orLike('pets.id', $term) // Busca por ID também
                         ->groupEnd()
                         ->orderBy('pets.id', 'DESC')
                         ->limit(50)
                         ->find();

        return $this->response->setJSON($pets);
    }

    public function ver($id)
    {
        $petModel = new PetModel();
        $agendamentoModel = new \App\Models\AgendamentoModel();

        // Busca Pet com dados do Tutor
        $pet = $petModel->select('pets.*, tutores.nome as tutor_nome, tutores.telefone as tutor_telefone')
                        ->join('tutores', 'tutores.id = pets.tutor_id')
                        ->where('pets.id', $id)
                        ->first();

        if (!$pet) {
            return redirect()->to('pets')->with('error', 'Pet não encontrado.');
        }

        // Histórico de Agendamentos
        $historico = $agendamentoModel->getHistoricoPorPet($id);

        return view('pets/ver', [
            'pet' => $pet, 
            'historico' => $historico
        ]);
    }
}
