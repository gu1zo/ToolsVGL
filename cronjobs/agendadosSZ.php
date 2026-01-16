<?php
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\Agendados as EntityAgendados;
use \App\Model\Entity\User as EntityUser;
use \App\Controller\Api\EvolutionAPI;
use App\Controller\Api\GoogleChatAPI;

$dataAgora = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format("Y-m-d H:i");
$mensagem = "";

$results = EntityAgendados::getAgendados(
    'status = "agendado" AND tipo = "digital" AND data <= "' . $dataAgora . '"'
);

while ($obAgendado = $results->fetchObject(EntityAgendados::class)) {
    $obUser = EntityUser::getUserById($obAgendado->id_usuario);

    $dataAgendada = (new DateTime($obAgendado->data))->format('d/m/Y H:i');

    $mensagem .= "⚠️ <b>HORÁRIO AGENDADO</b> ⚠️\n";
    $mensagem .= "Protocolo: <b>{$obAgendado->protocolo}</b>\n";
    $mensagem .= "Agendado por: <b>" . trim($obUser->nome) . "</b>\n";
    $mensagem .= "Agendado para: <b>{$dataAgendada}</b>\n\n";
}

$number = getenv('EVO_API_NUMBER_DIGITAL');

if (!empty($mensagem) && GoogleChatAPI::sendMessage(trim($mensagem), $number)) {
    $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    echo "Mensagem enviada - " . $data->format('d/m/Y H:i') . "\n";
}