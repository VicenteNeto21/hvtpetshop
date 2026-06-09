<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\TutorModel;

class Tutores extends BaseController
{
    public function index()
    {
        $tutorModel = new TutorModel();
        
        $search = $this->request->getGet('search');
        
        if ($search) {
            $tutorModel->groupStart()
                ->like('nome', $search)
                ->orLike('telefone', $search)
                ->groupEnd();
        }

        $data = [
            'tutores' => $tutorModel->orderBy('nome', 'ASC')->paginate(10),
            'pager' => $tutorModel->pager,
            'search' => $search
        ];

        return view('tutores/index', $data);
    }

    public function novo()
    {
        return view('tutores/novo');
    }

    public function salvar()
    {
        $rules = [
            'nome' => [
                'rules' => 'required|min_length[3]',
                'errors' => [
                    'required' => 'O nome do tutor é obrigatório.',
                    'min_length' => 'O nome deve ter no mínimo 3 caracteres.'
                ]
            ],
            'telefone' => 'permit_empty',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $tutorModel = new TutorModel();
        
        $data = $this->request->getPost();
        
        // Handle checkbox
        $data['telefone_is_whatsapp'] = isset($data['telefone_is_whatsapp']) ? 'Sim' : 'Não';

        $isEdit = !empty($data['id']);
        if ($tutorModel->save($data)) {
            $id = $data['id'] ?? $tutorModel->getInsertID();
            $msg = $isEdit ? 'Dados do tutor atualizados!' : 'Tutor cadastrado com sucesso!';
            return redirect()->to('tutores/ver/' . $id)->with('success', $msg);
        } else {
            return redirect()->back()->withInput()->with('error', 'Não foi possível salvar o tutor. Verifique os dados.');
        }
    }

    public function editar($id)
    {
        $tutorModel = new TutorModel();
        $tutor = $tutorModel->find($id);

        if (!$tutor) {
            return redirect()->to('tutores')->with('error', 'Tutor não encontrado.');
        }

        return view('tutores/novo', ['tutor' => $tutor]);
    }

    public function excluir($id)
    {
        $tutorModel = new TutorModel();
        
        // TODO: Verificar se tem pets ou agendamentos antes de excluir (Soft delete seria ideal)
        if ($tutorModel->delete($id)) {
            return redirect()->back()->with('success', 'Tutor removido do sistema.');
        } else {
            return redirect()->back()->with('error', 'Não foi possível remover o tutor.');
        }
    }
    public function ver($id)
    {
        $tutorModel = new TutorModel();
        $tutor = $tutorModel->find($id);

        if (!$tutor) {
            return redirect()->to('tutores')->with('error', 'Tutor não encontrado.');
        }

        // Buscar pets do tutor
        $petModel = new \App\Models\PetModel();
        $pets = $petModel->where('tutor_id', $id)->findAll();

        return view('tutores/ver', [
            'tutor' => $tutor,
            'pets' => $pets
        ]);
    }
}
