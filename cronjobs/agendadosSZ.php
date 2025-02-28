<?php
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\Agendados as EntityAgendados;
use \App\Model\Entity\User as EntityUser;
use \App\Controller\Api\EvolutionAPI;

$data = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format("Y-m-d H:i");
$mensagem = "";
$results = EntityAgendados::getAgendados('status = "agendado" AND tipo = "digital" AND data <= "' . $data . '"');

while ($obAgendado = $results->fetchObject(EntityAgendados::class)) {
    $obUser = EntityUser::getUserById($obAgendado->id_usuario);

    $data = (new DateTime($obAgendado->data))->format('d/m/Y H:i');

    $mensagem .= "⚠️ *HORÁRIO AGENDADO* ⚠️\n";
    $mensagem .= "_Protocolo:_ *" . $obAgendado->protocolo . "* \n";
    $mensagem .= "_Agendado por:_ *" . $obUser->nome . "* \n";
    $mensagem .= "_Agendado para:_ *" . $data . "* \n\n";
}
$number = getenv('EVO_API_NUMBER_DIGITAL');
/*
if (EvolutionAPI::sendMessage($mensagem, $number)) {
    $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    echo "Mensagem enviada - " . $data->format('d/m/Y H:i') . "\n";
}*/