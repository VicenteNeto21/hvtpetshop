<?php

namespace App\Controllers;

use App\Models\VacinaModel;

class Vacinas extends BaseController
{
    public function salvar()
    {
        $vacinaModel = new VacinaModel();
        $data = $this->request->getPost();

        $recorrencia = $data['recorrencia'] ?? 'nenhuma';
        
        // Se for série, calculamos as datas
        if ($recorrencia == 'serie') {
            $doses_totais = (int)($data['doses_totais'] ?? 1);
            $intervalo = (int)($data['intervalo_dias'] ?? 21);
            $dataBase = !empty($data['data_aplicacao']) ? $data['data_aplicacao'] : date('Y-m-d');
            
            $db = \Config\Database::connect();
            $db->transStart();
            
            for ($i = 1; $i <= $doses_totais; $i++) {
                $status = ($i == 1 && !empty($data['data_aplicacao']) && strtotime($data['data_aplicacao']) <= strtotime('today')) ? 'Aplicada' : 'Pendente';
                
                // Dose 1 usa a data base, as outras são calculadas
                if ($i == 1) {
                    $dtAplic = !empty($data['data_aplicacao']) ? $data['data_aplicacao'] : null;
                    $dtProx = date('Y-m-d', strtotime($dataBase . ' + ' . $intervalo . ' days'));
                } else {
                    $diasAdicionais = ($i - 1) * $intervalo;
                    // Proxima dose cai nela mesma pra calcularmos "para quando"
                    $dtProx = date('Y-m-d', strtotime($dataBase . ' + ' . $diasAdicionais . ' days'));
                    $dtAplic = null; 
                }

                $vacinaData = [
                    'pet_id' => $data['pet_id'],
                    'nome_vacina' => $data['nome_vacina'],
                    'lote' => $data['lote'] ?? null,
                    'veterinario' => $data['veterinario'] ?? null,
                    'recorrencia' => 'serie',
                    'dose_atual' => $i,
                    'doses_totais' => $doses_totais,
                    'data_aplicacao' => $dtAplic,
                    'data_proxima_dose' => $dtProx,
                    'status' => $status,
                    'tipo_registro' => $data['tipo_registro'] ?? 'vacina'
                ];
                $vacinaModel->insert($vacinaData);
            }
            
            $db->transComplete();
            
            if ($db->transStatus() === FALSE) {
                return redirect()->back()->with('error', 'Erro ao salvar a série de vacinas.');
            }
            return redirect()->to('pets/ver/' . $data['pet_id'])->with('success', 'Série de vacinas salva com sucesso!');
        } else {
            // Nenhuma, Anual ou Personalizado
            if ($recorrencia == 'personalizado') {
                $numero = (int)($data['personalizado_numero'] ?? 1);
                $periodo = $data['personalizado_periodo'] ?? 'meses';
                $recorrencia = "personalizado:{$numero}:{$periodo}";
                $data['recorrencia'] = $recorrencia;
            }

            if (empty($data['data_aplicacao'])) $data['data_aplicacao'] = null;
            if (empty($data['data_proxima_dose'])) {
                if ($recorrencia == 'anual' && $data['data_aplicacao']) {
                    $data['data_proxima_dose'] = date('Y-m-d', strtotime($data['data_aplicacao'] . ' + 1 year'));
                } elseif (strpos($recorrencia, 'personalizado:') === 0 && $data['data_aplicacao']) {
                    list($p, $numero, $periodo) = explode(':', $recorrencia);
                    $intervaloStr = "+ {$numero} ";
                    if ($periodo == 'dias') $intervaloStr .= 'days';
                    elseif ($periodo == 'meses') $intervaloStr .= 'months';
                    elseif ($periodo == 'anos') $intervaloStr .= 'years';
                    $data['data_proxima_dose'] = date('Y-m-d', strtotime($data['data_aplicacao'] . ' ' . $intervaloStr));
                } else {
                    $data['data_proxima_dose'] = null;
                }
            }
            
            if (!empty($data['data_aplicacao']) && strtotime($data['data_aplicacao']) <= strtotime('today')) {
                $data['status'] = 'Aplicada';
            } else {
                $data['status'] = 'Pendente';
            }

            $data['dose_atual'] = 1;
            $data['doses_totais'] = 1;

            if ($vacinaModel->save($data)) {
                // Se o usuário já cadastrou como aplicada e for anual ou personalizado, geramos a próxima pendente
                if ($data['status'] == 'Aplicada' && ($recorrencia == 'anual' || strpos($recorrencia, 'personalizado:') === 0)) {
                    $prox = date('Y-m-d', strtotime($data['data_aplicacao'] . ' + 1 year'));
                    if (strpos($recorrencia, 'personalizado:') === 0) {
                        list($p, $numero, $periodo) = explode(':', $recorrencia);
                        $intervaloStr = "+ {$numero} ";
                        if ($periodo == 'dias') $intervaloStr .= 'days';
                        elseif ($periodo == 'meses') $intervaloStr .= 'months';
                        elseif ($periodo == 'anos') $intervaloStr .= 'years';
                        $prox = date('Y-m-d', strtotime($data['data_aplicacao'] . ' ' . $intervaloStr));
                    }

                    $novaDose = [
                        'pet_id' => $data['pet_id'],
                        'nome_vacina' => $data['nome_vacina'],
                        'lote' => null,
                        'veterinario' => $data['veterinario'] ?? null,
                        'recorrencia' => $recorrencia,
                        'dose_atual' => 1,
                        'doses_totais' => 1,
                        'data_aplicacao' => null,
                        'data_proxima_dose' => $prox,
                        'status' => 'Pendente',
                        'tipo_registro' => $data['tipo_registro'] ?? 'vacina'
                    ];
                    $vacinaModel->insert($novaDose);
                }
                
                return redirect()->to('pets/ver/' . $data['pet_id'])->with('success', 'Registro salvo com sucesso!');
            } else {
                return redirect()->back()->with('error', 'Erro ao salvar a vacina.');
            }
        }
    }

