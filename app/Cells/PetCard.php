<?php

namespace App\Cells;

class PetCard
{
    public function render($params)
    {
        $pet = $params['pet'];
        $nome = $pet['nome'];
        $initial = mb_substr($nome, 0, 1);
        $especie = $pet['especie'] ?? 'Pet';
        $raca = $pet['raca'] ?? 'SRD';
        $tutor = $pet['tutor_nome'] ?? 'Sem Tutor';
        $id = $pet['id'];
        
        return "
            <div class=\"bg-white rounded-xl shadow-sm border border-slate-100 p-4 hover:shadow-md transition-all group flex flex-col md:flex-row md:items-center justify-between gap-4\">
                <a href=\"" . base_url("pets/ver/{$id}") . "\" class=\"flex items-center gap-4 flex-1 hover:opacity-80 transition-opacity cursor-pointer group/link\">
                    <div class=\"w-12 h-12 rounded-full bg-brand-50 text-brand-600 flex items-center justify-center text-lg font-bold shrink-0\">
                        {$initial}
                    </div>
                    <div>
                        <div class=\"flex items-center gap-2\">
                            <h3 class=\"font-bold text-slate-800 text-lg leading-tight\">{$nome}</h3>
                            <span class=\"text-xs text-slate-500 font-medium bg-slate-100 px-2 py-0.5 rounded-full\">
                                {$raca}
                            </span>
                        </div>
                        <div class=\"flex items-center gap-2 text-sm text-slate-500 mt-0.5\">
                            <i data-lucide=\"user\" class=\"w-3 h-3\"></i>
                            <span>{$tutor}</span>
                        </div>
                    </div>
                </a>
                
                <div class=\"flex items-center gap-3 pt-3 md:pt-0 border-t md:border-t-0 border-slate-50 md:border-l md:pl-4 border-slate-100\">
                     <a href=\"" . base_url("pets/editar/{$id}") . "\" class=\"px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:bg-slate-50 hover:text-slate-900 border border-slate-200 transition-colors\">
                        Editar
                    </a>
                    <a href=\"" . base_url("agenda/novo?pet={$id}") . "\" class=\"px-4 py-2 rounded-lg text-sm font-medium text-white bg-brand-500 hover:bg-brand-600 shadow-sm shadow-brand-500/20 transition-colors flex items-center gap-2\">
                        <i data-lucide=\"calendar-plus\" class=\"w-4 h-4\"></i>
                        Agendar
                    </a>
                </div>
            </div>
        ";
    }
}
