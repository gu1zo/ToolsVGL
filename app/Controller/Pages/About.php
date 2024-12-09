<?php

namespace App\Controller\Pages;

use \App\Utils\View;
use \App\Model\Entity\Organization;

class About extends Page
{

    /**
     * Método responsável por retornar o conteúdo (view) da sobre
     * @return string
     */
    public static function getAbout()
    {
        $objOrganization = new Organization;


        $content = View::render('pages/about', [
            'name' => $objOrganization->name,
            'description' => $objOrganization->description,
            'site' => $objOrganization->site
        ]);

        //Retorna a view da pagina
        return parent::getPage('SOBRE > RetisVGL', $content);
    }
}