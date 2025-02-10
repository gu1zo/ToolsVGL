<?php

namespace App\Http\Middleware;

use \App\Utils\View;
use \App\Model\Entity\Evento as EntityEvento;
class VerifyProtocol
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
        $postVars = $request->getPostVars();
        $id = $queryParams['id'] ?? '';
        $rota = '?verified=true';
        $verified = isset($queryParams['verified']) ? $queryParams['verified'] : false;
        if ($id != '') {
            $rota = "?id=" . $id . "&verified=true";
            $obEvento = EntityEvento::getEventoById($id);

            if ($postVars['protocolo'] == $obEvento->protocolo) {
                $verified = true;
            }
        }
        if ($verified == false) {
            $itens = '';

            $obEvento = EntityEvento::getEventoByProtocol($postVars['protocolo']);
            if ($obEvento instanceof EntityEvento && $id != $obEvento->id) {
                foreach ($postVars as $item => $value) {
                    if ($item != 'pontosAcesso') {
                        $itens .= View::render('eventos/middleware/item', [
                            'item' => $item,
                            'value' => $value
                        ]);
                    }
                }
                foreach ($postVars['pontosAcesso'] as $ponto) {
                    $itens .= View::render('eventos/middleware/item', [
                        'item' => 'pontosAcesso[]',
                        'value' => $ponto
                    ]);
                }
                echo View::render('eventos/middleware/duplicado', [
                    'itens' => $itens,
                    'protocolo' => $obEvento->protocolo,
                    'status' => $obEvento->status,
                    'rota' => $rota,
                    'tipo' => $postVars['tipo']
                ]);
                exit;
            }
        }
        return $next($request);

    }
}