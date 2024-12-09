<?php

namespace App\Controller\Admin;

use App\http\Request;
use \App\Utils\View;

class Home extends Page
{

    /**
     * Método responsável por retornar o painel renderizado
     * @param Request $request
     * @return string
     */
    public static function getHome($request)
    {
        //Conteúdo da home
        $content = View::render('admin/modules/home/index', []);

        //Retorna a página
        return parent::getPanel('HOME > RetisVGL', $content, 'home');
    }
}