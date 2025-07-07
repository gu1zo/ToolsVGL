<?php

namespace App\Controller\Pages;

use \App\Utils\View;
use \App\Session\Login\Login;

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
        $name = Login::isLogged() ? $_SESSION['usuario']['nome'] : '';

        return View::render('pages/header', [
            'user' => $name,
            'id' => Login::getId()
        ]);
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
            'sidebar' => self::getSideBar(),
            'content' => $content
        ]);
    }


    /**
     * Método responsável por retornar a sidebar renderizada
     * @return string
     */
    public static function getSideBar()
    {

        return View::render('pages/sidebar', [
            'sections' => self::getSideBarSections()
        ]);
    }

    /**
     * Método responsável por renderizar a sidebar
     * @return string
     */
    private static function getSideBarSections()
    {
        $sections = [
            'CSA' => [
                [
                    'name' => 'Agendados',
                    'icon' => 'bi bi-calendar-fill',
                    'content' => [
                        [
                            'item' => 'Suporte',
                            'link' => URL . '/agendados?tipo=suporte'
                        ],
                        [
                            'item' => 'Digital',
                            'link' => URL . '/agendados?tipo=digital'
                        ]
                    ]

                ],
                [
                    'name' => 'Nova Ligação Perdida',
                    'icon' => 'bi bi-telephone-inbound-fill',
                    'link' => URL . '/perdidas/novo',
                    'content' => []
                ],
                [
                    'name' => 'Fila',
                    'icon' => 'bi bi-person-lines-fill',
                    'link' => URL . '/fila',
                    'content' => []
                ],
                [
                    'name' => 'Apps',
                    'icon' => 'bi bi-tools',
                    'link' => URL . '/apps',
                    'content' => []
                ],

            ],
            'GESTÃO' => [
                [
                    'name' => 'Ligações Perdidas',
                    'icon' => 'bi bi-clipboard-data-fill',
                    'link' => URL . '/perdidas',
                    'content' => []
                ],
                [
                    'name' => 'Fila Gestão',
                    'icon' => 'bi bi-person-fill-gear',
                    'link' => URL . '/fila/gestao',
                    'content' => []
                ],
                [
                    'name' => 'Notas',
                    'icon' => 'bi bi-graph-up-arrow',
                    'link' => URL . '/notas',
                    'content' => []
                ],
                [
                    'name' => 'E-mails',
                    'icon' => 'bi bi-envelope-at',
                    'link' => URL . '/emails',
                    'content' => [
                        [
                            'item' => 'Novo',
                            'link' => URL . '/emails'
                        ],
                        [
                            'item' => 'Ver E-mails',
                            'link' => URL . '/emails/table'
                        ]
                    ]
                ],
            ],
            'USUÁRIOS' => [
                [
                    'name' => 'Cadastrar Usuários',
                    'icon' => 'bi bi-person-add',
                    'link' => URL . '/usuario/novo',
                    'content' => []
                ],
                [
                    'name' => 'Listar Usuários',
                    'icon' => 'bi bi-people-fill',
                    'link' => URL . '/usuario',
                    'content' => []
                ]
            ]
        ];
        return self::getSideBarSingleSection($sections);
    }


    /**
     * Método responsável por retonrar a seção individual da sidebar renderuzada
     * @param array $vars
     * @return string
     */
    private static function getSideBarSingleSection($vars = [])
    {
        $itens = '';
        $sectionItens = '';

        foreach ($vars as $section => $value) {
            foreach ($value as $value2) {
                if (empty($value2['content'])) {
                    $sectionItens .= self::getSidebarSectionItens($value2);
                } else {
                    $sectionItens .= self::getSidebarSectionDropdownItens($value2);
                }
            }
            $itens .= View::render('pages/sidebar/section', [
                'title' => $section,
                'itens' => $sectionItens
            ]);
            $sectionItens = '';
        }

        return $itens;
    }

    /**
     * Método responsável por retornar  os itens de cada sessão
     * @param array $vars
     * @return string
     */
    private static function getSidebarSectionItens($vars = [])
    {
        return View::render('pages/sidebar/itens', [
            'name' => $vars['name'],
            'icon' => $vars['icon'],
            'link' => $vars['link']

        ]);
    }
    private static function getSidebarSectionDropdownItens($vars = [])
    {
        $dropdownItens = '';

        foreach ($vars['content'] as $item) {
            $dropdownItens .= View::render('pages/sidebar/dropdown/itens', [
                'item' => $item['item'],
                'link' => $item['link']
            ]);
        }

        return View::render('pages/sidebar/dropdown/box', [
            'name' => $vars['name'],
            'icon' => $vars['icon'],
            'itens' => $dropdownItens

        ]);
    }
}