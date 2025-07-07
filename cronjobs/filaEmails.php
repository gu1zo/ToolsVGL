<?php

namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\FilaEmails as EntityFilaEmails;
use \App\Controller\Email\Email;
use DateTime;
use DateTimeZone;

$limite = 1000;

$results = EntityFilaEmails::getFilaEmails('status != "pendente"', 'id ASC', $limite);
$qtd = EntityFilaEmails::getFilaEmails('status != "pendente"', 'id ASC', $limite, 'COUNT(*) as qtd')->fetchObject()->qtd;
if ($qtd > 0) {
    while ($obFilaEmails = $results->fetchObject(EntityFilaEmails::class)) {
        $success = Email::sendFila($obFilaEmails->email, $obFilaEmails->body);
        if ($success) {
            $obFilaEmails->status = 'enviado';
            $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
            echo "Enviado e-mail: " . $obFilaEmails->email . "-" . $data->format('d/m/Y H:i:s') . "\n";
        } else {
            $obFilaEmails->status = 'erro';
        }
        $obFilaEmails->atualizar();
    }
    // Definir o fuso horário de Brasília
    $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    echo "Lote Enviado - " . $data->format('d/m/Y H:i') . "\n";
} else {
    $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
    echo "Nenhum Lote Ativo - " . $data->format('d/m/Y H:i') . "\n";
}