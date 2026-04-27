<?php
namespace App\Controller\Ajax;

use App\Model\Entity\Ligacoes as EntityLigacoes;

class GraficosUra
{

    public static function getGraficoNotas($request)
    {
        $queryParams = $request->getQueryParams();

        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];

        $filas = $queryParams['filaLigacoes'] ?? [];
        if (!is_array($filas))
            $filas = [$filas];

        $ids = array_filter(array_map('intval', $filas), fn($v) => $v > 0);

        $resultados = EntityLigacoes::getNotasByFilter($dataInicio, $dataFim, $ids);

        $notas = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

        while ($row = $resultados->fetchObject(EntityLigacoes::class)) {
            $nota = (int) $row->nota;
            if (isset($notas[$nota])) {
                $notas[$nota]++;
            }
        }

        return json_encode([
            'labels' => ['1', '2', '3', '4', '5'],
            'values' => array_values($notas)
        ]);
    }

    public static function getGraficoCSAT($request)
    {
        $queryParams = $request->getQueryParams();

        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];

        $filas = $queryParams['filaLigacoes'] ?? [];
        if (!is_array($filas))
            $filas = [$filas];

        $ids = array_filter(array_map('intval', $filas), fn($v) => $v > 0);

        $resultados = EntityLigacoes::getNotasByFilter($dataInicio, $dataFim, $ids);

        $promotores = $neutros = $detratores = 0;

        while ($row = $resultados->fetchObject(EntityLigacoes::class)) {
            $nota = (int) $row->nota;

            if ($nota >= 4)
                $promotores++;
            elseif ($nota == 3)
                $neutros++;
            else
                $detratores++;
        }

        return json_encode([
            'labels' => ['Satisfatórios', 'Neutros', 'Insatisfatórios'],
            'values' => [$promotores, $neutros, $detratores]
        ]);
    }
    public static function getGraficoElogiosPorAgente($request)
    {
        $queryParams = $request->getQueryParams();

        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];

        $filas = $queryParams['filaLigacoes'] ?? [];
        if (!is_array($filas))
            $filas = [$filas];

        $ids = implode(',', array_map('intval', $filas));

        $where = 'data BETWEEN "' . $dataInicio . ' 00:00:00"
        AND "' . $dataFim . ' 23:59:59"
        AND nota >= 4
        AND responsavel IS NOT NULL
        AND id_queue IN (' . $ids . ')';

        $fields = 'responsavel, COUNT(*) as total';

        $resultados = EntityLigacoes::getLigacoes($where, 'total DESC', 10, $fields, 'responsavel');

        $labels = [];
        $values = [];

        while ($row = $resultados->fetchObject()) {
            $labels[] = $row->responsavel;
            $values[] = (int) $row->total;
        }

        return json_encode(compact('labels', 'values'));
    }

    public static function getGraficoCriticasPorAgente($request)
    {
        $queryParams = $request->getQueryParams();

        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];

        $filas = $queryParams['filaLigacoes'] ?? [];
        if (!is_array($filas))
            $filas = [$filas];

        $ids = implode(',', array_map('intval', $filas));

        $where = 'data BETWEEN "' . $dataInicio . ' 00:00:00"
        AND "' . $dataFim . ' 23:59:59"
        AND nota <= 2
        AND responsavel IS NOT NULL
        AND id_queue IN (' . $ids . ')';

        $fields = 'responsavel, COUNT(*) as total';

        $resultados = EntityLigacoes::getLigacoes($where, 'total DESC', 10, $fields, 'responsavel');

        $labels = [];
        $values = [];

        while ($row = $resultados->fetchObject()) {
            $labels[] = $row->responsavel;
            $values[] = (int) $row->total;
        }

        return json_encode(compact('labels', 'values'));
    }

    public static function getMediaNotasPorAgente($request)
    {
        $queryParams = $request->getQueryParams();

        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];

        $filas = $queryParams['filaLigacoes'] ?? [];
        if (!is_array($filas))
            $filas = [$filas];

        $ids = array_filter(array_map('intval', $filas), fn($v) => $v > 0);

        $where = 'data BETWEEN "' . $dataInicio . ' 00:00:00"
        AND "' . $dataFim . ' 23:59:59"
        AND nota IS NOT NULL
        AND responsavel IS NOT NULL
        AND id_queue > 0';

        if (!empty($ids)) {
            $where .= ' AND id_queue IN (' . implode(',', $ids) . ')';
        }

        $fields = '
        id,
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

        $dados = [];

        while ($row = $resultados->fetchObject()) {
            $dados[] = [
                'id' => $row->id,
                'agente' => $row->responsavel,
                'quantidade_avaliacoes' => (int) $row->quantidade_avaliacoes,
                'media_notas' => (float) $row->media_notas,
                'promotores' => (int) $row->promotores,
                'neutros' => (int) $row->neutros,
                'detratores' => (int) $row->detratores,
                'csat' => (float) $row->csat
            ];
        }

        return json_encode($dados, JSON_UNESCAPED_UNICODE);
    }

    public static function getGraficoLinhaNotas($request)
    {
        $queryParams = $request->getQueryParams();

        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];

        $filas = $queryParams['filaLigacoes'] ?? [];
        if (!is_array($filas))
            $filas = [$filas];

        $ids = array_filter(array_map('intval', $filas), fn($v) => $v > 0);

        // últimos 12 meses
        $labels = [];
        $current = new \DateTime('first day of this month');

        for ($i = 11; $i >= 0; $i--) {
            $month = (clone $current)->modify("-$i months");
            $labels[] = $month->format('m/Y');
        }

        $promotores = array_fill(0, 12, 0);
        $neutros = array_fill(0, 12, 0);
        $detratores = array_fill(0, 12, 0);

        // 🔥 usa padrão correto
        $resultados = EntityLigacoes::getNotasByFilter($dataInicio, $dataFim, $ids);

        while ($row = $resultados->fetchObject(EntityLigacoes::class)) {

            if ($row->nota === null)
                continue;

            $nota = (int) $row->nota;
            $mesAno = (new \DateTime($row->data))->format('m/Y');

            $index = array_search($mesAno, $labels);

            if ($index !== false) {
                if ($nota >= 4)
                    $promotores[$index]++;
                elseif ($nota == 3)
                    $neutros[$index]++;
                else
                    $detratores[$index]++;
            }
        }

        return json_encode([
            'labels' => $labels,
            'datasets' => [
                ['label' => 'Satisfatórios', 'data' => $promotores],
                ['label' => 'Neutros', 'data' => $neutros],
                ['label' => 'Insatisfatórios', 'data' => $detratores],
            ]
        ]);
    }

    public static function getGraficoLinhaMediaNotas($request)
    {
        $queryParams = $request->getQueryParams();

        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];

        $filas = $queryParams['filaLigacoes'] ?? [];
        if (!is_array($filas))
            $filas = [$filas];

        $ids = array_filter(array_map('intval', $filas), fn($v) => $v > 0);

        // últimos 12 meses
        $labels = [];
        $current = new \DateTime('first day of this month');

        for ($i = 11; $i >= 0; $i--) {
            $month = (clone $current)->modify("-$i months");
            $labels[] = $month->format('m/Y');
        }

        $somas = array_fill(0, 12, 0);
        $quantidades = array_fill(0, 12, 0);

        // 🔥 padrão correto
        $resultados = EntityLigacoes::getNotasByFilter($dataInicio, $dataFim, $ids);

        while ($row = $resultados->fetchObject(EntityLigacoes::class)) {

            if ($row->nota === null)
                continue;

            $nota = (float) $row->nota;
            $mesAno = (new \DateTime($row->data))->format('m/Y');

            $index = array_search($mesAno, $labels);

            if ($index !== false) {
                $somas[$index] += $nota;
                $quantidades[$index]++;
            }
        }

        $medias = [];

        foreach ($somas as $i => $soma) {
            $medias[$i] = $quantidades[$i] > 0
                ? round($soma / $quantidades[$i], 2)
                : 0;
        }

        return json_encode([
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Média das Notas',
                    'data' => $medias
                ]
            ]
        ]);
    }
}