<?php

namespace App\Controller\Relatorios;

use App\Model\Entity\Notas as EntityNotas;
use App\Model\Entity\NotasResolutividade as EntityNotasResolutividade;
use App\Model\Entity\Ligacoes as EntityLigacoes;
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
    public static function getNotasUraCSV($request)
    {
        date_default_timezone_set('America/Sao_Paulo');

        $queryParams = $request->getQueryParams();

        $dataInicio = $queryParams['data_inicial'] ?? date('Y-m-d');
        $dataFim = $queryParams['data_final'] ?? date('Y-m-d');

        // ✅ PADRÃO IGUAL AO DATATABLE
        $filas = $queryParams['filaLigacoes'] ?? [];

        if (!is_array($filas)) {
            $filas = [$filas];
        }

        // 🔒 sanitiza
        $ids = array_filter(
            array_map('intval', $filas),
            fn($v) => $v > 0
        );

        $tipo = $queryParams['tipo'] ?? 'todos';

        // ✅ usa o mesmo padrão de filtro
        $resultados = EntityLigacoes::getNotasByFilter($dataInicio, $dataFim, $ids);

        $data = [
            ['numero', 'data', 'nota', 'fila', 'agente']
        ];

        while ($obNotas = $resultados->fetchObject(EntityLigacoes::class)) {

            $seguir = false;

            switch ($tipo) {
                case 'promotores':
                    $seguir = $obNotas->nota >= 4;
                    break;

                case 'neutros':
                    $seguir = $obNotas->nota == 3;
                    break;

                case 'detratores':
                    $seguir = $obNotas->nota < 3;
                    break;

                default:
                    $seguir = true;
            }

            if ($seguir) {
                $data[] = [
                    $obNotas->numero,
                    (new \DateTime($obNotas->data))->format('d/m/Y H:i'),
                    $obNotas->nota,
                    $obNotas->fila,
                    $obNotas->responsavel,
                ];
            }
        }

        // 📄 nome do arquivo
        $filename = "Relatorio_Notas_" . date('d-m-Y_H-i') . ".csv";

        // 📦 headers
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // BOM UTF-8 (excel-friendly)
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        foreach ($data as $row) {
            $utf8Row = array_map(
                fn($val) => mb_convert_encoding((string) $val, 'UTF-8', 'auto'),
                $row
            );

            fputcsv($output, $utf8Row, ';');
        }

        fclose($output);
        exit;
    }
    public static function getMediaNotasPorAgenteUraCSV($request)
    {
        $queryParams = $request->getQueryParams();

        $dataInicio = $queryParams['data_inicial'] ?? date('Y-m-d');
        $dataFim = $queryParams['data_final'] ?? date('Y-m-d');

        // ✅ PADRÃO IGUAL AO RESTO DO SISTEMA
        $filas = $queryParams['filaLigacoes'] ?? [];

        if (!is_array($filas)) {
            $filas = [$filas];
        }

        // 🔒 sanitiza
        $ids = array_filter(
            array_map('intval', $filas),
            fn($v) => $v > 0
        );

        // 📅 WHERE BASE
        $where = 'data BETWEEN "' . $dataInicio . ' 00:00:00"
        AND "' . $dataFim . ' 23:59:59"
        AND nota IS NOT NULL
        AND responsavel IS NOT NULL
        AND id_queue > 0';

        // 🎯 filtro por filas (igual DataTable)
        if (!empty($ids)) {
            $where .= ' AND id_queue IN (' . implode(',', $ids) . ')';
        } else {
            // 🔥 evita retornar tudo sem filtro
            $where .= ' AND 1=0';
        }

        // 📊 agregações
        $fields = '
        responsavel,
        COUNT(*) as quantidade_avaliacoes,
        ROUND(AVG(nota),2) as media_notas,
        SUM(CASE WHEN nota >= 4 THEN 1 ELSE 0 END) as promotores,
        SUM(CASE WHEN nota = 3 THEN 1 ELSE 0 END) as neutros,
        SUM(CASE WHEN nota <= 2 THEN 1 ELSE 0 END) as detratores,
        ROUND(
            (SUM(CASE WHEN nota >= 4 THEN 1 ELSE 0 END) / COUNT(*)) * 100,
        2) as csat
    ';

        $resultados = EntityLigacoes::getLigacoes(
            $where,
            'csat DESC',
            null,
            $fields,
            'responsavel'
        );

        // 📄 nome do arquivo
        $filename = 'relatorio_notas_por_agente_' . date('d-m-Y_H-i') . '.csv';

        // 📦 headers
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // BOM UTF-8 (Excel)
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // 🧾 cabeçalho
        fputcsv($output, [
            'Agente',
            'Qtd Avaliações',
            'Média Notas',
            'Promotores',
            'Neutros',
            'Detratores',
            'CSAT (%)'
        ], ';');

        while ($row = $resultados->fetchObject()) {
            fputcsv($output, [
                $row->responsavel,
                (int) $row->quantidade_avaliacoes,
                (float) $row->media_notas,
                (int) $row->promotores,
                (int) $row->neutros,
                (int) $row->detratores,
                (float) $row->csat
            ], ';');
        }

        fclose($output);
        exit;
    }
    public static function getMediaNotasPorAgenteCSV($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'] ?? null;
        $dataFim = $queryParams['data_final'] ?? null;
        $equipe = $queryParams['equipe'] ?? null;

        $periodo = 'data BETWEEN "' . $dataInicio . ' 00:00:00" 
        AND "' . $dataFim . ' 23:59:59"';

        $where = $periodo;

        if ($equipe != 'todas') {
            if ($equipe == 'ggnet' || $equipe == 'alt') {
                $where .= ' AND canal = "' . $equipe . '"';
            } else {
                $where .= ' AND equipe = "' . $equipe . '"';
            }
        }

        $fields = '
        agente,
        COUNT(*) as quantidade_avaliacoes,
        ROUND(AVG(nota),2) as media_notas,
        SUM(CASE WHEN nota >= 4 THEN 1 ELSE 0 END) as promotores,
        SUM(CASE WHEN nota = 3 THEN 1 ELSE 0 END) as neutros,
        SUM(CASE WHEN nota <= 2 THEN 1 ELSE 0 END) as detratores,
        ROUND(
            (SUM(CASE WHEN nota >= 4 THEN 1 ELSE 0 END) / COUNT(*)) * 100,
        2) as csat
    ';

        $order = 'csat DESC';
        $group = 'agente';

        $resultados = EntityNotas::getNotas($where, $order, null, $fields, $group);

        // 🔽 Headers para download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=relatorio_notas_por_agente.csv');

        $output = fopen('php://output', 'w');

        // 🔽 Cabeçalho do CSV
        fputcsv($output, [
            'Agente',
            'Qtd Avaliações',
            'Média Notas',
            'Promotores',
            'Neutros',
            'Detratores',
            'CSAT (%)'
        ], ';');

        while ($row = $resultados->fetchObject()) {
            fputcsv($output, [
                $row->agente,
                (int) $row->quantidade_avaliacoes,
                (float) $row->media_notas,
                (int) $row->promotores,
                (int) $row->neutros,
                (int) $row->detratores,
                (float) $row->csat
            ], ';');
        }

        fclose($output);
        exit;
    }

    public static function getGraficosUra($request)
    {
        $queryParams = $request->getQueryParams();
        $uri = http_build_query($queryParams);

        $content = View::render('graficos/graficosUra/graficos', [
            'itens' => self::getGraficosUraItem($request),
            'URI' => $uri
        ]);

        return self::getPage('Gráficos > ToolsVGL', $content);
    }

    private static function getGraficosUraItem($request)
    {
        $content = View::render('graficos/graficosUra/graficos-item', [
        ]);

        return $content;
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