    public function aplicar($id)
    {
        $vacinaModel = new VacinaModel();
        $vacina = $vacinaModel->find($id);

        if ($vacina) {
            $hoje = date('Y-m-d');
            $vacinaModel->update($id, [
                'status' => 'Aplicada',
                'data_aplicacao' => $hoje
            ]);
            
            // Se for anual ou personalizado, gerar a próxima baseada no dia da aplicação
            if ($vacina['recorrencia'] == 'anual' || strpos($vacina['recorrencia'], 'personalizado:') === 0) {
                $prox = date('Y-m-d', strtotime($hoje . ' + 1 year'));
                if (strpos($vacina['recorrencia'], 'personalizado:') === 0) {
                    list($p, $numero, $periodo) = explode(':', $vacina['recorrencia']);
                    $intervaloStr = "+ {$numero} ";
                    if ($periodo == 'dias') $intervaloStr .= 'days';
                    elseif ($periodo == 'meses') $intervaloStr .= 'months';
                    elseif ($periodo == 'anos') $intervaloStr .= 'years';
                    $prox = date('Y-m-d', strtotime($hoje . ' ' . $intervaloStr));
                }

                $novaDose = [
                    'pet_id' => $vacina['pet_id'],
                    'nome_vacina' => $vacina['nome_vacina'],
                    'recorrencia' => $vacina['recorrencia'],
                    'dose_atual' => 1,
                    'doses_totais' => 1,
                    'data_aplicacao' => null,
                    'data_proxima_dose' => $prox,
                    'status' => 'Pendente',
                    'tipo_registro' => $vacina['tipo_registro'] ?? 'vacina'
                ];
                $vacinaModel->insert($novaDose);
            }

            return redirect()->back()->with('success', 'Vacina marcada como aplicada!');
        }
        return redirect()->back()->with('error', 'Vacina não encontrada.');
    }

    public function editar($id)
    {
        $vacinaModel = new VacinaModel();
        $vacina = $vacinaModel->find($id);
        
        if (!$vacina) {
            return redirect()->back()->with('error', 'Vacina não encontrada.');
        }

        $petModel = new \App\Models\PetModel();
        $pet = $petModel->find($vacina['pet_id']);

        return view('vacinas/editar', ['vacina' => $vacina, 'pet' => $pet]);
    }

