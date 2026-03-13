<?php

use \App\http\Response;
use \App\Controller\Ligacoes\Ligacoes;


$obRouter->get('/ligacoes/dash', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Ligacoes::renderDashboardChamadas($request));
    }
]);

$obRouter->get('/ligacoes/dash/agentes', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Ligacoes::renderDashboardAgentes($request));
    }
]);