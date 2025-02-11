<?php

namespace App\Controller\Api;

use App\http\Request;
use WilliamCosta\DatabaseManager\Pagination;
use App\Controller\Evento\Evento;
use App\Model\Entity\Evento as EntityEvento;
use App\Model\Entity\Joins as EntityJoins;
use App\Utils\DateManipulation;
use DateTime;
use IntlDateFormatter;

class Api
{
    /**
     * Método responsável por retornar os detalhes da API
     * @param Request $request
     * @return array
     */
    public static function getDetails($request)
    {
        return [
            'nome' => 'API - RetisVGL',
            'versao' => 'v1.0.0',
            'autor' => 'Guilherme Recalcatte Vogel',
            'email' => 'guilhermerecalcatte@gmail.com'
        ];
    }

    /**
     * Método responsável por retoranr os detalhes da paginação
     * @param Request $request
     * @param Pagination $obPagination
     * @return array
     */
    protected static function getPagination($request, $obPagination)
    {
        $queryParams = $request->getQueryParams();

        $pages = $obPagination->getPages();

        return [
            'paginaAtual' => isset($queryParams['page']) ? (int) $queryParams['page'] : 1,
            'quantidadePaginas' => !empty($pages) ? count($pages) : 1
        ];
    }

    public static function getMessage()
    {
        date_default_timezone_set('America/Sao_Paulo');

        // Obtém a data e hora atual
        $formatter = new IntlDateFormatter(
            'pt_BR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::SHORT,
            'America/Sao_Paulo',
            IntlDateFormatter::GREGORIAN,
            'EEEE, d \'de\' MMMM \'de\' yyyy HH:mm:ss'
        );

        $dataAtual = new DateTime();
        $dataFormatada = $formatter->format($dataAtual);



        $manutencao = '';
        $falhas = '';


        $resultados = EntityJoins::getEventoByStatus('em execucao');
        if ($resultados->rowCount() > 0) {
            while ($row = $resultados->fetchObject(EntityJoins::class)) {
                $dataInicio = new DateTime($row->dataInicio);
                $duracao = DateManipulation::getHourDiff($dataInicio, $dataAtual);
                $lastupdate = $duracao;
                $usuario = $row->usuario_nome;
                $info = $row->observacao;

                $obComentario = EntityJoins::getLastInfoById($row->id);
                if ($obComentario instanceof EntityJoins) {
                    $lastupdate = DateManipulation::gethourDiff($obComentario->data, $dataAtual);
                    $usuario = $obComentario->usuario_nome;
                    $info = $obComentario->comentario;
                }
                $string = '';
                if ($row->tipo == 'manutencao') {
                    $string .= "_EVENTO_ *" . $row->protocolo . " " . Evento::getPontosAcessoTable($row->id) . "*\n";
                    $string .= "_duration:_ " . $duracao . "\n";
                    $string .= "_last update:_ " . $lastupdate . " por " . $usuario . "\n";
                    $string .= "_last info:_ " . $info . "\n\n";
                    $manutencao .= $string;
                } else if ($row->tipo == 'evento') {
                    $string .= "_EVENTO_ *" . $row->protocolo . " " . Evento::getPontosAcessoTable($row->id) . "*\n";
                    $string .= "_duration:_ " . $duracao . "\n";
                    $string .= "_last update:_ " . $lastupdate . " por " . $usuario . "\n";
                    $string .= "_last info:_ " . $info . "\n\n";
                    $falhas .= $string;
                }
            }
        }

        if ($manutencao == '') {
            $manutencao = "_" . $dataFormatada . "_ \nNenhuma tarefa pendente para o projeto";
        }
        if ($falhas == '') {
            $falhas = "_" . $dataFormatada . "_ \nNenhuma tarefa pendente para o projeto\n";
        }

        $message = "*GESTÃO DE FALHAS GGNET*\n" . $falhas . "\n*MANUTENÇÕES PROGRAMADAS GGNET*\n" . $manutencao;
        return $message;
    }
}