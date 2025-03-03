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
        $data = (new DateTime('now', new DateTimeZone('America/Sao_Paulo')))->format("Y-m-d H:i");
        $results = EntityAgendados::getAgendados('status = "agendado" AND tipo = "' . $tipo . '"', 'data ASC');
        $mensagem = "🕑 *AGENDADOS CSA-" . strtoupper($tipo) . "* 🕑\n\n";
        while ($obAgendado = $results->fetchObject(EntityAgendados::class)) {
            $obUser = EntityUser::getUserById($obAgendado->id_usuario);

            $data = (new DateTime($obAgendado->data))->format('d/m/Y H:i');
            $mensagem .= "_Data:_ *" . $data . "* ";
            $mensagem .= "_Protocolo:_ *" . $obAgendado->protocolo . "* ";
            $mensagem .= "_Usuário:_ *" . trim($obUser->nome) . "* \n";

        }

        return $mensagem;
    }
}