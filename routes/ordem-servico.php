<?php

use \App\http\Response;
use \App\Controller\OrdensServico\OrdensServico;

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