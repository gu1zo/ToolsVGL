<?php
namespace App\Controller\Ajax;

use App\Model\Entity\NotasResolutividade as EntityNotas;

class GraficosResolutividade
{

    public static function getGraficoNotas($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $equipe = $queryParams['equipe'];
        $uri = $_SERVER['REQUEST_URI'];

        $resultados = EntityNotas::getNotasByFilter($dataInicio, $dataFim, $equipe);
        $resolvido = 0;
        $nresolvido = 0;

        while ($obNotas = $resultados->fetchObject(EntityNotas::class)) {
            $nota = $obNotas->nota;
            if ($nota == 1) {
                $resolvido++;
            } else if ($nota == 0) {
                $nresolvido++;
            }
        }

        $labels = ['Resolvido', 'Não Resolvido'];
        $data = [
            'labels' => $labels,
            'values' => [$resolvido, $nresolvido]
        ];
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public static function getGraficoResolutividade($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $equipe = $queryParams['equipe'];

        $resultados = EntityNotas::getNotasByFilter($dataInicio, $dataFim, $equipe);
        $resolvido = 0;
        $nresolvido = 0;

        while ($obNotas = $resultados->fetchObject(EntityNotas::class)) {
            $nota = $obNotas->nota;
            if ($nota == 1) {
                $resolvido++;
            } elseif ($nota == 0) {
                $nresolvido++;
            }
        }

        $total = $resolvido + $nresolvido;

        $percentResolvido = $total > 0 ? round(($resolvido / $total) * 100, 2) : 0;
        $percentNaoResolvido = $total > 0 ? round(($nresolvido / $total) * 100, 2) : 0;

        $labels = ['Resolvido', 'Não Resolvido'];
        $data = [
            'labels' => $labels,
            'values' => [$percentResolvido, $percentNaoResolvido]
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }


    public static function getGraficoResolutividadePorAgente($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'] ?? null;
        $dataFim = $queryParams['data_final'] ?? null;
        $equipe = $queryParams['equipe'] ?? null;

        // Busca todos os agentes que têm notas no período
        $resultados = EntityNotas::getAgentesByFilter($dataInicio, $dataFim, $equipe);

        $elogiosPorAgente = [];

        while ($row = $resultados->fetchObject()) {
            $agente = $row->agente;

            // Busca as notas desse agente
            $notasAgente = EntityNotas::getNotasByAgente($agente, $dataInicio, $dataFim, $equipe);
            $qtdElogios = 0;

            while ($notaObj = $notasAgente->fetchObject()) {
                if ($notaObj->nota == 1) {
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




    public static function getGraficoNResolutividadePorAgente($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'] ?? null;
        $dataFim = $queryParams['data_final'] ?? null;
        $equipe = $queryParams['equipe'] ?? null;

        $resultados = EntityNotas::getAgentesByFilter($dataInicio, $dataFim, $equipe);

        $criticasPorAgente = [];

        while ($row = $resultados->fetchObject()) {
            $agente = $row->agente;

            $notasAgente = EntityNotas::getNotasByAgente($agente, $dataInicio, $dataFim, $equipe);
            $qtdCriticas = 0;

            while ($notaObj = $notasAgente->fetchObject()) {
                if ($notaObj->nota == 0) {
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


    public static function getGraficoLinhaResolutividadeIndividual($request)
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

        // Inicializa arrays para resolvidos e não resolvidos
        $resolvidos = array_fill(0, 12, 0);
        $naoResolvidos = array_fill(0, 12, 0);

        // Consulta do banco
        $resultados = EntityNotas::getNotasByEquipe($equipe);

        while ($obNota = $resultados->fetchObject(EntityNotas::class)) {
            $nota = (int) $obNota->nota; // 0 ou 1
            $data = new \DateTime($obNota->data);
            $mesAno = $data->format('m/Y');

            // Se está dentro dos últimos 12 meses
            $index = array_search($mesAno, $labels);
            if ($index !== false) {
                if ($nota === 1) {
                    $resolvidos[$index]++;
                } else {
                    $naoResolvidos[$index]++;
                }
            }
        }

        // Monta JSON para Chart.js (mesmo padrão de linha)
        $data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Resolvidos',
                    'data' => $resolvidos
                ],
                [
                    'label' => 'Não Resolvidos',
                    'data' => $naoResolvidos
                ]
            ]
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public static function getGraficoLinhaResolutividade($request)
    {
        $queryParams = $request->getQueryParams();
        $equipe = $queryParams['equipe'] ?? null;

        // Cria os últimos 12 meses
        $labels = [];
        $current = new \DateTime('first day of this month');
        for ($i = 11; $i >= 0; $i--) {
            $month = (clone $current)->modify("-$i months");
            $labels[] = $month->format('m/Y');
        }

        // Inicializa arrays para somatório e contagem
        $resolvidos = array_fill(0, 12, 0); // quantidade de resolvidos
        $total = array_fill(0, 12, 0);      // total de chamados

        // Consulta do banco
        $resultados = EntityNotas::getNotasByEquipe($equipe);

        while ($obNota = $resultados->fetchObject(EntityNotas::class)) {
            $nota = (int) $obNota->nota; // 0 ou 1
            $data = new \DateTime($obNota->data);
            $mesAno = $data->format('m/Y');

            $index = array_search($mesAno, $labels);
            if ($index !== false) {
                $total[$index]++;
                if ($nota === 1) {
                    $resolvidos[$index]++;
                }
            }
        }

        // Calcula % de resolutividade
        $percentuais = [];
        foreach ($total as $i => $qtd) {
            if ($qtd > 0) {
                $percentuais[$i] = round(($resolvidos[$i] / $qtd) * 100, 2);
            } else {
                $percentuais[$i] = 0;
            }
        }

        // Monta JSON para Chart.js
        $data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Resolutividade',
                    'data' => $percentuais
                ]
            ]
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }






}