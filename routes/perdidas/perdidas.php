<?php

use \App\http\Response;
use \App\Controller\Perdidas\Perdidas;

$obRouter->get('/perdidas', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Perdidas::getPerdidas($request));
    }
]);

$obRouter->get('/perdidas/novo', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Perdidas::getNovaPerdida($request));
    }
]);

$obRouter->post('/perdidas/novo', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Perdidas::setNovaPerdida($request));
    }
]);