<?php

namespace App\Http\Middleware;

class RequireTipo
{
    /**
     * Método responsável por executar o middleware
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, $next)
    {
        //VERIFICA SE O TIPO ESTÁ SETADO
        $queryParams = $request->getQueryParams();
        if (isset($queryParams['tipo'])) {
            $tipo = $queryParams['tipo'];
        }

        if (!isset($tipo) || $tipo == "") {
            $request->getRouter()->redirect('/evento/novoTipo');
            exit;
        }
        return $next($request);
    }
}