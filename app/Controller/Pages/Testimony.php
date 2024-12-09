<?php

namespace App\Controller\Pages;

use \App\Utils\View;
use \App\Model\Entity\Testimony as EntityTestimony;
use \WilliamCosta\DatabaseManager\Pagination;

class Testimony extends Page
{
    /**
     * Método responsável por obter a renderização dos itens de depoimentos para a página
     * @param Request
     * @param Pagination
     * @return string
     */
    private static function getTestimonyItems($request, &$obPagination)
    {
        $itens = '';

        //TOTAL DE REGISTROS
        $quantidadetotal = EntityTestimony::getTestimonies(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        //PAGINA ATUAL
        $queryParams = $request->getQueryParams();
        $paginaAtual = $queryParams['page'] ?? 1;

        $obPagination = new Pagination($quantidadetotal, $paginaAtual, 3);

        $results = EntityTestimony::getTestimonies(null, 'id DESC', $obPagination->getLimit());

        while ($obTestimony = $results->fetchObject(EntityTestimony::class)) {
            $itens .= View::render('pages/testimony/item', [
                'nome' => $obTestimony->nome,
                'mensagem' => $obTestimony->mensagem,
                'data' => date('d/m/Y H:i:s', strtotime($obTestimony->data))
            ]);
        }
        return $itens;
    }
    /**
     * Método responsável por retornar o conteúdo (view) de depoimentos
     * @return string
     */
    public static function getTestimonies($request)
    {
        $content = View::render('pages/testimonies', [
            'itens' => self::getTestimonyItems($request, $obPagination),
            'pagination' => parent::getPagination($request, $obPagination)
        ]);
        //Retorna a view da pagina
        return parent::getPage('DEPOIMENTOS > RetisVGL', $content);
    }

    /**
     * Método responsável por cadastrar o depoimento
     * @param \App\http\Request $request
     * @return string
     */
    public static function insertTestimony($request)
    {
        $postVars = $request->getPostVars();

        $obTestimony = new EntityTestimony;
        $obTestimony->nome = $postVars['nome'];
        $obTestimony->mensagem = $postVars['mensagem'];
        $obTestimony->cadastrar();


        return self::getTestimonies($request);
    }
}