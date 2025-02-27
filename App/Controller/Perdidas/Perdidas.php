<?php
namespace App\Controller\Perdidas;

use \App\Controller\Pages\Page;
use \App\Utils\View;
use \App\Utils\Alert;
use \App\Model\Entity\Perdidas as EntityPerdidas;
class Perdidas extends Page
{
    public static function getNovaPerdida($request)
    {
        $content = View::render('perdidas/form', [
            'status' => self::getStatus($request)
        ]);

        return parent::getPage('Nova Perdida > ToolsVGL', $content);
    }

    public static function setNovaPerdida($request)
    {
        $postVars = $request->getPostVars();

        $obPerdidas = new EntityPerdidas;

        $obPerdidas->motivo = $postVars['motivo'];
        $obPerdidas->espera = $postVars['espera'];
        $obPerdidas->posicao = $postVars['posicao'];
        $obPerdidas->originador = $postVars['originador'];
        $obPerdidas->data = $postVars['data'];
        $obPerdidas->dnis = $postVars['dnis'];

        $obPerdidas->cadastrar();
        $request->getRouter()->redirect('/perdidas?status=created');
        exit;
    }

    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'created':
                return Alert::getSuccess('Perdida cadastrada com sucesso!');
                break;
        }
        return '';
    }

    public static function getPerdidas($request)
    {
        //Conteúdo da home
        $content = View::render('perdidas/table/table', [
            'itens' => self::getPerdidasItems($request),
            'status' => self::getStatus($request)
        ]);
        //Retorna a página
        return parent::getPage('Perdidas > ToolsVGL', $content);
    }
    private static function getPerdidasItems($request)
    {
        $itens = '';

        //TOTAL DE REGISTROS
        $quantidadetotal = EntityPerdidas::getPerdidas(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        if ($quantidadetotal <= 0) {
            $itens = '';
        }

        $results = EntityPerdidas::getPerdidas(null, 'id DESC');

        while ($obPerdidas = $results->fetchObject(EntityPerdidas::class)) {
            $itens .= View::render('/perdidas/table/item', [
                'id' => $obPerdidas->id,
                'motivo' => $obPerdidas->motivo,
                'espera' => $obPerdidas->espera,
                'posicao' => $obPerdidas->posicao,
                'originador' => $obPerdidas->originador,
                'data' => $obPerdidas->data,
                'dnis' => $obPerdidas->dnis,
            ]);
        }
        return $itens;
    }
}