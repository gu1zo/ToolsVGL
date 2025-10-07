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

        $dataInicio = (new DateTime($postVars['dataInicio']))->format('d/m/Y H:i');
        $dataFim = (new DateTime($postVars['dataFim']))->format('d/m/Y H:i');

        $obMassiva = new EntityMassivas;

        $obMassiva->evento = $postVars['evento'];
        $obMassiva->dataInicio = $dataInicio;
        $obMassiva->dataFim = $dataFim;
        $obMassiva->cadastrar();
        $request->getRouter()->redirect('/massiva?status=created');
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
        return parent::getPage('Perdidas > ToolsVGL', $content);
    }

    private static function getMassivasItens($request)
    {
        $itens = '';

        //TOTAL DE REGISTROS
        $quantidadetotal = EntityMassivas::getMassivas(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        if ($quantidadetotal <= 0) {
            $itens = '';
        }

        $results = EntityMassivas::getMassivas(null, 'id DESC');

        while ($obMassivas = $results->fetchObject(EntityMassivas::class)) {
            $itens .= View::render('/massivas/table/item', [
                'id' => $obMassivas->id,
                'evento' => $obMassivas->motivo,
                'dataInicio' => $obMassivas->espera,
                'dataFim' => $obMassivas->posicao,
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