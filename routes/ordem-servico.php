<?php

use \App\http\Response;
use \App\Controller\OrdensServico\OrdensServico;
use \App\Controller\Relatorios\Relatorio;

$obRouter->get('/os/form', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, OrdensServico::getOs($request));
    }
]);

$obRouter->get('/os/table', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, OrdensServico::getOsTable($request));
    }
]);

$obRouter->get('/os', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, OrdensServico::getOsDetails($request));
    }
]);

$obRouter->post('/os/avaliar', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, OrdensServico::setAvaliacao($request));
    }
]);
$obRouter->get('/os/graficos', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Relatorio::getGraficosOs($request));
    }
]);