    public function atualizar($id)
    {
        $vacinaModel = new VacinaModel();
        $vacina = $vacinaModel->find($id);

        if (!$vacina) {
            return redirect()->back()->with('error', 'Vacina não encontrada.');
        }

        $data = $this->request->getPost();
        
        $updateData = [
            'nome_vacina' => $data['nome_vacina'],
            'tipo_registro' => $data['tipo_registro'] ?? 'vacina',
            'data_aplicacao' => empty($data['data_aplicacao']) ? null : $data['data_aplicacao'],
            'data_proxima_dose' => empty($data['data_proxima_dose']) ? null : $data['data_proxima_dose'],
            'lote' => $data['lote'],
            'veterinario' => $data['veterinario'],
        ];

        if (!empty($updateData['data_aplicacao']) && strtotime($updateData['data_aplicacao']) <= strtotime('today')) {
            $updateData['status'] = 'Aplicada';
        } else {
            $updateData['status'] = 'Pendente';
        }

        // Referências para ver se a data mudou e precisamos empurrar as próximas doses
        $oldRef = !empty($vacina['data_aplicacao']) ? $vacina['data_aplicacao'] : (!empty($vacina['data_proxima_dose']) ? $vacina['data_proxima_dose'] : null);
        $newRef = !empty($updateData['data_aplicacao']) ? $updateData['data_aplicacao'] : (!empty($updateData['data_proxima_dose']) ? $updateData['data_proxima_dose'] : null);

        if ($vacinaModel->update($id, $updateData)) {
            // Lógica para empurrar as datas das doses subsequentes
            if ($vacina['recorrencia'] == 'serie' && $oldRef && $newRef && $oldRef != $newRef) {
                $diffDays = round((strtotime($newRef) - strtotime($oldRef)) / 86400);
                
                if ($diffDays != 0) {
                    $subsequentes = $vacinaModel->where('pet_id', $vacina['pet_id'])
                        ->where('nome_vacina', $vacina['nome_vacina'])
                        ->where('recorrencia', 'serie')
                        ->where('dose_atual >', $vacina['dose_atual'])
                        ->findAll();
                        
                    foreach ($subsequentes as $sub) {
                        $updateSub = [];
                        if (!empty($sub['data_aplicacao'])) {
                            $updateSub['data_aplicacao'] = date('Y-m-d', strtotime($sub['data_aplicacao'] . ($diffDays >= 0 ? " +$diffDays days" : " $diffDays days")));
                        }
                        if (!empty($sub['data_proxima_dose'])) {
                            $updateSub['data_proxima_dose'] = date('Y-m-d', strtotime($sub['data_proxima_dose'] . ($diffDays >= 0 ? " +$diffDays days" : " $diffDays days")));
                        }
                        if (!empty($updateSub)) {
                            $vacinaModel->update($sub['id'], $updateSub);
                        }
                    }
                }
            }

            return redirect()->to('pets/ver/' . $data['pet_id'])->with('success', 'Registro atualizado com sucesso!');
        } else {
            return redirect()->back()->with('error', 'Erro ao atualizar registro.');
        }
    }

    public function excluir($id)
    {
        $vacinaModel = new VacinaModel();
        $vacina = $vacinaModel->find($id);

        if (!$vacina) {
            return redirect()->back()->with('error', 'Registro não encontrado.');
        }
        
        if ($vacinaModel->delete($id)) {
            // Se for série, exclui as doses subsequentes
            if ($vacina['recorrencia'] == 'serie') {
                $subsequentes = $vacinaModel->where('pet_id', $vacina['pet_id'])
                    ->where('nome_vacina', $vacina['nome_vacina'])
                    ->where('recorrencia', 'serie')
                    ->where('dose_atual >', $vacina['dose_atual'])
                    ->findAll();
                
                foreach ($subsequentes as $sub) {
                    $vacinaModel->delete($sub['id']);
                }
            }

            return redirect()->back()->with('success', 'Registro excluído com sucesso.');
        } else {
            return redirect()->back()->with('error', 'Não foi possível excluir o registro.');
        }
    }

    public function imprimir($pet_id)
    {
        $petModel = new \App\Models\PetModel();
        $vacinaModel = new VacinaModel();

        // Busca Pet com dados do Tutor
        $pet = $petModel->select('pets.*, tutores.nome as tutor_nome, tutores.telefone as tutor_telefone')
                        ->join('tutores', 'tutores.id = pets.tutor_id')
                        ->where('pets.id', $pet_id)
                        ->first();

        if (!$pet) {
            return redirect()->back()->with('error', 'Pet não encontrado.');
        }

        $vacinas = $vacinaModel->where('pet_id', $pet_id)
                               ->orderBy('data_aplicacao', 'DESC')
                               ->findAll();

        return view('vacinas/imprimir', [
            'pet' => $pet,
            'vacinas' => $vacinas
        ]);
    }
}
