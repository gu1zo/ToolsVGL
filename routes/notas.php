<?php

use \App\http\Response;
use \App\Controller\Notas\Notas;
use \App\Controller\Relatorios\Relatorio;

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
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Notas::getNotasTable($request));
    }
]);

$obRouter->get('/notas/delete', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Notas::getDeleteNota($request));
    }
]);

$obRouter->post('/notas/delete', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Notas::setDeleteNota($request));
    }
]);

$obRouter->get('/notas/relatorios', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Relatorio::getNotasCSV($request));
    }
]);