<?php

namespace App\Controller\Email;

use \App\Utils\View;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Email
{

    public static function send($tipo, $vars, $cliente)
    {
        $assunto = isset($vars['assunto']) ? $vars['assunto'] : 'Atualizações Eventos na Rede';

        try {
            $mail = new PHPMailer(true);

            $mail->isSMTP();

            //$mail->SMTPDebug = SMTP::DEBUG_SERVER;
            //$mail->Host = 'smtp.gegnet.com.br';
            $mail->Host = getenv('SMTP_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = getenv('SMTP_USER');
            $mail->Password = getenv('SMTP_PASS');
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = 465;
            $mail->CharSet = 'UTF-8';

            $mail->setFrom(getenv('SMTP_USER')); #remetente do email
            $mail->isHTML(true); #define o email com formato html
            $mail->Subject = $assunto; #define o assunto do email
            $mail->Body = self::getEmailBody($tipo, $vars, $cliente);

            $mail->clearAddresses(); // Limpa destinatários anteriores
            $mail->addAddress($cliente['e_mail']);
            $mail->send();
        } catch (Exception $e) {
            echo '<pre>';
            print_r($e);
            echo '</pre>';
            exit;
        }

    }

    private static function getEmailBody($tipo, $vars, $cliente)
    {
        switch ($tipo) {

            case 'atualizacao':
                return View::render('emails/atualizacao', [
                    'nome' => $cliente['nome']
                ]);
                break;
            case 'cancelada':
                return View::render('emails/cancelada', [
                    'nome' => $cliente['nome']
                ]);
                break;
            case 'emergencial':
                $horarioInicial = strtotime($vars['horario-inicial']);
                $horarioPrevisto = strtotime($vars['horario-previsto']);
                return View::render('emails/emergencial', [
                    'nome' => $cliente['nome'],
                    'horario-inicial' => date('d/m/Y H:i', timestamp: $horarioInicial),
                    'horario-previsto' => date('d/m/Y H:i', $horarioPrevisto)
                ]);
                break;
            case 'incidente':
                $horarioInicial = strtotime($vars['horario-inicial']);
                return View::render('emails/incidente', [
                    'nome' => $cliente['nome'],
                    'horario-inicial' => date('d/m/Y H:i', timestamp: $horarioInicial)
                ]);
                break;
            case 'preventiva':
                return View::render('emails/atualizacao', [
                    'nome' => $cliente['nome']
                ]);
                break;
            case 'trocar-senha':
                return View::render('emails/troca-senha', [
                    'url' => $vars['url'],
                    'nome' => $cliente['nome']
                ]);
                break;
        }
    }
}