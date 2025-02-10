<?php

namespace App\Utils;

class StringManipulation
{
    public static function processTemplate($template, $array)
    {
        // Regex para capturar o conteúdo dentro de {{ }}
        $pattern = '/{{(.*?)}}/';

        // Substituir os placeholders pela lógica do array
        $processedTemplate = preg_replace_callback($pattern, function ($matches) use ($array) {
            $key = strtolower(trim($matches[1])); // Obtém o conteúdo dentro de {{ }}
            return isset($array[$key]) && $array[$key] === 'selected' ? 'selected' : '';
        }, $template);

        return $processedTemplate;
    }

    public static function formatarTipo($string)
    {
        // Lista de palavras que precisam de correções específicas
        $substituicoesEspecificas = [
            'manutencao' => 'Manutenção',
            'emergencial' => 'Emergencial',
            'evento' => 'Evento',
        ];

        // Verifica se a string precisa de substituição específica
        if (array_key_exists($string, $substituicoesEspecificas)) {
            return $substituicoesEspecificas[$string];
        }

        // Se não estiver na lista, retorna com a primeira letra maiúscula
        return ucfirst($string);
    }
}