<?php
namespace App\Controller\OrdensServico;

use \App\Utils\View;
use \App\Utils\Alert;
use \App\Controller\Pages\Page;
use \App\Model\Entity\Tecnicos as EntityTecnicos;
use \App\Model\Entity\OrdensServico as EntityOrdensServico;
use \App\Model\Entity\EquipamentosOrdensServico as EntityEquipamentosOrdensServico;
use \App\Model\Entity\ImagensOrdensServico as EntityImagensOrdensServico;
use DateTime;

class OrdensServico extends Page
{
    public static function getOs($request)
    {
        $content = View::render('/ordem-servico/form', [
            'status' => self::getStatus($request)
        ]);

        return parent::getPage('Notas > ToolsVGL', $content);
    }
    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'nenhuma':
                return Alert::getError('Nenhuma Ordem de Serviço encontrada com os filtros!');
            case 'no-permission':
                return Alert::getError('Você não tem permissão');
        }
        return '';
    }

    public static function getOsTable($request)
    {
        $queryParams = $request->getQueryParams();
        $uri = http_build_query($queryParams);
        $content = View::render('/ordem-servico/table', [
            'status' => self::getStatus($request),
            'itens' => self::getTableItens($request),
            'URI' => $uri
        ]);

        return parent::getPage('Notas > ToolsVGL', $content);
    }


    public static function getTableItens($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $tecnico = $queryParams['tecnico'];
        $resultados = EntityOrdensServico::getOsByFilter($dataInicio, $dataFim, $tecnico);
        $uri = str_replace("/table", "/delete", $_SERVER['REQUEST_URI']);

        $itens = '';
        while ($obOrdens = $resultados->fetchObject(EntityOrdensServico::class)) {
            $data = (new DateTime($obOrdens->data))->format('d/m/Y H:i');


            $imagens = EntityImagensOrdensServico::getTotalImagesByIdOs($obOrdens->id) > 0 ? '✔️' : '❌';
            $equipamentos = EntityEquipamentosOrdensServico::getTotalEquipimanetosByIdOs($obOrdens->id) > 0 ? '✔️' : '❌';

            $confirmacao = $obOrdens->confirmacao == 0 ? '❌' : '✔️';


            $itens .= View::render('/ordem-servico/item', [
                'id' => $obOrdens->id,
                'numero' => $obOrdens->numero,
                'data' => $data,
                'nome_tecnico' => $obOrdens->nome_tecnico,
                'cliente' => $obOrdens->cliente,
                'tipo' => $obOrdens->tipo,
                'imagens' => $imagens,
                'equipamentos' => $equipamentos,
                'confirmacao' => $confirmacao,
                'URI' => $uri
            ]);
        }
        return $itens;
    }
    public static function getOsDetails($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obOs = EntityOrdensServico::getOrdemServicoById($id);
        if (!$obOs instanceof EntityOrdensServico) {
            $request->getRouter()->redirect('/os/table');
            exit;
        }

    }

}