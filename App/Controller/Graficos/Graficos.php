<?php

namespace App\Controller\Graficos;

use \App\Utils\View;
use \App\Controller\Pages\Page;

class Graficos extends Page
{

    /**
     * Método responsável por retornar a página dos gráficos renderizada
     * @return string
     */
    public static function getGraficos()
    {
        $content = View::render('/graficos/graficos', [
            'itens' => View::render('/graficos/grafico-item')
        ]);

        //Retorna a view da pagina
        return parent::getPage('Dashboard > RetisVGL', $content);
    }
    public static function getDEX()
    {
        $content = View::render('/graficos/graficos', [
            'itens' => View::render('/graficos/dex')
        ]);

        //Retorna a view da pagina
        return parent::getPage('Dashboard > RetisVGL', $content);
    }
}