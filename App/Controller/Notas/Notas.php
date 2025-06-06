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
        $content = View::render('notas/table', [
            'cards' => self::getCards($request),
            'itens' => self::getTableItens($request)
        ]);

        return parent::getPage('Notas > ToolsVGL', $content);
    }

    public static function getTableItens($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $canal = $queryParams['canal'];
        $equipe = $queryParams['equipe'];
        $resultados = EntityNotas::getNotasByFilter($dataInicio, $dataFim, $canal, $equipe);


        $itens = '';
        while ($obNotas = $resultados->fetchObject(EntityNotas::class)) {
            $data = (new DateTime($obNotas->data))->format('d/m/Y H:i');
            $itens .= View::render('notas/item', [
                'id' => $obNotas->id,
                'protocolo' => $obNotas->protocolo,
                'data' => $data,
                'nota' => $obNotas->nota,
                'equipe' => $obNotas->equipe,
                'mensagem' => $obNotas->mensagem,
                'agente' => $obNotas->agente,
            ]);
        }

        return $itens;
    }

    private static function getCards($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $canal = $queryParams['canal'];
        $equipe = $queryParams['equipe'];
        $resultados = EntityNotas::getNotasByFilter($dataInicio, $dataFim, $canal, $equipe);
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
                'porcentagem' => number_format(($promotores / $total) * 100, 2) . "%"
            ],
            [
                'name' => 'Neutros',
                'color' => 'lightblue',
                'total' => $neutros,
                'porcentagem' => number_format(($neutros / $total) * 100, 2) . "%"
            ],
            [
                'name' => 'Detratores',
                'color' => 'red',
                'total' => $detratores,
                'porcentagem' => number_format(($detratores / $total) * 100, 2) . "%"
            ],
            [
                'name' => 'Nota Média',
                'color' => 'darkblue',
                'total' => '',
                'porcentagem' => number_format(($totalNotas / $total), 2)
            ],
            [
                'name' => 'CSAT',
                'color' => 'green',
                'total' => '',
                'porcentagem' => number_format(($promotores / $total) * 100, 2) . "%"
            ],
        ];


        foreach ($status as $card) {
            $content .= View::render('/notas/card', [
                'titulo' => $card['name'],
                'color' => $card['color'],
                'total' => $card['total'],
                'porcentagem' => $card['porcentagem']
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
                break;
        }
        return '';
    }
}