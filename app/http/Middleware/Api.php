<?php

namespace App\http\Middleware;

use App\http\Request;
use App\Http\Response;

class Api
{

    /**
     * Método responsável por executar o middleware
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, $next)
    {
        $request->getRouter()->setContentType('application/json');
        //Executa o próximo da fila
        return $next($request);
    }

}