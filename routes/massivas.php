<?php

use \App\http\Response;
use \App\Controller\Massiva\Massiva;

$obRouter->get('/massivas', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Massiva::getMassivas($request));
    }
]);

$obRouter->get('/massivas/form', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Massiva::getNovaMassiva($request));
    }
]);

$obRouter->post('/massivas/form', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Massiva::setNovaMassiva($request));
    }
]);