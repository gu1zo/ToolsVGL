<?php
namespace App\Controller\Ligacoes;

use \App\Utils\View;
use DateTime;

class Ligacoes
{
    public static function renderDashboardChamadas($request)
    {
        $queryParams = $request->getQueryParams();
        $queue = $queryParams['queue'];
        return View::render('ligacoes/dashboard', [
            'queue' => $queue
        ]);
    }

    public static function renderDashboardAgentes($request)
    {
        $queryParams = $request->getQueryParams();
        $queue = $queryParams['queue'];
        return View::render('ligacoes/dashboard-agentes', [
            'queue' => $queue
        ]);
    }
}