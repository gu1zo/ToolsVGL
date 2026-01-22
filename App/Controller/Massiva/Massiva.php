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
            'status' => self::getStatus($request),
            'data-inicio' => '',
            'data-fim' => '',
            'evento' => '',
            'regionais' => self::getRegionais($request),
            'tipos' => self::getTipos($request),
            'qtd' => '',
            'int6' => ''
        ]);

        return parent::getPage('Nova Massiva > ToolsVGL', $content);
    }

    public static function setNovaMassiva($request)
    {
        $postVars = $request->getPostVars();


        $obMassiva = new EntityMassivas;
        $dataFim = $postVars['dataFim'] == '' ? null : $postVars['dataFim'];

        $obMassiva->evento = $postVars['evento'];
        $obMassiva->dataInicio = $postVars['dataInicio'];
        $obMassiva->dataFim = $dataFim;
        $obMassiva->qtd = $postVars['qtd'];
        $obMassiva->int6 = $postVars['int6'];
        $obMassiva->regional = $postVars['regional'];
        $obMassiva->tipo = $postVars['tipo'];
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
            $dataFim = $obMassivas->dataFim == null ? '-' : $obMassivas->dataFim;
            $itens .= View::render('/massivas/item', [
                'id' => $obMassivas->id,
                'evento' => $obMassivas->evento,
                'dataInicio' => $obMassivas->dataInicio,
                'dataFim' => $dataFim
            ]);
        }
        return $itens;
    }

    public static function getEditMassiva($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obMassiva = EntityMassivas::getMassivaById($id);

        if (!$obMassiva instanceof EntityMassivas) {
            $request->getRouter()->redirect('/massivas?status=undefined');
            exit;
        }

        $dataFim = $obMassiva->dataFim == null ? '' : $obMassiva->dataFim;

        $content = View::render('massivas/form', [
            'status' => self::getStatus($request),
            'data-inicio' => $obMassiva->dataInicio,
            'data-fim' => $dataFim,
            'evento' => $obMassiva->evento,
            'qtd' => $obMassiva->qtd,
            'int6' => $obMassiva->int6,
            'regionais' => self::getRegionais($request),
            'tipos' => self::getTipos($request)
        ]);

        return parent::getPage('Nova Massiva > ToolsVGL', $content);
    }

    public static function setEditMassiva($request)
    {
        $postVars = $request->getPostVars();
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obMassiva = EntityMassivas::getMassivaById($id);

        if (!$obMassiva instanceof EntityMassivas) {
            $request->getRouter()->redirect('/massivas?status=undefined');
            exit;
        }

        $dataFim = $postVars['dataFim'] == '' ? null : $postVars['dataFim'];

        $obMassiva->evento = $postVars['evento'];
        $obMassiva->dataInicio = $postVars['dataInicio'];
        $obMassiva->dataFim = $dataFim;
        $obMassiva->qtd = $postVars['qtd'];
        $obMassiva->int6 = $postVars['int6'];
        $obMassiva->regional = $postVars['regional'];
        $obMassiva->tipo = $postVars['tipo'];
        $obMassiva->atualizar();
        $request->getRouter()->redirect('/massivas?status=updated');
        exit;
    }

    public static function getDeleteMassiva($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obMassiva = EntityMassivas::getMassivaById($id);

        if (!$obMassiva instanceof EntityMassivas) {
            $request->getRouter()->redirect('/massivas?status=undefined');
            exit;
        }

        $content = View::render('massivas/delete', [
        ]);

        return parent::getPage('Nova Massiva > ToolsVGL', $content);

    }

    public static function setDeleteMassiva($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obMassiva = EntityMassivas::getMassivaById($id);

        if (!$obMassiva instanceof EntityMassivas) {
            $request->getRouter()->redirect('/massivas?status=undefined');
            exit;
        }

        $obMassiva->excluir();
        $request->getRouter()->redirect('/massivas?status=deleted');
        exit;
    }


    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'created':
                return Alert::getSuccess('Evento cadastrado com sucesso!');
            case 'updated':
                return Alert::getSuccess('Evento atualizado com sucesso!');
            case 'deleted':
                return Alert::getSuccess('Evento excluído com sucesso!');
            case 'undefined':
                return Alert::getError('Evento não localizado!');
        }
        return '';
    }

    private static function getRegionais($request)
    {
        $queryParams = $request->getQueryParams();
        $id = isset($queryParams['id']) ? $queryParams['id'] : null;
        $itens = "";
        $regionais = [
            'CDR',
            'VII',
            'UVA',
            'RSL',
            'IRI',
            'CNI',
            'ITH',
            'CBS',
            'CTA',
            'PYE',
            'JBA',
            'CCO',
            'MFA'
        ];



        $obMassiva = EntityMassivas::getMassivaById($id);

        if (!$obMassiva instanceof EntityMassivas) {
            $regionalMassiva = "";
        } else {
            $regionalMassiva = $obMassiva->regional;
        }
        foreach ($regionais as $regional) {
            if ($regionalMassiva == $regional) {
                $itens .= View::render('massivas/option', [
                    'item' => $regional,
                    'status' => 'selected'
                ]);
            } else {
                $itens .= View::render('massivas/option', [
                    'item' => $regional,
                    'status' => ''
                ]);
            }
        }

        return $itens;

    }

    private static function getTipos($request)
    {
        $queryParams = $request->getQueryParams();
        $id = isset($queryParams['id']) ? $queryParams['id'] : null;
        $itens = "";
        $tipos = [
            'Rompimento',
            'Falha na OLT',
            'Falha no backbone'
        ];

        $obMassiva = EntityMassivas::getMassivaById($id);
        if (!$obMassiva instanceof EntityMassivas) {
            $tipoMassiva = "";
        } else {
            $tipoMassiva = $obMassiva->tipo;
        }
        foreach ($tipos as $tipo) {
            if ($tipoMassiva == $tipo) {
                $itens .= View::render('massivas/option', [
                    'item' => $tipo,
                    'status' => 'selected'
                ]);
            }
            $itens .= View::render('massivas/option', [
                'item' => $tipo,
                'status' => ''
            ]);
        }

        return $itens;

    }
}