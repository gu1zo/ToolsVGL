<?php

use \App\http\Response;
use \App\Controller\Fila\Fila;

$obRouter->get('/fila', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Fila::getFila($request));
    }
]);

$obRouter->get('/fila/gestao', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Fila::getFilaGestor($request));
    }
]);

$obRouter->get('/fila/delete', [
    'middlewares' => [
        'required-login',
        'required-admin-fila'
    ],
    function ($request) {
        return new response(200, Fila::deleteFila($request));
    }
]);