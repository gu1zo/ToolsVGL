<?php

namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\FilaEmails as EntityFilaEmails;
use DateTime;
use DateTimeZone;

$limite = 500;
$results = EntityFilaEmails::getFilaEmails('status = "pendente"', 'id ASC', $limite);

$processes = [];
$pipesList = [];

// Dispara subprocessos
while ($obFilaEmails = $results->fetchObject(EntityFilaEmails::class)) {
    $cmd = 'php ' . escapeshellarg(__DIR__ . '/send_email.php') . ' ' .
        escapeshellarg($obFilaEmails->id);

    // Abre o processo e captura stdout
    $descriptorspec = [
        1 => ['pipe', 'w'], // stdout
        2 => ['pipe', 'w']  // stderr (opcional)
    ];

    $process = proc_open($cmd, $descriptorspec, $pipes);

    if (is_resource($process)) {
        $processes[] = $process;
        $pipesList[] = $pipes;
    }
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