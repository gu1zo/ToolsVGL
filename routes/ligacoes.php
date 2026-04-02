<?php

use \App\http\Response;
use \App\Controller\Ligacoes\Ligacoes;


$obRouter->get('/ligacoes', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Ligacoes::getLigacoes($request));
    }
]);

$obRouter->get('/ligacoes/table', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Ligacoes::getLigacoesTable($request));
    }
]);

$obRouter->get('/ligacoes/dash', [
    'middlewares' => [
    ],
    function ($request) {
        return new response(200, Ligacoes::renderDashboardChamadas($request));
    }
]);

$obRouter->get('/ligacoes/dash/agentes', [
    'middlewares' => [
    ],
    function ($request) {
        return new response(200, Ligacoes::renderDashboardAgentes($request));
    }
]);