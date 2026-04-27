<?php
namespace App\Controller\Notas;

use \App\Utils\View;
use \App\Utils\Alert;
use \App\Controller\Pages\Page;
use \App\Model\Entity\Ligacoes as EntityLigacoes;
use DateTime;

class NotasUra extends Page
{

    public static function getNotas($request)
    {
        $content = View::render('/notas/notas-ura/form', [
            'equipes' => self::getEquipes(),
            'status' => self::getStatus($request)
        ]);

        return parent::getPage('Notas URA > ToolsVGL', $content);
    }

    public static function getNotasTable($request)
    {
        $queryParams = $request->getQueryParams();
        $uri = http_build_query($queryParams);
        $content = View::render('/notas/notas-ura/table', [
            'cards' => self::getCards($request),
            'status' => self::getStatus($request),
            'itens' => ''/*self::getTableItens($request)*/ ,
            'URI' => $uri
        ]);

        return parent::getPage('Notas URA > ToolsVGL', $content);
    }

    public static function getTableItens($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $equipe = $queryParams['equipe'];
        $tipo = $queryParams['tipo'] ?? 'todos';
        $resultados = EntityLigacoes::getNotasByFilter($dataInicio, $dataFim, $equipe);
        $uri = str_replace("/table", "/delete", $_SERVER['REQUEST_URI']);

        $itens = '';
        while ($obNotas = $resultados->fetchObject(EntityLigacoes::class)) {
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
                $itens .= View::render('/notas/notas-ura/item', [
                    'id' => $obNotas->id,
                    'numero' => $obNotas->numero,
                    'data' => $data,
                    'nota' => $obNotas->nota,
                    'equipe' => $obNotas->fila,
                    'agente' => $obNotas->responsavel,
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
        $filas = $queryParams['filaLigacoes'] ?? [];

        if (!is_array($filas)) {
            $filas = [$filas];
        }

        $dados = EntityLigacoes::getCardsData($dataInicio, $dataFim, $filas);

        if ($dados->total <= 0) {
            $request->getRouter()->redirect('/notas-ura?status=nenhuma');
            exit;
        }

        $total = $dados->total;

        $status = [
            [
                'name' => 'Satisfatórios',
                'color' => 'green',
                'total' => $dados->promotores,
                'porcentagem' => number_format(($dados->promotores / $total) * 100, 2) . "%"
            ],
            [
                'name' => 'Neutros',
                'color' => 'lightblue',
                'total' => $dados->neutros,
                'porcentagem' => number_format(($dados->neutros / $total) * 100, 2) . "%"
            ],
            [
                'name' => 'Insatisfatórios',
                'color' => 'red',
                'total' => $dados->detratores,
                'porcentagem' => number_format(($dados->detratores / $total) * 100, 2) . "%"
            ],
            [
                'name' => 'Nota Média',
                'color' => 'darkblue',
                'total' => '',
                'porcentagem' => number_format($dados->media, 2)
            ],
            [
                'name' => 'CSAT',
                'color' => 'green',
                'total' => '',
                'porcentagem' => number_format(($dados->promotores / $total) * 100, 2) . "%"
            ],
        ];

        $content = '';

        foreach ($status as $card) {
            $content .= View::render('/notas/notas-ura/card', [
                'titulo' => $card['name'],
                'color' => $card['color'],
                'total' => $card['total'],
                'porcentagem' => $card['porcentagem'],
                'link' => '#'
            ]);
        }

        return $content;
    }
    private static function getEquipes()
    {
        $results = EntityLigacoes::getFilas();
        $itens = '';
        while ($obNotas = $results->fetchObject(EntityLigacoes::class)) {
            $itens .= View::render('/notas/notas-ura/option', [
                'equipe' => $obNotas->fila
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


        $obNotas = EntityLigacoes::getLigacoesById($id);

        if (!$obNotas instanceof EntityLigacoes) {
            $request->getRouter()->redirect('/notas-ura');
            exit;
        }

        $content = View::render('/notas/notas-ura/delete', [
            'protocolo' => $obNotas->numero,
            'equipe' => $obNotas->fila,
            'agente' => $obNotas->responsavel,
        ]);

        //Retorna a página
        return parent::getPage('Excluir Nota > ToolsVGL', $content);
    }

    public static function setDeleteNota($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];
        $uri = http_build_query($queryParams);


        $obNotas = EntityLigacoes::getLigacoesById($id);

        if (!$obNotas instanceof EntityLigacoes) {
            $request->getRouter()->redirect('/notas-ura');
            exit;
        }
        $obNotas->nota = null;
        $obNotas->atualizar();

        $request->getRouter()->redirect('/notas-ura/table?' . $uri . '&status=deleted');
        exit;
    }

    public static function setDeleteNotasByGroup($request)
    {
        $postVars = $request->getPostVars();
        $queryParams = $request->getQueryParams();
        $notas = $postVars['notas'] ?? [];
        $uri = http_build_query($queryParams);

        foreach ($notas as $nota) {
            $obNotas = EntityLigacoes::getLigacoesById($nota);

            if ($obNotas instanceof EntityLigacoes) {
                $obNotas->nota = null;
                $obNotas->atualizar();
            }

        }
        $request->getRouter()->redirect('/notas-ura/table?' . $uri . '&status=deleted');
        exit;
    }
}