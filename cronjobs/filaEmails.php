<?php

namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\FilaEmails as EntityFilaEmails;
use DateTime;
use DateTimeZone;

$limite = 150;
$results = EntityFilaEmails::getFilaEmails('status = "pendente"', 'id ASC', $limite);

$processes = [];
$pipesList = [];

// Intervalo entre disparos em microssegundos
$intervaloMicro = 330000; // 0.33 segundos

while ($obFilaEmails = $results->fetchObject(EntityFilaEmails::class)) {
    $cmd = 'php ' . escapeshellarg(__DIR__ . '/send_email.php') . ' ' .
        escapeshellarg($obFilaEmails->id);

    // Abre o processo e captura stdout/stderr
    $descriptorspec = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];

    $process = proc_open($cmd, $descriptorspec, $pipes);

    if (is_resource($process)) {
        $processes[] = $process;
        $pipesList[] = $pipes;
    }

    // Dorme antes de disparar o próximo (exceto no último)
    usleep($intervaloMicro);
}

// Ler saídas
foreach ($processes as $index => $process) {
    $stdout = stream_get_contents($pipesList[$index][1]);
    fclose($pipesList[$index][1]);

    $stderr = stream_get_contents($pipesList[$index][2]);
    fclose($pipesList[$index][2]);

    proc_close($process);

    // Exibir o que cada subprocesso imprimiu
    echo $stdout;
    if (!empty($stderr)) {
        echo "Erro: " . $stderr;
    }
}

// Registrar conclusão do lote
$data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
echo "Lote concluído em paralelo - " . $data->format('d/m/Y H:i') . "\n";