<?php

namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Controller\Api\Api;
use \App\Controller\Api\EvolutionAPI;
use DateTime;
use DateTimeZone;


$message = Api::getMessage();
if (EvolutionAPI::sendMessage($message)) {
    $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo')); // Definir o fuso horário de Brasília
    echo "Mensagem Enviada - " . $data->format('d/m/Y H:i') . "\n";
}