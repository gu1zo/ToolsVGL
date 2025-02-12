<?php

namespace App\Http\Middleware;

class VerifyPonto
{
    /**
     * Método responsável por executar o middleware
     * @param Request $request
     * @param Closure $next
     * @return Response
     */
    public function handle($request, $next)
    {
        $postVars = $request->getPostVars();
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'] ?? '';
        $pontosAcesso = $postVars['pontosAcesso'] ?? '';
        $tipo = $postVars['tipo'];
        $pontosAcessoOpicinal = $postVars['pontos-acesso-opcional'] ?? '';

        if ($pontosAcesso == '' && $pontosAcessoOpicinal == '' && $tipo != 'backbone') {
            $request->getRouter()->redirect($request->getUri() . '?tipo=' . $tipo . '&status=no-pontos&id=' . $id);
            exit;
        }
        return $next($request);
    }
}