<?php
namespace App\Controller\Ligacoes;

use \App\Utils\View;
use DateTime;

class Ligacoes
{
    public static function render($request)
    {
        $queryParams = $request->getQueryParams();
        $queue = $queryParams['queue'];
        return View::render('ligacoes/dashboard', [
            'queue' => $queue
        ]);
    }
}