<?php
namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';


use App\Model\Entity\Queues as EntityQueues;
use App\Model\Entity\Sippulse\cdrs_full as EntityLigacoesSip;
use DateTime;
use DateTimeZone;

$resultados = EntityLigacoesSip::getFilas();

while ($row = $resultados->fetchObject(EntityLigacoesSip::class)) {
    $id = $row->queue_id ?? null;
    $nome = $row->queue_name ?? null;
    if ($id == null || $nome == null) {
        continue;
    }

    $obQueue = EntityQueues::getQueueById($id);
    if ($obQueue instanceof EntityQueues) {
        $obQueue->nome = $nome;
        $obQueue->atualizar();
        continue;
    } else {

        $obQueue = new EntityQueues();
        $obQueue->id = $id;
        $obQueue->nome = $nome;
        $obQueue->cadastrar();
    }
}

$dataAtual = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
echo "Filas sincronizadas - " . $dataAtual->format('d/m/Y H:i') . "\n";