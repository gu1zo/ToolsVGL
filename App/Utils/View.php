<?php

namespace App\Utils;

class View
{

    private static $vars = [];

    public static function init($vars = [])
    {
        self::$vars = $vars;
    }

    /**
     * Método repsonsável por retornar o conteúdo de uma view
     * @param string $view
     * @return string
     */
    private static function getContentView($view)
    {
        $file = __DIR__ . '/../../resources/view/' . $view . '.html';
        return file_exists($file) ? file_get_contents($file) : '';
    }


    /**
     * Método responsável por retonrar o conteúdo renderizado de uma view
     * @param string $view
     * @param array $vars (string/numeric)
     * @return string
     */
    public static function render($view, $vars = [])
    {
        //Conteúdo da View
        $contentView = self::getContentView($view);

        $vars = array_merge(self::$vars, $vars);

        //chaves
        $keys = array_keys($vars);
        $keys = array_map(function ($item) {
            return '{{' . $item . '}}';
        }, $keys);

        // RETORNA CONTEÚDO RENDERIZADO
        return str_replace($keys, array_values($vars), $contentView);
    }
}