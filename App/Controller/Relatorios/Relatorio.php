<?php

namespace App\Controller\Relatorios;

use App\Model\Entity\Notas as EntityNotas;
use App\Model\Entity\NotasResolutividade as EntityNotasResolutividade;
use App\Model\Entity\Massiva as EntityMassivas;
use App\Utils\View;
use App\Controller\Pages\Page;
use DateTime;

class Relatorio extends Page
{
    public static function getNotasCSV($request)
    {
        date_default_timezone_set('America/Sao_Paulo');
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $equipe = $queryParams['equipe'];
        $tipo = $queryParams['tipo'] ?? 'todos';

        $resultados = EntityNotas::getNotasByFilter($dataInicio, $dataFim, $equipe);
        $data = [
            ['protocolo', 'data', 'nota', 'equipe', 'mensagem', 'agente', 'canal']
        ];

        while ($obNotas = $resultados->fetchObject(EntityNotas::class)) {
            $seguir = false;

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
                $data[] = [
                    $obNotas->protocolo,
                    $obNotas->data,
                    $obNotas->nota,
                    $obNotas->equipe,
                    $obNotas->mensagem,
                    $obNotas->agente,
                    $obNotas->canal
                ];
            }
        }

        // Nome do arquivo CSV

        $filename = "Relatório Notas " . date('d-m-Y') . ".csv";

        // Definir cabeçalhos para download
        // Cabeçalhos
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Abrir saída
        $output = fopen('php://output', 'w');

        // BOM para UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Escrever dados com conversão explícita para UTF-8
        foreach ($data as $row) {
            $utf8Row = array_map(fn($val) => mb_convert_encoding($val, 'UTF-8', 'auto'), $row);
            fputcsv($output, $utf8Row, ';');
        }

        fclose($output);
        exit;

    }

    public static function getGraficos($request)
    {
        $queryParams = $request->getQueryParams();
        $uri = http_build_query($queryParams);

        $content = View::render('graficos/graficos/graficos', [
            'itens' => self::getGraficosItem($request),
            'URI' => $uri
        ]);

        return self::getPage('Gráficos > ToolsVGL', $content);
    }

    private static function getGraficosItem($request)
    {
        $content = View::render('graficos/graficos/graficos-item', [
        ]);

        return $content;
    }
    public static function getNotasResolutividadeCSV($request)
    {
        date_default_timezone_set('America/Sao_Paulo');
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $equipe = $queryParams['equipe'];
        $tipo = $queryParams['tipo'] ?? 'todos';

        $resultados = EntityNotasResolutividade::getNotasByFilter($dataInicio, $dataFim, $equipe);
        $data = [
            ['protocolo', 'data', 'nota', 'equipe', 'mensagem', 'agente', 'canal']
        ];

        while ($obNotas = $resultados->fetchObject(EntityNotasResolutividade::class)) {
            $seguir = false;

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
                $data[] = [
                    $obNotas->protocolo,
                    $obNotas->data,
                    $obNotas->nota,
                    $obNotas->equipe,
                    $obNotas->mensagem,
                    $obNotas->agente,
                    $obNotas->canal
                ];
            }
        }

        // Nome do arquivo CSV

        $filename = "Relatório Notas " . date('d-m-Y') . ".csv";

        // Definir cabeçalhos para download
        // Cabeçalhos
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Abrir saída
        $output = fopen('php://output', 'w');

        // BOM para UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Escrever dados com conversão explícita para UTF-8
        foreach ($data as $row) {
            $utf8Row = array_map(fn($val) => mb_convert_encoding($val, 'UTF-8', 'auto'), $row);
            fputcsv($output, $utf8Row, ';');
        }

        fclose($output);
        exit;

    }


    public static function getMassivasCSV($request)
    {
        date_default_timezone_set('America/Sao_Paulo');

        $resultados = EntityMassivas::getMassivas();
        $data = [
            ['evento', 'dataInicio', 'dataFim', 'int6', 'qtd', 'regional', ' tipo']
        ];


        while ($obMassivas = $resultados->fetchObject(EntityMassivas::class)) {
            $dataFim = $obMassivas->dataFim == null ? '' : $obMassivas->dataFim;
            $data[] = [
                $obMassivas->evento,
                $obMassivas->dataInicio,
                $dataFim,
                $obMassivas->int6,
                $obMassivas->qtd,
                $obMassivas->regional,
                $obMassivas->tipo,
            ];
        }

        // Nome do arquivo CSV

        $filename = "Relatório Massivas " . date('d-m-Y') . ".csv";

        // Definir cabeçalhos para download
        // Cabeçalhos
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Abrir saída
        $output = fopen('php://output', 'w');

        // BOM para UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Escrever dados com conversão explícita para UTF-8
        foreach ($data as $row) {
            $utf8Row = array_map(fn($val) => mb_convert_encoding($val, 'UTF-8', 'auto'), $row);
            fputcsv($output, $utf8Row, ';');
        }

        fclose($output);
        exit;

    }

    public static function getGraficosCordialidade($request)
    {
        $queryParams = $request->getQueryParams();
        $uri = http_build_query($queryParams);

        $content = View::render('graficos/graficosCordialidade/graficos', [
            'itens' => self::getGraficosCordialidadeItem($request),
            'URI' => $uri
        ]);

        return self::getPage('Gráficos > ToolsVGL', $content);
    }

    private static function getGraficosCordialidadeItem($request)
    {
        $content = View::render('graficos/graficosCordialidade/graficos-item', [
        ]);

        return $content;
    }

    public static function getGraficosOs($request)
    {
        $queryParams = $request->getQueryParams();
        $uri = http_build_query($queryParams);

        $content = View::render('graficos/ordemServico/graficos', [
            'itens' => self::getGraficosOsItens($request),
            'URI' => $uri
        ]);

        return self::getPage('Gráficos > ToolsVGL', $content);
    }

    public static function getGraficosMassiva($request)
    {
        $queryParams = $request->getQueryParams();
        $uri = http_build_query($queryParams);

        $content = View::render('graficos/graficosMassiva/graficos', [
            'itens' => self::getGraficosMassivaItens($request),
            'URI' => $uri
        ]);

        return self::getPage('Gráficos > ToolsVGL', $content);
    }
    private static function getGraficosMassivaItens($request)
    {
        $content = View::render('graficos/graficosMassiva/graficos-item', [
        ]);

        return $content;
    }


    private static function getGraficosOsItens($request)
    {
        $content = View::render('graficos/ordemServico/graficos-item', [
        ]);

        return $content;
    }
}