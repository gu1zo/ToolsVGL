<?php
namespace App\Controller\Agendados;

use \App\Utils\View;
use \App\Controller\Pages\Page;
use \App\Session\Login\Login;
use DateTime;
use DateTimeZone;
use \App\Model\Entity\Agendados as EntityAgendados;
use \App\Model\Entity\User as EntityUser;

class Agendados extends Page
{

    public static function getAgendados($request)
    {
        $queryParams = $request->getQueryParams();
        $tipo = ucfirst($queryParams['tipo']);

        $content = View::render('agendados/form', [
            'tipo' => $queryParams['tipo'],
            'id_usuario' => Login::getId()
        ]);


        $content = View::render('agendados/table', [
            'tipo' => $tipo,
            'content' => $content
        ]);

        return parent::getPage('Agendados > ToolsVGL', $content);
    }

    public static function getMessage($tipo)
    {
        $mensagem = '';

        $results = EntityAgendados::getAgendados(
            'status = "agendado" AND tipo = "' . $tipo . '"',
            'data ASC'
        );

        while ($obAgendado = $results->fetchObject(EntityAgendados::class)) {
            $obUser = EntityUser::getUserById($obAgendado->id_usuario);

            $data = (new DateTime($obAgendado->data))->format('d/m/Y H:i');

            $mensagem .= "<p>";
            $mensagem .= "<b>Data:</b> {$data}<br>";
            $mensagem .= "<b>Protocolo:</b> {$obAgendado->protocolo}<br>";
            $mensagem .= "<b>Usuário:</b> " . trim($obUser->nome);
            $mensagem .= "<br><br>";
        }

        return $mensagem;
    }
}