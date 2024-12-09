<?php

namespace App\Controller\Pages;

use \App\Utils\View;

class Page
{
    /**
     * Método responsável por renderizar o footer
     * @return string
     */
    private static function getFooter()
    {
        return View::render('pages/footer');
    }


    /**
     * Método responsável por renderizar o header
     * @return string
     */
    private static function getHeader()
    {
        return View::render('pages/header');
    }
    /**
     * Método responsável por renderizar o layout de paginação
     * @param Request
     * @param Pagination
     * @return string
     */
    public static function getPagination($request, $obPagination)
    {
        //PÁGINAS
        $pages = $obPagination->getPages();
        //VERIFICA QUANTIDADE DE PAGINAS
        if (count($pages) <= 1)
            return '';
        //LINKS
        $links = '';

        //URL ATUAL SEM GETS
        $url = $request->getRouter()->getCurrentUrl();

        $queryParams = $request->getQueryParams();

        foreach ($pages as $page) {
            //Altera a página
            $queryParams['page'] = $page['page'];

            //LIUNK
            $link = $url . '?' . http_build_query($queryParams);

            $links .= View::render('pages/pagination/link', [
                'page' => $page['page'],
                'link' => $link,
                'active' => $page['current'] ? 'active' : ''
            ]);
            //renderiza o box 
        }

        return View::render('pages/pagination/box', [
            'links' => $links
        ]);

    }

    /**
     * Método responsável por retornar o conteúdo (view) da página genérica
     * @return string
     */
    public static function getPage($title, $content)
    {
        return View::render('pages/page', [
            'title' => $title,
            'header' => self::getHeader(),
            'footer' => self::getFooter(),
            'content' => $content
        ]);
    }
}