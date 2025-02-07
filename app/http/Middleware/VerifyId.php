<?php

namespace App\Http\Middleware;
use \App\Model\Entity\Evento as EntityEvento;

class VerifyId
{
    /**
     * Método responsável por executar o middleware
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, $next)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'] ?? '';

        if ($id == '') {
            $request->getRouter()->redirect('/evento/table?status=no-id&evento-status=todos');
            exit;
        }

        $obEvento = EntityEvento::getEventoById($id);

        if (!$obEvento instanceof EntityEvento) {
            $request->getRouter()->redirect('/evento/table?evento-status=todos');
            exit;
        }
        return $next($request);
    }
}