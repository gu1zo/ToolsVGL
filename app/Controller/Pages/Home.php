<?php

namespace App\Controller\Pages;

use \App\Controller\Evento\Evento;
use \App\Model\Entity\Evento as EntityEvento;
use \App\Utils\View;

class Home extends Page
{

    /**
     * Método responsável por retornar o conteúdo (view) da home
     * @return string
     */
    public static function getHome($request)
    {
        $content = View::render('pages/home', [
            'itens' => self::getCards(),
            'table' => Evento::getTableEventos($request)
        ]);

        //Retorna a view da pagina
        return parent::getPage('Dashboard > RetisVGL', $content);
    }

    private static function getCards()
    {
        $content = '';
        $status = [
            [
                'name' => 'Em Análise',
                'color' => 'gray',
                'qtd' => EntityEvento::getQtdEventoByStatus('em analise')
            ],
            [

                'name' => 'Reagendado',
                'color' => 'lightblue',
                'qtd' => EntityEvento::getQtdEventoByStatus('reagendado')
            ],
            [

                'name' => 'Pendente',
                'color' => 'darkblue',
                'qtd' => EntityEvento::getQtdEventoByStatus('pendente')
            ],
            [

                'name' => 'Em Execução',
                'color' => 'yellow',
                'qtd' => EntityEvento::getQtdEventoByStatus('em execucao')
            ],
            [

                'name' => 'Clientes Afetados',
                'color' => 'red',
                'qtd' => EntityEvento::getTotalClientesAfetados()
            ]
        ];

        foreach ($status as $card) {
            $content .= View::render('pages/home/item', [
                'status' => $card['name'],
                'color' => $card['color'],
                'qtd' => $card['qtd']
            ]);
        }

        return $content;
    }
}