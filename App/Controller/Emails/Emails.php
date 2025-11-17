<?php
namespace App\Controller\Emails;

use \App\Utils\View;
use \App\Utils\Alert;
use \App\Controller\Pages\Page;
use \App\Model\Entity\FilaEmails as EntityFilaEmails;
use DateTime;

class Emails extends Page
{
    public static function getNovoEmail($request)
    {
        $content = View::render('envio-emails/form', [
            'title' => 'Envio E-mails',
            'status' => self::getStatus($request)
        ]);

        return Page::getPage('E-mails > ToolsVGL', $content);
    }

    public static function setNovoEmail($request)
    {
        $titulo = $_POST['titulo'];
        $htmlTmp = $_FILES['arquivo_html']['tmp_name'];
        $csvTmp = $_FILES['arquivo_csv']['tmp_name'];

        // Pasta onde será salvo (cria se não existir)
        $pastaDestino = __DIR__ . "/../../../resources/view/emails/";

        // Gera nome único
        $novoNome = uniqid('body_', true) . '.html';
        // Move o arquivo HTML
        if (move_uploaded_file($htmlTmp, $pastaDestino . $novoNome)) {
            $fileName = $novoNome;
        } else {
            return false;
        }

        // Abre e lê o CSV
        if (($handle = fopen($csvTmp, 'r')) !== false) {
            // Lê a primeira linha (header) e ignora
            fgetcsv($handle, 1000, ',');

            while (($linha = fgetcsv($handle, 1000, ',')) !== false) {
                $email = trim($linha[0]);

                // Valida o e-mail
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $obFilaEmail = new EntityFilaEmails;
                    $obFilaEmail->titulo = $titulo;
                    $obFilaEmail->email = $email;
                    $obFilaEmail->body = $fileName;

                    $obFilaEmail->cadastrar();
                }
            }
            fclose($handle);
        } else {
            return false;
        }

        $request->getRouter()->redirect('/emails?status=send');
        exit;
    }

    public static function getEmailsTable($request)
    {
        $content = View::render('envio-emails/ver-emails', [
            'title' => 'E-mails',
            'status' => self::getStatus($request),
            'itens' => self::getSelectItens()
        ]);

        return self::getPage('E-mails > ToolsVGL', $content);
    }

    public static function setEmailsTable($request)
    {
        $content = View::render('envio-emails/table', [
            'itens' => self::getTableItens($request)
        ]);

        return self::getPage('E-mails > ToolsVGL', $content);
    }

    private static function getTableItens($request)
    {
        $postVars = $request->getPostVars();
        $titulo = $postVars['titulo'];
        $itens = '';
        $results = EntityFilaEmails::getFilaEmails('titulo = "' . $titulo . '"');
        while ($obFilaEmails = $results->fetchObject(EntityFilaEmails::class)) {

            $itens .= View::render('envio-emails/table-item', [
                'email' => $obFilaEmails->email,
                'status' => ucfirst($obFilaEmails->status)
            ]);
        }
        return $itens;
    }

    private static function getSelectItens()
    {
        $itens = '';
        $results = EntityFilaEmails::getFilaEmails(null, null, null, '*', 'titulo');
        while ($obFilaEmails = $results->fetchObject(EntityFilaEmails::class)) {
            $itens .= View::render('envio-emails/item', [
                'titulo' => $obFilaEmails->titulo
            ]);
        }
        return $itens;
    }


    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'send':
                return Alert::getSuccess('E-mails cadastrados na fila com sucesso!');
        }
        return '';
    }
}