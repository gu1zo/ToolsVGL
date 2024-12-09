<?php

namespace App\Controller\Admin;

use \App\Utils\View;

class Page
{


    //Modelos disponíveis no painel
    private static $modules = [
        'home' => [
            'label' => 'Home',
            'link' => URL . '/admin'
        ],
        'testimonies' => [
            'label' => 'Depoimentos',
            'link' => URL . '/admin/testimonies'
        ],
        'users' => [
            'label' => 'Usuários',
            'link' => URL . '/admin/users'
        ]

    ];
    public static function getPage($title, $content)
    {
        return View::render('admin/page', [
            'title' => $title,
            'content' => $content
        ]);
    }

    /**
     * Métido responsável por renderizar a view do painel dinamico
     * @param string $title
     * @param string $content
     * @param string $currentModule
     * @return string
     */
    public static function getPanel($title, $content, $currentModule)
    {
        $contentPanel = View::render('admin/panel', [
            'menu' => self::getMenu($currentModule),
            'content' => $content
        ]);
        return self::getPage($title, $contentPanel);
    }
    /**
     * Métidi responsável por rendereizar a view do menu do painel
     * @param string $currentModule
     * @return string
     */
    private static function getMenu($currentModule)
    {

        //LINKS DO MENU
        $links = '';

        foreach (self::$modules as $hash => $module) {
            $links .= View::render('admin/menu/link', [
                'label' => $module['label'],
                'link' => $module['link'],
                'current' => $hash == $currentModule ? 'text-danger' : ''
            ]);
        }

        //Retorna a renderização do menu
        return View::render('admin/menu/box', [
            'links' => $links
        ]);
    }


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

            $links .= View::render('admin/pagination/link', [
                'page' => $page['page'],
                'link' => $link,
                'active' => $page['current'] ? 'active' : ''
            ]);
            //renderiza o box 
        }

        return View::render('admin/pagination/box', [
            'links' => $links
        ]);

    }
}