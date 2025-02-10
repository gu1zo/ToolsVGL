<?php
namespace App\Controller\Ajax;

use App\Model\Entity\Joins as EntityJoins;
use App\Model\Entity\Evento as EntityEvento;

use DateTime;

class Graficos
{
    function converterHorasDecimais($horasDecimais)
    {
        $horas = floor($horasDecimais);
        $minutosDecimais = ($horasDecimais - $horas) * 60;
        $minutos = floor($minutosDecimais);
        $segundos = round(($minutosDecimais - $minutos) * 60);

        return sprintf('%02d:%02d:%02d', $horas, $minutos, $segundos);
    }
    public static function getGraficosTotalEventos()
    {

        $dataAtual = new DateTime();
        $dataInicio = (clone $dataAtual)->modify('-11 months')->modify('first day of this month');

        // Array para armazenar os rótulos e os dados
        $labels = [];
        $dados = [
            'Manutenção' => array_fill(0, 12, 0),
            'Evento' => array_fill(0, 12, 0),
            'Solicitação Emergencial' => array_fill(0, 12, 0),
            'Total' => array_fill(0, 12, 0)
        ];

        // Gera os labels dos meses corretamente
        $tempData = clone $dataInicio;
        for ($i = 0; $i < 12; $i++) {
            $labels[] = $tempData->format('M Y');
            $tempData->modify('+1 month');

        }
        $mapaMes = array_flip($labels);

        // Define o intervalo correto para a consulta
        $dataInicioStr = $dataInicio->format('Y-m-01 00:00:00');
        $dataAtualStr = $dataAtual->format('Y-m-d 23:59:59');

        // Consulta SQL para contar os eventos por mês e tipo
        $resultados = EntityJoins::getEventosByDateAndMonth($dataInicioStr, $dataAtualStr);
        // Mapeia os resultados para o array

        while ($row = $resultados->fetchObject(EntityJoins::class)) {
            $mes = $row->mes;
            switch ($row->tipo) {
                case 'manutencao':
                    $tipo = 'Manutenção';
                    break;
                case 'evento':
                    $tipo = 'Evento';
                    break;
                case 'emergencial':
                    $tipo = 'Solicitação Emergencial';
                    break;
            }


            $total = (int) $row->total;

            if ($total > 0) {
                $indice = $mapaMes[$mes];
                $dados[$tipo][$indice] = $total;
                $dados['Total'][$indice] += $total;
            }
        }

        // Monta a estrutura final
        $data = [
            'labels' => $labels,
            'datasets' => []
        ];

        foreach ($dados as $tipo => $valores) {
            $data['datasets'][] = [
                'label' => $tipo,
                'data' => $valores
            ];
        }
        return json_encode($data, JSON_PRETTY_PRINT);

    }

