<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\PetModel;

class Pets extends BaseController
{
    public function index()
    {
        $petModel = new PetModel();
        
        $search = $this->request->getGet('search');
        
        // Base Query
        $petModel->select('pets.*, tutores.nome as tutor_nome')
                 ->join('tutores', 'tutores.id = pets.tutor_id');

        if ($search) {
            $petModel->groupStart()
                ->like('pets.nome', $search)
                ->orLike('tutores.nome', $search)
                ->orLike('pets.raca', $search)
                ->orLike('pets.id', $search)
                ->groupEnd();
        }

        $pets = $petModel->orderBy('pets.id', 'DESC')->paginate(12);

        return view('pets/index', [
            'pets' => $pets,
            'pager' => $petModel->pager,
            'search' => $search
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
    public function novo()
    {
        $tutorModel = new \App\Models\TutorModel();
        
        // Se vier com tutor_id na URL (ex: adicionar pet para um tutor específico desde a tela de tutores)
        $tutor_id = $this->request->getGet('tutor_id');
        
        return view('pets/novo', [
            'tutores' => $tutorModel->orderBy('nome', 'ASC')->findAll(),
            'selected_tutor_id' => $tutor_id
        ]);
    }

    public function salvar()
    {
        $rules = [
            'nome' => 'required|min_length[2]',
            'tutor_id' => 'required|is_not_unique[tutores.id]',
            'especie' => 'required',
            'sexo' => 'required|in_list[M,F]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $petModel = new PetModel();
        
        $data = $this->request->getPost();
        
        // Tratar campos opcionais vazios para salvar como NULL (evita 0000-00-00 ou erro de conversão)
        if (empty($data['nascimento'])) $data['nascimento'] = null;
        if (empty($data['peso']))       $data['peso'] = null;
        if (empty($data['raca']))       $data['raca'] = null;
        if (empty($data['cor']))        $data['cor'] = null;
        if (empty($data['observacoes'])) $data['observacoes'] = null;

        if ($petModel->save($data)) {
            $id = $data['id'] ?? $petModel->getInsertID();
            return redirect()->to('pets/ver/' . $id)->with('success', 'Pet salvo com sucesso!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Erro ao salvar pet.');
        }
    }

    public function editar($id)
    {
        $petModel = new PetModel();
        $tutorModel = new \App\Models\TutorModel();
        
        $pet = $petModel->find($id);

        if (!$pet) {
            return redirect()->to('pets')->with('error', 'Pet não encontrado.');
        }

        return view('pets/novo', [
            'pet' => $pet,
            'tutores' => $tutorModel->orderBy('nome', 'ASC')->findAll(),
            'selected_tutor_id' => $pet['tutor_id']
        ]);
    }

    public function excluir($id)
    {
        $petModel = new PetModel();
        
        // TODO: Verificar se tem agendamentos antes de excluir
        if ($petModel->delete($id)) {
            return redirect()->back()->with('success', 'Pet removido com sucesso.');
        } else {
            return redirect()->back()->with('error', 'Erro ao remover pet.');
        }
    }
}
