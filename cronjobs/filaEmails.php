<?php
$maxParallel = 10; // limite de processos ativos
$processes = [];
$pipesList = [];
$queue = [];

// monta a fila
while ($obFilaEmails = $results->fetchObject(EntityFilaEmails::class)) {
    $queue[] = $obFilaEmails->id;
}

while (!empty($queue) || !empty($processes)) {
    // inicia novos processos até atingir o limite
    while (count($processes) < $maxParallel && !empty($queue)) {
        $id = array_shift($queue);
        $cmd = 'php ' . escapeshellarg(__DIR__ . '/send_email.php') . ' ' . escapeshellarg($id);
        $descriptorspec = [
            1 => ['pipe', 'w'],
            2 => ['pipe', 'w']
        ];
        $process = proc_open($cmd, $descriptorspec, $pipes);

        if (is_resource($process)) {
            $processes[] = $process;
            $pipesList[] = $pipes;
        }
    }

    // verifica processos concluídos
    foreach ($processes as $index => $process) {
        $status = proc_get_status($process);
        if (!$status['running']) {
            $stdout = stream_get_contents($pipesList[$index][1]);
            fclose($pipesList[$index][1]);

            $stderr = stream_get_contents($pipesList[$index][2]);
            fclose($pipesList[$index][2]);

            proc_close($process);

            echo $stdout;
            if (!empty($stderr)) {
                echo "Erro: " . $stderr;
            }

            unset($processes[$index]);
            unset($pipesList[$index]);
        }
    }

    // evita loop apertado
    usleep(100000); // 0.1 segundo
}