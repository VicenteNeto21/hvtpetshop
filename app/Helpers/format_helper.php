<?php

if (!function_exists('formata_telefone')) {
    function formata_telefone($telefone)
    {
        $tel = preg_replace('/[^0-9]/', '', $telefone);
        
        if (strlen($tel) == 11) {
            return preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $tel);
        } elseif (strlen($tel) == 10) {
            return preg_replace('/(\d{2})(\d{4})(\d{4})/', '($1) $2-$3', $tel);
        }
        
        return $telefone;
    }
}

if (!function_exists('gerar_link_whatsapp')) {
    function gerar_link_whatsapp($telefone, $mensagem = '')
    {
        $tel = preg_replace('/[^0-9]/', '', $telefone);
        if (strlen($tel) == 10 || strlen($tel) == 11) {
            $tel = '55' . $tel; // Adiciona DDI Brasil se não tiver
        }
        
        $url = 'https://wa.me/' . $tel;
        if (!empty($mensagem)) {
            $url .= '?text=' . urlencode($mensagem);
        }
        
        return $url;
    }
}

if (!function_exists('formata_moeda')) {
    function formata_moeda($valor)
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }
}
