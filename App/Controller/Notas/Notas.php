<?php
namespace App\Controller\Notas;

use \App\Utils\View;
use \App\Utils\Alert;
use \App\Controller\Pages\Page;
use \App\Model\Entity\Notas as EntityNotas;
use DateTime;

class Notas extends Page
{

    public static function getNotas($request)
    {
        $content = View::render('notas/form', [
            'equipes' => self::getEquipes(),
            'status' => self::getStatus($request)
        ]);

        return parent::getPage('Notas > ToolsVGL', $content);
    }

    public static function getNotasTable($request)
    {
        $queryParams = $request->getQueryParams();
        $uri = http_build_query($queryParams);
        $content = View::render('notas/table', [
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

        $itens = '';
        while ($obNotas = $resultados->fetchObject(EntityNotas::class)) {
            $seguir = false;
            $data = (new DateTime($obNotas->data))->format('d/m/Y H:i');

            switch ($tipo) {
                case 'promotores':
                    if ($obNotas->nota >= 4) {
                        $seguir = true;
                    }
                    break;
                case 'neutros':
                    if ($obNotas->nota == 3) {
                        $seguir = true;
                    }
                    break;
                case 'detratores':
                    if ($obNotas->nota < 3) {
                        $seguir = true;
                    }
                    break;
                default:
                    $seguir = true;
            }
            if ($seguir) {
                $itens .= View::render('notas/item', [
                    'id' => $obNotas->id,
                    'protocolo' => $obNotas->protocolo,
                    'data' => $data,
                    'nota' => $obNotas->nota,
                    'equipe' => $obNotas->equipe,
                    'mensagem' => $obNotas->mensagem,
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
        $detratores = 0;
        $promotores = 0;
        $neutros = 0;
        $total = 0;
        $totalNotas = 0;

        while ($obNotas = $resultados->fetchObject(EntityNotas::class)) {
            $nota = $obNotas->nota;
            if ($nota <= 2) {
                $detratores++;
            } else if ($nota == 3) {
                $neutros++;
            } else if ($nota > 3) {
                $promotores++;
            }
            $totalNotas += $nota;
            $total++;
        }

        if ($total <= 0) {
            $request->getRouter()->redirect('/notas?status=nenhuma');
            exit;
        }

        $content = '';
        $status = [
            [
                'name' => 'Promotores',
                'color' => 'green',
                'total' => $promotores,
                'porcentagem' => number_format(($promotores / $total) * 100, 2) . "%",
                'link' => $uri . '&tipo=promotores'
            ],
            [
                'name' => 'Neutros',
                'color' => 'lightblue',
                'total' => $neutros,
                'porcentagem' => number_format(($neutros / $total) * 100, 2) . "%",
                'link' => $uri . '&tipo=neutros'
            ],
            [
                'name' => 'Detratores',
                'color' => 'red',
                'total' => $detratores,
                'porcentagem' => number_format(($detratores / $total) * 100, 2) . "%",
                'link' => $uri . '&tipo=detratores'
            ],
            [
                'name' => 'Nota Média',
                'color' => 'darkblue',
                'total' => '',
                'porcentagem' => number_format(($totalNotas / $total), 2),
                'link' => $uri . '&tipo=todos'
            ],
            [
                'name' => 'CSAT',
                'color' => 'green',
                'total' => '',
                'porcentagem' => number_format(($promotores / $total) * 100, 2) . "%",
                'link' => $uri . '&tipo=todos'
            ],
        ];


        foreach ($status as $card) {
            $content .= View::render('/notas/card', [
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
            $itens .= View::render('notas/option', [
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
                return Alert::getSuccess('Nota excluída com sucesso');
        }
        return '';
    }

    public static function getDeleteNota($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];


        $obNotas = EntityNotas::getNotaById($id);

        if (!$obNotas instanceof EntityNotas) {
            $request->getRouter()->redirect('/notas');
            exit;
        }

        $content = View::render('notas/delete', [
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
            $request->getRouter()->redirect('/notas');
            exit;
        }
        $obNotas->excluir();

        $request->getRouter()->redirect('/notas/table?' . $uri . '&status=deleted');
        exit;
    }
}