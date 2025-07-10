<?php
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\FilaEmails as EntityFilaEmails;
use \App\Controller\Email\Email;

$id = $argv[1] ?? null;

if ($id) {
    $obFilaEmails = EntityFilaEmails::getFilaEmails('id = ' . (int) $id)->fetchObject(EntityFilaEmails::class);
    if ($obFilaEmails) {
        $success = Email::sendFila($obFilaEmails->email, $obFilaEmails->body);

        if ($success) {
            $obFilaEmails->status = 'enviado';
            $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
            echo "Enviado e-mail: " . $obFilaEmails->email . " - " . $data->format('d/m/Y H:i:s') . "\n";
        } else {
            $obFilaEmails->status = 'erro';
            echo "Falha ao enviar e-mail: " . $obFilaEmails->email . "\n";
        }
        $obFilaEmails->atualizar();
    } else {
        echo "E-mail ID $id não encontrado.\n";
    }
} else {
    echo "ID do e-mail não informado.\n";
}