<?php
namespace App\Cronjobs;

require __DIR__ . '/../includes/app.php';

use App\Model\Rest\APISippulse;
use App\Model\Entity\Ligacoes as EntityLigacoes;
use DateTime;
use DateTimeZone;
use DateInterval;
use DatePeriod;

function timeToSeconds($time)
{
    if (!$time)
        return 0;
    $parts = explode(':', $time);
    if (count($parts) !== 3)
        return 0;
    return ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
}

$filas = [
    ["fila" => "FILA_CSA_GGNET", "ivrId" => 15],
    ["fila" => "FILA_CSA_N2_GGNET", "ivrId" => 15],
    ["fila" => "FILA_CSA_N2_ALT", "ivrId" => 20],
    ["fila" => "FILA_SAC_FINANCEIRO_GGNET", "ivrId" => 24],
    ["fila" => "FILA_SAC_FINANCEIRO_ALT", "ivrId" => 21],
    ["fila" => "FILA_CSA_EVO", "ivrId" => null],
    ["fila" => "FILA_SAC_FINANCEIRO_EVO", "ivrId" => null],
];

// intervalo de datas
$inicio = new DateTime('2026-04-01', new DateTimeZone('America/Sao_Paulo'));
$fim = new DateTime('2026-04-03', new DateTimeZone('America/Sao_Paulo'));

// inclui o último dia
$fim->modify('+1 day');

$periodo = new DatePeriod($inicio, new DateInterval('P1D'), $fim);

foreach ($periodo as $dataConsulta) {

    foreach ($filas as $item) {

        $queue = $item['fila'];
        $ivrId = $item['ivrId'];

        $page = 0;
        $size = 100;
        $totalPages = 1;

        do {
            $response = APISippulse::getChamadas(
                $dataConsulta->format('Y-m-d'),
                $page,
                $size,
                $queue
            );

            $content = $response['content'] ?? [];

            foreach ($content as $ligacao) {

                $status = $ligacao['status'] ?? null;
                if ($status != 'answered' && $status != 'abandoned')
                    continue;

                $obLigacoes = EntityLigacoes::getLigacoesByUuid($ligacao['uuid']);

                if (!$obLigacoes instanceof EntityLigacoes) {

                    $nota = null;

                    if ($ivrId !== null) {
                        $nota = APISippulse::getNota(
                            $dataConsulta->format('Y-m-d'),
                            0,
                            20,
                            $ivrId,
                            $ligacao['callerId'],
                            $ligacao['uuid']
                        );

                        if ($nota == 't' || $nota < 1 || $nota > 5) {
                            $nota = null;
                        }
                    }

                    $obLigacoes = new EntityLigacoes();
                    $obLigacoes->data = $ligacao['startStamp'] ?? null;
                    $obLigacoes->tempo_fila = $ligacao['waitingDuration'] ?? 0;

                    $tempoAtendimento = $ligacao['queueCallDuration'] ?? $ligacao['duration'] ?? '00:00:00';
                    $obLigacoes->tempo_atendimento = timeToSeconds($tempoAtendimento);

                    $obLigacoes->responsavel = $ligacao['userName'] ?? null;
                    $obLigacoes->status = $status;
                    $obLigacoes->fila = $queue;
                    $obLigacoes->id_queue = 0;
                    $obLigacoes->numero = $ligacao['callerId'] ?? null;
                    $obLigacoes->uuid = $ligacao['uuid'];
                    $obLigacoes->nota = $nota;

                    $obLigacoes->cadastrar();
                }
            }

            $totalPages = $response['totalPages'] ?? 1;
            $page++;

        } while ($page < $totalPages);
    }

    echo "Dia " . $dataConsulta->format('d/m/Y') . " sincronizado\n";
}

$dataAtual = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
echo "Sincronização concluída em " . $dataAtual->format('d/m/Y H:i') . "\n";