    public static function getGraficosHora()
    {

        $dataAtual = new DateTime();
        $dataInicio = (clone $dataAtual)->modify('-11 months')->modify('first day of this month');

        // Array para armazenar os rótulos e os dados
        $labels = [];
        $dados = array_fill(0, 12, 0); // Inicializa o array com valores zerados

        // Gera os labels dos meses corretamente (em inglês)
        $tempData = clone $dataInicio;
        for ($i = 0; $i < 12; $i++) {
            $labels[] = $tempData->format('M Y'); // Exemplo: "Jan 2024"
            $tempData->modify('+1 month');
        }

        // Define o intervalo correto para a consulta
        $dataInicioStr = $dataInicio->format('Y-m-01 00:00:00');
        $dataAtualStr = $dataAtual->format('Y-m-d 23:59:59');

        // Consulta SQL para obter os eventos com dataInicio e dataFim
        $resultados = EntityJoins::getEventosByDate($dataInicioStr, $dataAtualStr);

        // Mapeia os resultados para o array
        $mapaMes = array_flip($labels);

        while ($row = $resultados->fetchObject(EntityJoins::class)) {
            $mes = $row->mes;
            $dataInicioEvento = $row->dataInicio != null ? new DateTime($row->dataInicio) : $dataAtual;
            $dataFimEvento = $row->dataFim != null ? new DateTime($row->dataFim) : $dataInicioEvento;

            // Calcula a duração em horas
            $duracaoHoras = ($dataFimEvento >= $dataInicioEvento)
                ? ($dataFimEvento->getTimestamp() - $dataInicioEvento->getTimestamp()) / 3600
                : 0;

            // Adiciona a duração ao mês correspondente
            if (isset($mapaMes[$mes])) {
                $indice = $mapaMes[$mes];
                $dados[$indice] += $duracaoHoras;
            }
        }

        // Monta a estrutura final
        $data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Tempo Total Afetado (horas)',
                    'data' => array_map(function ($valor) {
                        return round($valor, 2); // Arredonda para uma casa decimal
                    }, $dados)
                ]
            ]
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }
    public static function getGraficosTotalClientes()
    {
        $dataAtual = new DateTime();
        $dataInicio = (clone $dataAtual)->modify('-11 months')->modify('first day of this month');

        // Array para armazenar os rótulos e os dados
        $labels = [];
        $dados = array_fill(0, 12, 0); // Inicializa o array com valores zerados

        // Gera os labels dos meses corretamente (em inglês)
        $tempData = clone $dataInicio;
        for ($i = 0; $i < 12; $i++) {
            $labels[] = $tempData->format('M Y'); // Exemplo: "Jan 2024"
            $tempData->modify('+1 month');
        }

        // Define o intervalo correto para a consulta
        $dataInicioStr = $dataInicio->format('Y-m-01 00:00:00');
        $dataAtualStr = $dataAtual->format('Y-m-d 23:59:59');

        // Consulta SQL para obter os eventos com clientes afetados
        $resultados = EntityJoins::getClientesEventosByDate($dataInicioStr, $dataAtualStr);

        // Mapeia os resultados para o array
        $mapaMes = array_flip($labels);

        // Variável para validar o total de clientes
        $totalClientes = 0;

        while ($row = $resultados->fetchObject(EntityJoins::class)) {
            $mes = $row->mes;
            $clientesJson = $row->clientes;

            // Decodifica o JSON e conta os clientes
            $clientesArray = json_decode($clientesJson, true);
            $quantidadeClientes = is_array($clientesArray) ? count($clientesArray) : 0;

            // Adiciona a quantidade ao mês correspondente
            if (isset($mapaMes[$mes])) {
                $indice = $mapaMes[$mes];
                $dados[$indice] += $quantidadeClientes;

                // Adiciona à soma total de clientes
                $totalClientes += $quantidadeClientes;
            }
        }

        // Valida a soma total
        $somaTotal = array_sum($dados);
        if ($totalClientes !== $somaTotal) {
            error_log("Erro na soma: total esperado = $totalClientes, soma calculada = $somaTotal");
        }

        // Monta a estrutura final
        $data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Total de Clientes Afetados',
                    'data' => $dados
                ]
            ]
        ];
        return json_encode($data, JSON_PRETTY_PRINT);
    }
    public static function getGraficosHoraMedia()
    {


        $dataAtual = new DateTime();
        $dataInicio = (clone $dataAtual)->modify('-11 months')->modify('first day of this month');

        // Arrays para armazenar os rótulos e os dados
        $labels = [];
        $dados = array_fill(0, 12, 0); // Inicializa com valores zerados
        $contagemEventos = array_fill(0, 12, 0); // Contador de eventos por mês

        // Gera os labels dos meses corretamente (em inglês)
        $tempData = clone $dataInicio;
        for ($i = 0; $i < 12; $i++) {
            $labels[] = $tempData->format('M Y'); // Exemplo: "Jan 2024"
            $tempData->modify('+1 month');
        }

        // Define o intervalo correto para a consulta
        $dataInicioStr = $dataInicio->format('Y-m-01 00:00:00');
        $dataAtualStr = $dataAtual->format('Y-m-d 23:59:59');

        // Consulta SQL para obter os eventos com dataInicio e dataFim
        $resultados = EntityJoins::getEventosByDate($dataInicioStr, $dataAtualStr);

        // Mapeia os resultados para o array
        $mapaMes = array_flip($labels);

        while ($row = $resultados->fetchObject(EntityJoins::class)) {
            $mes = $row->mes;
            $dataInicioEvento = $row->dataInicio != null ? new DateTime($row->dataInicio) : $dataAtual;
            $dataFimEvento = $row->dataFim != null ? new DateTime($row->dataFim) : $dataInicioEvento;

            // Calcula a duração em horas
            $duracaoHoras = ($dataFimEvento >= $dataInicioEvento)
                ? ($dataFimEvento->getTimestamp() - $dataInicioEvento->getTimestamp()) / 3600
                : 0;

            // Adiciona a duração ao mês correspondente e conta o evento
            if (isset($mapaMes[$mes])) {
                $indice = $mapaMes[$mes];
                $dados[$indice] += $duracaoHoras;

                $contagemEventos[$indice]++; // Incrementa o contador de eventos
            }
        }

        // Calcula a média para cada mês
        foreach ($dados as $indice => $totalHoras) {
            if ($contagemEventos[$indice] > 0) {
                $dados[$indice] = round($totalHoras / $contagemEventos[$indice], 2); // Média e arredondamento
            } else {
                $dados[$indice] = 0; // Se não houver eventos, mantém 0
            }
        }

        // Monta a estrutura final
        $data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Média de Tempo Afetado por Evento (horas)',
                    'data' => $dados
                ]
            ]
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public static function getGraficosForcaMaior()
    {
        $dataAtual = new DateTime();
        $dataInicio = (clone $dataAtual)->modify('-11 months')->modify('first day of this month');

        // Arrays para armazenar os rótulos e dados
        $labels = [];
        $dadosForcaMaior = array_fill(0, 12, 0); // Inicializa com 0 para eventos por força maior
        $dadosTotalEventos = array_fill(0, 12, 0); // Inicializa com 0 para o total de eventos

        // Gera os labels dos meses corretamente (em inglês)
        $tempData = clone $dataInicio;
        for ($i = 0; $i < 12; $i++) {
            $labels[] = $tempData->format('M Y'); // Exemplo: "Jan 2024"
            $tempData->modify('+1 month');
        }

        // Define o intervalo correto para a consulta
        $dataInicioStr = $dataInicio->format('Y-m-01 00:00:00');
        $dataAtualStr = $dataAtual->format('Y-m-d 23:59:59');

        // Consulta SQL para obter os eventos com o campo "forca_maior"
        $resultados = EntityJoins::getEventosForcaMaiorByDate($dataInicioStr, $dataAtualStr);

        // Mapeia os resultados para o array de meses
        $mapaMes = array_flip($labels);

        while ($row = $resultados->fetchObject(EntityJoins::class)) {
            $mes = $row->mes;
            $forcaMaior = (bool) $row->forca_maior; // Converte para booleano (caso não esteja no tipo correto)

            // Verifica se o evento ocorreu por força maior
            if (isset($mapaMes[$mes])) {
                $indice = $mapaMes[$mes];
                $dadosTotalEventos[$indice]++; // Conta o total de eventos

                // Se o evento foi causado por força maior, incrementa o contador
                if ($forcaMaior) {
                    $dadosForcaMaior[$indice]++;
                }
            }
        }

        // Calcula o percentual de eventos causados por força maior para cada mês
        $percentuais = [];
        foreach ($dadosForcaMaior as $indice => $countForcaMaior) {
            if ($dadosTotalEventos[$indice] > 0) {
                $percentual = ($countForcaMaior / $dadosTotalEventos[$indice]) * 100;
                $percentuais[] = round($percentual, 2); // Arredonda para 2 casas decimais
            } else {
                $percentuais[] = 0; // Se não houver eventos, o percentual será 0
            }
        }

        // Monta a estrutura final com os percentuais
        $data = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Percentual de Eventos Causados por Força Maior',
                    'data' => $percentuais
                ]
            ]
        ];

        return json_encode($data, JSON_PRETTY_PRINT);
    }

    public static function getTopCaixas()
    {
        $dataAtual = new DateTime();
        $dataInicio = (clone $dataAtual)->modify('-11 months')->modify('first day of this month');

        $dataInicioStr = $dataInicio->format('Y-m-01 00:00:00');
        $dataAtualStr = $dataAtual->format('Y-m-d 23:59:59');

        $resultados = EntityJoins::getTop10CaixasByDate($dataInicioStr, $dataAtualStr);
        $data = [];
        while ($row = $resultados->fetchObject(EntityJoins::class)) {
            $data[] = [
                'nome' => $row->ponto_acesso_nome,
                'total' => $row->total_vezes_afetado,
                'horas' => $row->tempo_total_afetado
            ];
        }
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    public static function getTopMotivos()
    {
        $dataAtual = new DateTime();
        $dataInicio = (clone $dataAtual)->modify('-11 months')->modify('first day of this month');

        $dataInicioStr = $dataInicio->format('Y-m-01 00:00:00');
        $dataAtualStr = $dataAtual->format('Y-m-d 23:59:59');

        $resultados = EntityJoins::getTop10MotivosByDate($dataInicioStr, $dataAtualStr);
        $data = [];
        while ($row = $resultados->fetchObject(EntityJoins::class)) {
            $data[] = [
                'motivo' => $row->motivo_conclusao,
                'total' => $row->total,
            ];
        }
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function getDEX()
    {
        $dataAtual = new DateTime();
        $dataInicio = (clone $dataAtual)->modify('-11 months')->modify('first day of this month');

        $dataInicioStr = $dataInicio->format('Y-m-01 00:00:00');
        $dataAtualStr = $dataAtual->format('Y-m-d 23:59:59');

        // Obtém os eventos dentro do intervalo de datas
        $resultados = EntityEvento::getEventosByDateAndMonth($dataInicioStr, $dataAtualStr);

        // Gera os labels para os últimos 12 meses
        $labels = [];
        $tempData = clone $dataInicio;
        for ($i = 0; $i < 12; $i++) {
            $labels[] = $tempData->format('M Y');
            $tempData->modify('+1 month');
        }

        // Mapa para contar eventos por mês
        $contagemEventos = array_fill_keys($labels, 0);

        // Processa os resultados
        while ($row = $resultados->fetchObject(EntityEvento::class)) {
            $clientes = json_decode($row->clientes, true);

            // Verifica se o evento afetou mais de 149 clientes
            if (is_array($clientes) && count($clientes) > 149) {
                $mesAno = date('M Y', strtotime($row->dataInicio));

                if (isset($contagemEventos[$mesAno])) {
                    $contagemEventos[$mesAno]++;
                }
            }
        }

        // Monta o array final no formato esperado
        $data = [
            "labels" => $labels,
            "datasets" => [
                [
                    "label" => "Total",
                    "data" => array_values($contagemEventos)
                ]
            ]
        ];
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }


}