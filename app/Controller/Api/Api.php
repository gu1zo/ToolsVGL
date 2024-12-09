<?php

namespace App\Controller\Api;

use App\http\Request;
use WilliamCosta\DatabaseManager\Pagination;

class Api
{
    /**
     * Método responsável por retornar os detalhes da API
     * @param Request $request
     * @return array
     */
    public static function getDetails($request)
    {
        return [
            'nome' => 'API - RetisVGL',
            'versao' => 'v1.0.0',
            'autor' => 'Guilherme Recalcatte Vogel',
            'email' => 'guilhermerecalcatte@gmail.com'
        ];
    }

    /**
     * Método responsável por retoranr os detalhes da paginação
     * @param Request $request
     * @param Pagination $obPagination
     * @return array
     */
    protected static function getPagination($request, $obPagination)
    {
        $queryParams = $request->getQueryParams();

        $pages = $obPagination->getPages();

        return [
            'paginaAtual' => isset($queryParams['page']) ? (int) $queryParams['page'] : 1,
            'quantidadePaginas' => !empty($pages) ? count($pages) : 1
        ];
    }
}