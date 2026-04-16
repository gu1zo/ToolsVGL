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
        $equipe = $queryParams['equipe'];

        $resultados = EntityLigacoes::getNotasByFilter($dataInicio, $dataFim, $equipe);
        $nota1 = 0;
        $nota2 = 0;
        $nota3 = 0;
        $nota4 = 0;
        $nota5 = 0;
        $total = 0;
        $totalNotas = 0;

        while ($obNotas = $resultados->fetchObject(EntityLigacoes::class)) {
            $nota = $obNotas->nota;
            switch ($nota) {
                case 1:
                    $nota1++;
                    break;
                case 2:
                    $nota2++;
                    break;
                case 3:
                    $nota3++;
                    break;
                case 4:
                    $nota4++;
                    break;
                case 5:
                    $nota5++;
                    break;
            }
            $total++;
        }

        $labels = ['1', '2', '3', '4', '5'];
        $data = [
            'labels' => $labels,
            'values' => [$nota1, $nota2, $nota3, $nota4, $nota5]
        ];
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public static function getGraficoCSAT($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $equipe = $queryParams['equipe'];
        $uri = $_SERVER['REQUEST_URI'];

        $resultados = EntityLigacoes::getNotasByFilter($dataInicio, $dataFim, $equipe);
        $detratores = 0;
        $promotores = 0;
        $neutros = 0;
        $total = 0;
        $totalNotas = 0;

        while ($obNotas = $resultados->fetchObject(EntityLigacoes::class)) {
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

        $labels = ['Satisfatórios', 'Neutros', 'Insatisfatórios'];
        $data = [
            'labels' => $labels,
            'values' => [$promotores, $neutros, $detratores]
        ];
        return json_encode($data, JSON_PRETTY_PRINT);
    }
    public static function getGraficoElogiosPorAgente($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'] ?? null;
        $dataFim = $queryParams['data_final'] ?? null;
        $equipe = $queryParams['equipe'] ?? null;

        // Busca todos os agentes que têm notas no período
        $resultados = EntityLigacoes::getAgentesByFilter($dataInicio, $dataFim, $equipe);

        $elogiosPorAgente = [];

        while ($row = $resultados->fetchObject()) {
            $agente = $row->responsavel;

            // Busca as notas desse agente
            $notasAgente = EntityLigacoes::getNotasByAgente($agente, $dataInicio, $dataFim, $equipe);
            $qtdElogios = 0;

            while ($notaObj = $notasAgente->fetchObject()) {
                if ($notaObj->nota >= 4 && $notaObj->nota <= 5) {
                    $qtdElogios++;
                }
            }

            if ($qtdElogios > 0) {
                $elogiosPorAgente[$agente] = $qtdElogios;
            }
        }

        // Ordena pelos maiores e pega os top 10
        arsort($elogiosPorAgente);
        $elogiosTop10 = array_slice($elogiosPorAgente, 0, 10, true);

        // Monta JSON
        $labels = array_keys($elogiosTop10);
        $values = array_values($elogiosTop10);

        return json_encode([
            'labels' => $labels,
            'values' => $values
        ], JSON_PRETTY_PRINT);
    }

    public static function getGraficoCriticasPorAgente($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'] ?? null;
        $dataFim = $queryParams['data_final'] ?? null;
        $equipe = $queryParams['equipe'] ?? null;

        $resultados = EntityLigacoes::getAgentesByFilter($dataInicio, $dataFim, $equipe);

        $criticasPorAgente = [];

        while ($row = $resultados->fetchObject()) {
            $agente = $row->responsavel;

            $notasAgente = EntityLigacoes::getNotasByAgente($agente, $dataInicio, $dataFim, $equipe);
            $qtdCriticas = 0;

            while ($notaObj = $notasAgente->fetchObject()) {
                if ($notaObj->nota >= 1 && $notaObj->nota <= 2) {
                    $qtdCriticas++;
                }
            }

            if ($qtdCriticas > 0) {
                $criticasPorAgente[$agente] = $qtdCriticas;
            }
        }

        arsort($criticasPorAgente);
        $criticasTop10 = array_slice($criticasPorAgente, 0, 10, true);

        $labels = array_keys($criticasTop10);
        $values = array_values($criticasTop10);

        return json_encode([
            'labels' => $labels,
            'values' => $values
        ], JSON_PRETTY_PRINT);
    }

    public static function getMediaNotasPorAgente($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'] ?? null;
        $dataFim = $queryParams['data_final'] ?? null;
        $equipe = $queryParams['equipe'] ?? null;

        $periodo = 'data BETWEEN "' . $dataInicio . ' 00:00:00" 
            AND "' . $dataFim . ' 23:59:59" AND nota is not null AND responsavel is not null';

        $where = $periodo;

        if ($equipe != 'todas') {
            if ($equipe == 'ggnet' || $equipe == 'alt') {
                $where .= ' AND canal = "' . $equipe . '"';
            } else {
                $where .= ' AND fila = "' . $equipe . '"';
            }
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

        $order = 'csat DESC';
        $group = 'responsavel';

        $resultados = EntityLigacoes::getLigacoes($where, $order, null, $fields, $group);

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

        return json_encode($dados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function getGraficoLinhaNotas($request)
    {
        // Pega filtros opcionais (ex.: equipe)
        $queryParams = $request->getQueryParams();
        $equipe = $queryParams['equipe'] ?? null;

        // Cria os últimos 12 meses (no formato "m/Y" para exibição)
        $labels = [];
        $current = new \DateTime('first day of this month');
        for ($i = 11; $i >= 0; $i--) {
            $month = (clone $current)->modify("-$i months");
            $labels[] = $month->format('m/Y');
        }

        // Inicializa arrays para cada linha
        $promotores = array_fill(0, 12, 0);
        $neutros = array_fill(0, 12, 0);
        $detratores = array_fill(0, 12, 0);

        // Consulta do banco
        $resultados = EntityLigacoes::getNotasByEquipe($equipe);

        while ($obNota = $resultados->fetchObject(EntityLigacoes::class)) {
            if ($obNota->nota == null) {
                continue;
            }
            $nota = (int) $obNota->nota;
            $data = new \DateTime($obNota->data); // ajuste conforme nome do campo de data
            $mesAno = $data->format('m/Y');

            // Se está dentro dos últimos 12 meses
            $index = array_search($mesAno, $labels);
            if ($index !== false) {
                if ($nota >= 4) {
                    $promotores[$index]++;
                } elseif ($nota == 3) {
                    $neutros[$index]++;
                } elseif ($nota <= 2) {
                    $detratores[$index]++;
                }
            }
        }

        // Monta JSON para Chart.js
        $data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Satisfatórios',
                    'data' => $promotores
                ],
                [
                    'label' => 'Neutros',
                    'data' => $neutros
                ],
                [
                    'label' => 'Insatisfatórios',
                    'data' => $detratores
                ]
            ]
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public static function getGraficoLinhaMediaNotas($request)
    {
        // Pega filtros opcionais (ex.: equipe)
        $queryParams = $request->getQueryParams();
        $equipe = $queryParams['equipe'] ?? null;

        // Cria os últimos 12 meses (no formato "m/Y" para exibição)
        $labels = [];
        $current = new \DateTime('first day of this month');
        for ($i = 11; $i >= 0; $i--) {
            $month = (clone $current)->modify("-$i months");
            $labels[] = $month->format('m/Y');
        }

        // Inicializa arrays para somatório e contagem
        $somas = array_fill(0, 12, 0);
        $quantidades = array_fill(0, 12, 0);

        // Consulta do banco
        $resultados = EntityLigacoes::getNotasByEquipe($equipe);

        while ($obNota = $resultados->fetchObject(EntityLigacoes::class)) {
            if ($obNota->nota == null) {
                continue;
            }
            $nota = (float) $obNota->nota;
            $data = new \DateTime($obNota->data); // ajuste conforme nome do campo de data
            $mesAno = $data->format('m/Y');

            // Se está dentro dos últimos 12 meses
            $index = array_search($mesAno, $labels);
            if ($index !== false) {
                $somas[$index] += $nota;
                $quantidades[$index]++;
            }
        }

        // Calcula média
        $medias = [];
        foreach ($somas as $i => $soma) {
            if ($quantidades[$i] > 0) {
                $medias[$i] = round($soma / $quantidades[$i], 2);
            } else {
                $medias[$i] = 0; // Ou 0, ou deixar null para aparecer "vazio" no gráfico
            }
        }

        // Monta JSON para Chart.js
        $data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Média das Notas',
                    'data' => $medias
                ]
            ]
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }
}