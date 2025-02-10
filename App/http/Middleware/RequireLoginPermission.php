<?php

namespace App\Http\Middleware;

use \App\Session\Login\Login as SessionLogin;

class RequireLoginPermission
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
        $id = $queryParams['id'];        
        if(!SessionLogin::isAdmin() && SessionLogin::getId() != $id){
            $request->getRouter()->redirect('/usuario?status=no-permission');
            exit;
        }
        return $next($request);
    }
}