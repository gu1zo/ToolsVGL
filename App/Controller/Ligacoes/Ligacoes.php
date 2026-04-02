<?php
namespace App\Controller\Ligacoes;

use App\Controller\Pages\Page;
use \App\Utils\View;
use \App\Utils\Alert;
use \App\Model\Entity\Ligacoes as EntityLigacoes;
use DateTime;
use Dom\EntityReference;

class Ligacoes extends Page
{
    public static function renderDashboardChamadas($request)
    {
        $queryParams = $request->getQueryParams();
        $queue = $queryParams['queue'];
        return View::render('ligacoes/dashboard', [
            'queue' => $queue
        ]);
    }

    public static function renderDashboardAgentes($request)
    {
        $queryParams = $request->getQueryParams();
        $queue = $queryParams['queue'];
        return View::render('ligacoes/dashboard-agentes', [
            'queue' => $queue
        ]);
    }

    public static function getLigacoes($request)
    {
        $content = View::render('/ligacoes/form', [
            'filas' => self::getFilas(),
            'status' => self::getStatus($request)
        ]);

        return parent::getPage('Estatisticas Ligação > ToolsVGL', $content);
    }

    private static function getFilas()
    {
        $results = EntityLigacoes::getFilas();
        $itens = '';

        while ($obLigacoes = $results->fetchObject(EntityLigacoes::class)) {
            $itens .= View::render('/ligacoes/option', [
                'fila' => $obLigacoes->fila
            ]);
        }
        return $itens;
    }

    public static function getLigacoesTable($request)
    {
        $queryParams = $request->getQueryParams();
        $uri = http_build_query($queryParams);
        $content = View::render('/ligacoes/table', [
            'cards' => self::getCards($request),
            'status' => self::getStatus($request),
            'itens' => self::getTableItens($request),
            'URI' => $uri
        ]);

        return parent::getPage('Ligações > ToolsVGL', $content);
    }

    private static function getCards($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $fila = $queryParams['filaLigacoes'];
        $uri = $_SERVER['REQUEST_URI'];

        $resultados = EntityLigacoes::getLigacoesByFilter($dataInicio, $dataFim, $fila);

        // Totais
        $total = 0;
        $atendidas = 0;
        $perdidas = 0;

        // Somatórios
        $totalTempoFila = 0;
        $totalTempoAtendimento = 0;

        // Meta
        $dentroMeta = 0;

        while ($ligacao = $resultados->fetchObject(EntityLigacoes::class)) {

            $tempoFila = (int) $ligacao->tempo_fila;
            $tempoAtendimento = (int) $ligacao->tempo_atendimento;
            $status = $ligacao->status;

            $total++;
            $totalTempoFila += $tempoFila;

            // Dentro da meta (<= 45s)
            if ($tempoFila <= 45) {
                $dentroMeta++;
            }

            // Ajusta aqui conforme teu status real
            if ($status === 'answered') {
                $atendidas++;
                $totalTempoAtendimento += $tempoAtendimento;
            } else {
                $perdidas++;
            }
        }

        if ($total <= 0) {
            $request->getRouter()->redirect('/ligacoes?status=nenhuma');
            exit;
        }

        // Cálculos
        $tme = $total > 0 ? $totalTempoFila / $total : 0;
        $tma = $atendidas > 0 ? $totalTempoAtendimento / $atendidas : 0;
        $percentMeta = ($dentroMeta / $total) * 100;

        // Formata segundos → HH:MM:SS
        $formatTime = function ($seconds) {
            return gmdate("H:i:s", (int) $seconds);
        };

        $cards = [
            [
                'name' => 'TME',
                'color' => 'darkblue',
                'total' => '',
                'porcentagem' => $formatTime($tme),
                'link' => $uri
            ],
            [
                'name' => 'TMA',
                'color' => 'darkblue',
                'total' => '',
                'porcentagem' => $formatTime($tma),
                'link' => $uri
            ],
            [
                'name' => '% Meta (45s)',
                'color' => 'green',
                'total' => '',
                'porcentagem' => number_format($percentMeta, 2) . '%',
                'link' => $uri
            ],
            [
                'name' => 'Atendidas',
                'color' => 'green',
                'total' => $atendidas,
                'porcentagem' => number_format(($atendidas / $total) * 100, 2) . '%',
                'link' => $uri . '&tipo=atendidas'
            ],
            [
                'name' => 'Perdidas',
                'color' => 'red',
                'total' => $perdidas,
                'porcentagem' => number_format(($perdidas / $total) * 100, 2) . '%',
                'link' => $uri . '&tipo=perdidas'
            ],
            [
                'name' => 'Total',
                'color' => 'gray',
                'total' => $total,
                'porcentagem' => '<br>',
                'link' => $uri
            ],
        ];

        $content = '';

        foreach ($cards as $card) {
            $content .= View::render('/ligacoes/card', [
                'titulo' => $card['name'],
                'color' => $card['color'],
                'total' => $card['total'],
                'porcentagem' => $card['porcentagem'],
                'link' => $card['link']
            ]);
        }

        return $content;
    }

    public static function getTableItens($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $fila = $queryParams['filaLigacoes'];
        $resultados = EntityLigacoes::getLigacoesByFilter($dataInicio, $dataFim, $fila);
        $uri = str_replace("/table", "/delete", $_SERVER['REQUEST_URI']);

        // Função para formatar tempo
        $formatTime = function ($seconds) {
            $seconds = (int) $seconds;

            if ($seconds >= 3600) {
                return gmdate("H:i:s", $seconds); // mais de 1h
            }

            return gmdate("i:s", $seconds); // padrão mm:ss
        };

        $itens = '';

        while ($obLigacoes = $resultados->fetchObject(EntityLigacoes::class)) {

            $data = (new DateTime($obLigacoes->data))->format('d/m/Y H:i');

            $status = ($obLigacoes->status == 'answered') ? 'Atendida' : 'Perdida';

            $itens .= View::render('/ligacoes/item', [
                'id' => $obLigacoes->id,
                'data' => $data,
                'tempo_fila' => $formatTime($obLigacoes->tempo_fila),
                'tempo_atendimento' => $formatTime($obLigacoes->tempo_atendimento),
                'agente' => $obLigacoes->responsavel,
                'status' => $status,
                'numero' => $obLigacoes->numero,
                'URI' => $uri
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
                return Alert::getError('Nenhuma ligação cadastrada no período!');
            case 'no-permission':
                return Alert::getError('Você não tem permissão');
        }
        return '';
    }

}