<?php

namespace App\Http\Middleware;

use \App\Session\Login\Login as SessionLogin;

class RequireLogout
{
    /**
     * Método responsável por executar o middleware
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, $next)
    {
        //VERIFICA SE USER ESTÁ LOGADO
        if (SessionLogin::isLogged()) {
            //die('Está logado');
            $request->getRouter()->redirect('/');
            exit;
        }
        //die('Não está logado');
        return $next($request);
    }
}