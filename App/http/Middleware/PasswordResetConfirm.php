<?php

namespace App\Http\Middleware;

use \App\Session\Login as SessionLogin;

class PasswordResetConfirm
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
        $postVars = $request->getPostVars();
        $queryParams = $request->getQueryParams();
        $token = $queryParams['token'];

        if ($postVars['senha'] != $postVars['senha-confirma']) {
            $request->getRouter()->redirect('/recuperacao?token=' . $token . '&status=password-error');
            exit;
        }

        //die('Não está logado');
        return $next($request);
    }
}