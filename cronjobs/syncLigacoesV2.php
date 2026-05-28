<?php
namespace App\Cronjobs;

require __DIR__ . '/../includes/app.php';

use App\Model\Entity\Ligacoes as EntityLigacoes;
use App\Model\Entity\Sippulse\cdrs_full as EntityLigacoesSip;
use App\Model\Entity\Queues as EntityQueues;
use DateTime;
use DateTimeZone;

function timeToSeconds($time)
{
    if (!$time)
        return 0;

    $parts = explode(':', $time);
    if (count($parts) !== 3)
        return 0;

    return ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
}

// Define período fixo
$dataInicio = new DateTime('2026-05-1', new DateTimeZone('America/Sao_Paulo'));
$dataFim = new DateTime('2026-05-24', new DateTimeZone('America/Sao_Paulo'));

$results = EntityQueues::getQueues();

while ($fila = $results->fetchObject(EntityQueues::class)) {
    $filasPermitidas = [
        'FILA_CSA_GGNET',
        'FILA_CSA_N2_GGNET',
        'FILA_CSA_N2_ALT',
        'FILA_SAC_FINANCEIRO_GGNET',
        'FILA_SAC_FINANCEIRO_ALT',
        'FILA_CSA_EVO',
        'FILA_SAC_FINANCEIRO_EVO',
        'FILA_SATISFACAO_GGNET'
    ];

    if (!in_array($fila->nome, $filasPermitidas)) {
        continue;
    }

    $res = EntityLigacoesSip::getLigacoesByFilter(
        $dataInicio->format('Y-m-d'),
        $dataFim->format('Y-m-d'),
        $fila->nome
    );

    while ($row = $res->fetchObject(EntityLigacoesSip::class)) {

        $obLigacoes = EntityLigacoes::getLigacoesByUuid($row->uuid);
        // Evita duplicidade
        if ($obLigacoes instanceof EntityLigacoes) {
            continue;
        }

        $obLigacoes = new EntityLigacoes();
        $nota = null;
        if ($row->digit >= 1 && $row->digit <= 5) {
            $nota = $row->digit;
        }
        if ($row->queue_status != 'answered' && $row->queue_status != 'abandonned') {
            continue;
        }

        $obLigacoes->data = $row->start_stamp ?? null;
        $obLigacoes->tempo_fila = $row->queue_waiting_duration ?? 0;
        $obLigacoes->tempo_atendimento = (int) ($row->queue_call_duration ?? 0);
        $obLigacoes->responsavel = $row->queue_user_name ?? '';
        $obLigacoes->status = $row->queue_status ?? null;
        $obLigacoes->id_queue = $row->queue_id ?? null;
        $obLigacoes->fila = $row->queue_name ?? null;
        $obLigacoes->numero = $row->caller_id ?? null;
        $obLigacoes->uuid = $row->uuid ?? null;
        $obLigacoes->nota = $nota;

        $obLigacoes->cadastrar();
    }
}

$dataAtual = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));

echo "Ligações de: "
    . $dataInicio->format('d/m/Y') . " até "
    . $dataFim->format('d/m/Y')
    . " sincronizadas - "
    . $dataAtual->format('d/m/Y H:i') . "\n";