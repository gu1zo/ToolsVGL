<?php
namespace App\Controller\Massiva;

use \App\Utils\View;
use \App\Utils\Alert;
use \App\Controller\Pages\Page;
use \App\Model\Entity\Massiva as EntityMassivas;
use DateTime;

class Massiva extends Page
{
    public static function getNovaMassiva($request)
    {
        $content = View::render('massivas/form', [
            'status' => self::getStatus($request)
        ]);

        return parent::getPage('Nova Massiva > ToolsVGL', $content);
    }

    public static function setNovaMassiva($request)
    {
        $postVars = $request->getPostVars();

        $obMassiva = new EntityMassivas;

        $obMassiva->evento = $postVars['evento'];
        $obMassiva->dataInicio = $postVars['dataInicio'];
        $obMassiva->dataFim = $postVars['dataFim'];
        $obMassiva->cadastrar();
        $request->getRouter()->redirect('/massivas?status=created');
        exit;
    }

    public static function getMassivas($request)
    {
        //Conteúdo da home
        $content = View::render('massivas/table', [
            'itens' => self::getMassivasItens($request),
            'status' => self::getStatus($request)
        ]);
        //Retorna a página
        return parent::getPage('Massivas > ToolsVGL', $content);
    }

    private static function getMassivasItens($request)
    {
        $itens = '';

        //TOTAL DE REGISTROS
        $quantidadetotal = EntityMassivas::getMassivas(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        if ($quantidadetotal <= 0) {
            $itens = '';
        }

        $results = EntityMassivas::getMassivas(null, 'id ASC');

        while ($obMassivas = $results->fetchObject(EntityMassivas::class)) {
            $itens .= View::render('/massivas/item', [
                'id' => $obMassivas->id,
                'evento' => $obMassivas->evento,
                'dataInicio' => $obMassivas->dataInicio,
                'dataFim' => $obMassivas->dataFim,
            ]);
        }
        return $itens;
    }


    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'created':
                return Alert::getSuccess('Massiva cadastrada com sucesso!');
        }
        return '';
    }
}