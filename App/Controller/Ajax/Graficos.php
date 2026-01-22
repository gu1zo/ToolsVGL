<?php
namespace App\Controller\Ajax;

use App\Model\Entity\Notas as EntityNotas;
use App\Model\Entity\Avaliacoes as EntityAvaliacao;
use App\Model\Entity\Tecnicos as EntityTecnicos;
use App\Model\Entity\Massiva as EntityMassivas;

class Graficos
{

    public static function getGraficoNotas($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $equipe = $queryParams['equipe'];

        $resultados = EntityNotas::getNotasByFilter($dataInicio, $dataFim, $equipe);
        $nota1 = 0;
        $nota2 = 0;
        $nota3 = 0;
        $nota4 = 0;
        $nota5 = 0;
        $total = 0;
        $totalNotas = 0;

        while ($obNotas = $resultados->fetchObject(EntityNotas::class)) {
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
        $resultados = EntityNotas::getAgentesByFilter($dataInicio, $dataFim, $equipe);

        $elogiosPorAgente = [];

        while ($row = $resultados->fetchObject()) {
            $agente = $row->agente;

            // Busca as notas desse agente
            $notasAgente = EntityNotas::getNotasByAgente($agente, $dataInicio, $dataFim, $equipe);
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

        $resultados = EntityNotas::getAgentesByFilter($dataInicio, $dataFim, $equipe);

        $criticasPorAgente = [];

        while ($row = $resultados->fetchObject()) {
            $agente = $row->agente;

            $notasAgente = EntityNotas::getNotasByAgente($agente, $dataInicio, $dataFim, $equipe);
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
        $resultados = EntityNotas::getNotasByEquipe($equipe);

        while ($obNota = $resultados->fetchObject(EntityNotas::class)) {
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
        $resultados = EntityNotas::getNotasByEquipe($equipe);

        while ($obNota = $resultados->fetchObject(EntityNotas::class)) {
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

    public static function getGraficoNotasOs($request)
    {

        $resultados = EntityAvaliacao::getAvaliacoes();
        $bom = 0;
        $neutro = 0;
        $ruim = 0;
        $total = 0;

        while ($obNotas = $resultados->fetchObject()) {
            $nota = $obNotas->nota;
            if ($nota <= 4) {
                $ruim++;
            } else if ($nota >= 7) {
                $bom++;
            } else {
                $neutro++;
            }
            $total++;
        }

        $labels = ['Bom', 'Neutro', 'Ruim'];
        $data = [
            'labels' => $labels,
            'values' => [$bom, $neutro, $ruim]
        ];
        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public static function getGraficoTecnicosPositividade($request)
    {
        // Busca todos os agentes que têm notas no período
        $resultados = EntityAvaliacao::getTecnicos();

        $elogiosPorTecnico = [];

        while ($row = $resultados->fetchObject()) {
            $tecnico = $row->id_tecnico;

            // Busca as notas desse agente
            $notasTecnico = EntityAvaliacao::getAvaliacoesByTecnico($tecnico);
            $qtdElogios = 0;

            while ($notaObj = $notasTecnico->fetchObject()) {
                if ($notaObj->nota >= 7 && $notaObj->nota <= 10) {
                    $qtdElogios++;
                }
            }

            if ($qtdElogios > 0) {
                $obTecnico = EntityTecnicos::getTecnicosById($tecnico);
                $nomeTecnico = $obTecnico->nome;
                $elogiosPorTecnico[$nomeTecnico] = $qtdElogios;
            }
        }

        // Ordena pelos maiores e pega os top 10
        arsort($elogiosPorTecnico);
        $elogiosTop10 = array_slice($elogiosPorTecnico, 0, 10, true);

        // Monta JSON
        $labels = array_keys($elogiosTop10);
        $values = array_values($elogiosTop10);

        return json_encode([
            'labels' => $labels,
            'values' => $values
        ], JSON_PRETTY_PRINT);
    }
    public static function getGraficoTecnicosNegatividade($request)
    {
        // Busca todos os agentes que têm notas no período
        $resultados = EntityAvaliacao::getTecnicos();

        $criticaPorTecnico = [];

        while ($row = $resultados->fetchObject()) {
            $tecnico = $row->id_tecnico;

            // Busca as notas desse agente
            $notasTecnico = EntityAvaliacao::getAvaliacoesByTecnico($tecnico);
            $qtdCritica = 0;

            while ($notaObj = $notasTecnico->fetchObject()) {
                if ($notaObj->nota <= 4) {
                    $qtdCritica++;
                }
            }

            if ($qtdCritica > 0) {
                $obTecnico = EntityTecnicos::getTecnicosById($tecnico);
                $nomeTecnico = $obTecnico->nome;
                $criticaPorTecnico[$nomeTecnico] = $qtdCritica;
            }
        }

        // Ordena pelos maiores e pega os top 10
        arsort($criticaPorTecnico);
        $criticaTop10 = array_slice($criticaPorTecnico, 0, 10, true);

        // Monta JSON
        $labels = array_keys($criticaTop10);
        $values = array_values($criticaTop10);

        return json_encode([
            'labels' => $labels,
            'values' => $values
        ], JSON_PRETTY_PRINT);
    }

    public static function getGraficoLinhaOs($request)
    {
        // Cria os últimos 12 meses (no formato "m/Y" para exibição)
        $labels = [];
        $current = new \DateTime('first day of this month');
        for ($i = 11; $i >= 0; $i--) {
            $month = (clone $current)->modify("-$i months");
            $labels[] = $month->format('m/Y');
        }

        // Inicializa arrays para cada linha
        $bom = array_fill(0, 12, 0);
        $neutro = array_fill(0, 12, 0);
        $ruim = array_fill(0, 12, 0);

        // Consulta do banco
        $resultados = EntityAvaliacao::getAvaliacoes();

        while ($obNota = $resultados->fetchObject()) {
            $nota = (int) $obNota->nota;
            $data = new \DateTime($obNota->data); // ajuste conforme nome do campo de data
            $mesAno = $data->format('m/Y');

            // Se está dentro dos últimos 12 meses
            $index = array_search($mesAno, $labels);
            if ($index !== false) {
                if ($nota <= 4) {
                    $ruim[$index]++;
                } elseif ($nota >= 7) {
                    $bom[$index]++;
                } else {
                    $neutro[$index]++;
                }
            }
        }

        // Monta JSON para Chart.js
        $data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Bom',
                    'data' => $bom
                ],
                [
                    'label' => 'Neutro',
                    'data' => $neutro
                ],
                [
                    'label' => 'Ruim',
                    'data' => $ruim
                ]
            ]
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }
    public static function getGraficoRegionais($request)
    {
        $resultados = EntityMassivas::getMassivas();
        $regionais = [];

        while ($row = $resultados->fetchObject()) {
            $regional = $row->regional;

            if (!empty($regional)) {
                if (!isset($regionais[$regional])) {
                    $regionais[$regional] = 0;
                }

                $regionais[$regional]++;
            }
        }

        $labels = array_keys($regionais);
        $values = array_values($regionais);

        return json_encode([
            'labels' => $labels,
            'values' => $values
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function getGraficosTipo($request)
    {
        $resultados = EntityMassivas::getMassivas();
        $tipos = [];

        while ($row = $resultados->fetchObject()) {
            $tipo = $row->tipo;

            if (!empty($tipo)) {
                if (!isset($tipos[$tipo])) {
                    $tipos[$tipo] = 0;
                }

                $tipos[$tipo]++;
            }
        }

        $labels = array_keys(array: $tipos);
        $values = array_values($tipos);

        return json_encode([
            'labels' => $labels,
            'values' => $values
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function getGraficoLinhaRegionais($request)
    {
        // Últimos 12 meses (labels)
        $labels = [];
        $current = new \DateTime('first day of this month');

        for ($i = 11; $i >= 0; $i--) {
            $month = (clone $current)->modify("-$i months");
            $labels[] = $month->format('m/Y');
        }

        // Busca todas as massivas/eventos
        $resultados = EntityMassivas::getMassivas();

        $dadosRegionais = [];
        $totalMensal = array_fill(0, 12, 0);

        while ($row = $resultados->fetchObject()) {
            $regional = $row->regional;
            $data = new \DateTime($row->dataInicio); // ajuste se necessário
            $mesAno = $data->format('m/Y');

            $index = array_search($mesAno, $labels);
            if ($index === false || empty($regional)) {
                continue;
            }

            // Inicializa a regional se ainda não existir
            if (!isset($dadosRegionais[$regional])) {
                $dadosRegionais[$regional] = array_fill(0, 12, 0);
            }

            // Incrementa contadores
            $dadosRegionais[$regional][$index]++;
            $totalMensal[$index]++;
        }

        // Monta datasets para o Chart.js
        $datasets = [];

        foreach ($dadosRegionais as $regional => $valores) {
            $datasets[] = [
                'label' => $regional,
                'data' => $valores,
            ];
        }

        // Linha de total mensal
        $datasets[] = [
            'label' => 'Total de Eventos',
            'data' => $totalMensal,
        ];

        return json_encode([
            'labels' => $labels,
            'datasets' => $datasets
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


    public static function getGraficoLinhaTipos($request)
    {
        // Últimos 12 meses (labels)
        $labels = [];
        $current = new \DateTime('first day of this month');

        for ($i = 11; $i >= 0; $i--) {
            $month = (clone $current)->modify("-$i months");
            $labels[] = $month->format('m/Y');
        }

        // Busca todas as massivas/eventos
        $resultados = EntityMassivas::getMassivas();

        $dadosTipos = [];

        while ($row = $resultados->fetchObject()) {
            $tipo = $row->tipo; // <-- campo do tipo do evento
            $data = new \DateTime($row->dataInicio); // ajuste se necessário
            $mesAno = $data->format('m/Y');

            $index = array_search($mesAno, $labels);
            if ($index === false || empty($tipo)) {
                continue;
            }

            // Inicializa o tipo se ainda não existir
            if (!isset($dadosTipos[$tipo])) {
                $dadosTipos[$tipo] = array_fill(0, 12, 0);
            }

            // Incrementa contadores
            $dadosTipos[$tipo][$index]++;
        }

        // Monta datasets para o Chart.js
        $datasets = [];

        foreach ($dadosTipos as $tipo => $valores) {
            $datasets[] = [
                'label' => $tipo,
                'data' => $valores,
            ];
        }

        return json_encode([
            'labels' => $labels,
            'datasets' => $datasets
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }





}