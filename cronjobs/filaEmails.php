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

// Configuração
$intervaloMicro = 330000; // 0.33s = 3 envios por segundo
$maxProcessosSimultaneos = 5;

while ($obFilaEmails = $results->fetchObject(EntityFilaEmails::class)) {
    // Enquanto já tiver muitos processos ativos, aguarda
    while (count($processes) >= $maxProcessosSimultaneos) {
        foreach ($processes as $index => $proc) {
            $status = proc_get_status($proc);
            if (!$status['running']) {
                // Leitura da saída
                $stdout = stream_get_contents($pipesList[$index][1]);
                fclose($pipesList[$index][1]);
                $stderr = stream_get_contents($pipesList[$index][2]);
                fclose($pipesList[$index][2]);
                proc_close($proc);

                // Exibir
                echo $stdout;
                if (!empty($stderr)) {
                    echo "Erro: " . $stderr;
                }

                // Remove da lista
                unset($processes[$index]);
                unset($pipesList[$index]);
            }
        }
        usleep(100000); // espera 0.1s antes de verificar de novo
    }

    // Dispara novo processo
    $cmd = 'php ' . escapeshellarg(__DIR__ . '/send_email.php') . ' ' .
        escapeshellarg($obFilaEmails->id);

    $descriptorspec = [
        1 => ['pipe', 'w'],
        2 => ['pipe', 'w']
    ];

    $process = proc_open($cmd, $descriptorspec, $pipes);

    if (is_resource($process)) {
        $processes[] = $process;
        $pipesList[] = $pipes;
    }

    usleep($intervaloMicro);
}

// Aguarda todos os últimos processos terminarem
foreach ($processes as $index => $process) {
    $stdout = stream_get_contents($pipesList[$index][1]);
    fclose($pipesList[$index][1]);
    $stderr = stream_get_contents($pipesList[$index][2]);
    fclose($pipesList[$index][2]);
    proc_close($process);

    echo $stdout;
    if (!empty($stderr)) {
        echo "Erro: " . $stderr;
    }
}

// Registrar conclusão
$data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
echo "Lote concluído em paralelo - " . $data->format('d/m/Y H:i') . "\n";