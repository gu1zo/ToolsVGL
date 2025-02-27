<?php

namespace App\Controller\Pages;

use \App\Utils\View;

class Home extends Page
{

    /**
     * Método responsável por retornar o conteúdo (view) da home
     * @return string
     */
    public static function getHome($request)
    {
        //Retorna a view da pagina
        return parent::getPage('Dashboard > ToolsVGL', '');
    }
}