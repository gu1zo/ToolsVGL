<?php
namespace App\Controller\Notas;

use \App\Utils\View;
use \App\Utils\Alert;
use \App\Controller\Pages\Page;
use \App\Model\Entity\NotasResolutividade as EntityNotas;
use DateTime;

class NotasResolutividade extends Page
{

    public static function getNotas($request)
    {
        $content = View::render('/notas/notas-cordialidade/form', [
            'equipes' => self::getEquipes(),
            'status' => self::getStatus($request)
        ]);

        return parent::getPage('Notas > ToolsVGL', $content);
    }

    public static function getNotasTable($request)
    {
        $queryParams = $request->getQueryParams();
        $uri = http_build_query($queryParams);
        $content = View::render('/notas/notas-cordialidade/table', [
            'cards' => self::getCards($request),
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
        $equipe = $queryParams['equipe'];
        $tipo = $queryParams['tipo'] ?? 'todos';
        $resultados = EntityNotas::getNotasByFilter($dataInicio, $dataFim, $equipe);
        $uri = str_replace("/table", "/delete", $_SERVER['REQUEST_URI']);
        $nota = '-';
        $itens = '';

        while ($obNotas = $resultados->fetchObject(EntityNotas::class)) {
            $seguir = false;
            $data = (new DateTime($obNotas->data))->format('d/m/Y H:i');


            if ($obNotas->nota == 1) {
                $nota = "✔️";
            }
            if ($obNotas->nota == 0) {
                $nota = "❌";
            }

            switch ($tipo) {
                case 'resolvidos':
                    if ($obNotas->nota == 1) {
                        $nota = "Sim";
                        $seguir = true;
                    }
                    break;
                case 'nresolvidos':
                    if ($obNotas->nota == 0) {
                        $nota = "Não";
                        $seguir = true;
                    }
                    break;
                default:
                    $seguir = true;
            }
            if ($seguir) {
                $itens .= View::render('/notas/notas-cordialidade/item', [
                    'id' => $obNotas->id,
                    'protocolo' => $obNotas->protocolo,
                    'data' => $data,
                    'nota' => $nota,
                    'equipe' => $obNotas->equipe,
                    'agente' => $obNotas->agente,
                    'URI' => $uri
                ]);
            }

        }

        return $itens;
    }

    private static function getCards($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $equipe = $queryParams['equipe'];
        $uri = $_SERVER['REQUEST_URI'];

        $resultados = EntityNotas::getNotasByFilter($dataInicio, $dataFim, $equipe);
        $nresolvidos = 0;
        $resolvidos = 0;
        $total = 0;
        $totalNotas = 0;

        while ($obNotas = $resultados->fetchObject(EntityNotas::class)) {
            $nota = $obNotas->nota;
            if ($nota == 0) {
                $nresolvidos++;
            } else if ($nota == 1) {
                $resolvidos++;
            }
            $totalNotas += $nota;
            $total++;
        }

        if ($total <= 0) {
            $request->getRouter()->redirect('/notas-cordialidade?status=nenhuma');
            exit;
        }

        $content = '';
        $status = [
            [
                'name' => 'Resolvidos',
                'color' => 'green',
                'total' => $resolvidos,
                'porcentagem' => number_format(($resolvidos / $total) * 100, 2) . "%",
                'link' => $uri . '&tipo=resolvidos'
            ],
            [
                'name' => 'Não Resolvidos',
                'color' => 'red',
                'total' => $nresolvidos,
                'porcentagem' => number_format(($nresolvidos / $total) * 100, 2) . "%",
                'link' => $uri . '&tipo=nresolvidos'
            ],
            [
                'name' => 'Resolutividade',
                'color' => 'darkblue',
                'total' => '',
                'porcentagem' => number_format(($totalNotas / $total), 2) * 100 . "%",
                'link' => $uri . '&tipo=todos'
            ],
        ];


        foreach ($status as $card) {
            $content .= View::render('/notas/notas-cordialidade/card', [
                'titulo' => $card['name'],
                'color' => $card['color'],
                'total' => $card['total'],
                'porcentagem' => $card['porcentagem'],
                'link' => $card['link']
            ]);
        }

        return $content;
    }
    private static function getEquipes()
    {
        $results = EntityNotas::getEquipes();
        $itens = '';
        while ($obNotas = $results->fetchObject(EntityNotas::class)) {
            $itens .= View::render('/notas/notas-cordialidade/option', [
                'equipe' => $obNotas->equipe
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
            case 'nenhuma':
                return Alert::getError('Nenhuma nota cadastrada no período!');
            case 'no-permission':
                return Alert::getError('Você não tem permissão');
            case 'deleted':
                return Alert::getSuccess('Notas excluídas com sucesso');
        }
        return '';
    }

    public static function getDeleteNota($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];


        $obNotas = EntityNotas::getNotaById($id);

        if (!$obNotas instanceof EntityNotas) {
            $request->getRouter()->redirect('/notas-cordialidade');
            exit;
        }

        $content = View::render('/notas/notas-cordialidade/delete', [
            'protocolo' => $obNotas->protocolo,
            'equipe' => $obNotas->equipe,
            'agente' => $obNotas->agente,
        ]);

        //Retorna a página
        return parent::getPage('Excluir Nota > ToolsVGL', $content);
    }

    public static function setDeleteNota($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];
        $uri = http_build_query($queryParams);


        $obNotas = EntityNotas::getNotaById($id);

        if (!$obNotas instanceof EntityNotas) {
            $request->getRouter()->redirect('/notas-cordialidade');
            exit;
        }
        $obNotas->excluir();

        $request->getRouter()->redirect('/notas-cordialidade/table?' . $uri . '&status=deleted');
        exit;
    }

    public static function setDeleteNotasByGroup($request)
    {
        $postVars = $request->getPostVars();
        $queryParams = $request->getQueryParams();
        $notas = $postVars['notas'] ?? [];
        $uri = http_build_query($queryParams);

        foreach ($notas as $nota) {
            $obNotas = EntityNotas::getNotaById($nota);

            if ($obNotas instanceof EntityNotas) {
                $obNotas->excluir();
            }

        }
        $request->getRouter()->redirect('/notas-cordialidade/table?' . $uri . '&status=deleted');
        exit;
    }
}