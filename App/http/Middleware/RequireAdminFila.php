<?php

namespace App\Http\Middleware;

use \App\Session\Login\Login as SessionLogin;

class RequireAdminFila
{
    /**
     * Método responsável por executar o middleware
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, $next)
    {
        if (!SessionLogin::isAdmin()) {
            $request->getRouter()->redirect('/fila/gestao?status=no-permission');
            exit;
        }
        return $next($request);
    }
}