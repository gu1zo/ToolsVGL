<?php

namespace App\Controller\Api;

use App\http\Request;
use App\Model\Entity\Testimony as EntityTestimony;
use WilliamCosta\DatabaseManager\Pagination;

class Testimony extends Api
{
    /**
     * Método responsável por retornar os detalhes da API
     * @param mixed $request
     * @return array
     */
    public static function getTestimonies($request)
    {
        return [
            'depoimentos' => self::getTestimonyItems($request, $obPagination),
            'paginacao' => parent::getPagination($request, $obPagination)
        ];
    }

    private static function getTestimonyItems($request, &$obPagination)
    {
        $itens = [];

        //TOTAL DE REGISTROS
        $quantidadetotal = EntityTestimony::getTestimonies(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        //PAGINA ATUAL
        $queryParams = $request->getQueryParams();
        $paginaAtual = $queryParams['page'] ?? 1;

        $obPagination = new Pagination($quantidadetotal, $paginaAtual, 3);

        $results = EntityTestimony::getTestimonies(null, 'id DESC', $obPagination->getLimit());

        while ($obTestimony = $results->fetchObject(EntityTestimony::class)) {
            $itens[] = [
                'id' => (int) $obTestimony->id,
                'nome' => $obTestimony->nome,
                'mensagem' => $obTestimony->mensagem,
                'data' => $obTestimony->data
            ];
        }
        return $itens;
    }

    /**
     * Método responsável por retornar os detalhes de um depoimento
     * @param Request $request
     * @param int $id
     * @return array
     */
    public static function getTestimony($request, $id)
    {
        if (!is_numeric($id)) {
            throw new \Exception("O id '" . $id . "' não é válido");
        }

        $obTestimony = EntityTestimony::getTestimonyById($id);
        if (!$obTestimony instanceof EntityTestimony) {
            throw new \Exception("O depoimento '" . $id . "' não foi encontrado", 404);
        }

        return [
            'id' => (int) $obTestimony->id,
            'nome' => $obTestimony->nome,
            'mensagem' => $obTestimony->mensagem,
            'data' => $obTestimony->data
        ];
    }
}