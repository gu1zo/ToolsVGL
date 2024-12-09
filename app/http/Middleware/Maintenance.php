<?php

namespace App\http\Middleware;

use App\http\Request;
use App\Http\Response;

class Maintenance
{

    /**
     * Método responsável por executar o middleware
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, $next)
    {
        //VERIFICA MANUTENÇÃO
        if (getenv('MAINTENANCE') == 'true') {
            throw new \Exception('Página em manutenção, tente novamente mais tarde', 200);
        }

        //Executa o próximo da fila
        return $next($request);
    }

}