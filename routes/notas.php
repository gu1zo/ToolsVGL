<?php

use \App\http\Response;
use \App\Controller\Notas\Notas;

$obRouter->get('/notas', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Notas::getNotas($request));
    }
]);

$obRouter->get('/notas/table', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Notas::getNotasTable($request));
    }
]);