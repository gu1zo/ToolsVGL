<?php

use \App\http\Response;
use \App\Controller\Notas\NotasResolutividade;
use \App\Controller\Relatorios\Relatorio;

$obRouter->get('/massivas', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Perdidas::getPerdidas($request));
    }
]);

$obRouter->get('/massivas/form', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Perdidas::getNovaPerdida($request));
    }
]);

$obRouter->post('/massivas/form', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Perdidas::setNovaPerdida($request));
    }
]);