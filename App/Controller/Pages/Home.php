<?php

namespace App\Controller\Pages;

use \App\Utils\View;
use \App\Utils\Alert;

class Home extends Page
{

    /**
     * Método responsável por retornar o conteúdo (view) da home
     * @return string
     */
    public static function getHome($request)
    {
        $content = self::getStatus($request);
        //Retorna a view da pagina
        return parent::getPage('Dashboard > ToolsVGL', $content);
    }

    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'no-permission':
                return Alert::getError('Você não tem permissão');
        }
        return '';
    }
}