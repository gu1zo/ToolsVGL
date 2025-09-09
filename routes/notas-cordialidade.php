<?php

use \App\http\Response;
use \App\Controller\Notas\NotasCordialidade;
use \App\Controller\Relatorios\Relatorio;

$obRouter->get('/notas-cordialidade', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, NotasCordialidade::getNotas($request));
    }
]);

$obRouter->get('/notas-cordialidade/table', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, NotasCordialidade::getNotasTable($request));
    }
]);

$obRouter->get('/notas-cordialidade/delete', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, NotasCordialidade::getDeleteNota($request));
    }
]);

$obRouter->post('/notas-cordialidade/delete', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, NotasCordialidade::setDeleteNotasByGroup($request));
    }
]);

$obRouter->get('/notas-cordialidade/relatorios', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Relatorio::getNotasCSV($request));
    }
]);

$obRouter->get('/notas-cordialidade/graficos', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Relatorio::getGraficos($request));
    }
]);