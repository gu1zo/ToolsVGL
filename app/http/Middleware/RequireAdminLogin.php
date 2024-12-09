<?php

namespace App\Http\Middleware;

use \App\Session\Admin\Login as SesisonAdminLogin;

class RequireAdminLogin
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
        if (!SesisonAdminLogin::isLogged()) {
            //die('Está logado');
            $request->getRouter()->redirect('/admin/login');
            exit;
        }
        //die('Não está logado');
        return $next($request);
    }
}