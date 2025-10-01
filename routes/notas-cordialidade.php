<?php

use \App\http\Response;
use \App\Controller\Notas\NotasResolutividade;
use \App\Controller\Relatorios\Relatorio;

$obRouter->get('/notas-cordialidade', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, NotasResolutividade::getNotas($request));
    }
]);

$obRouter->get('/notas-cordialidade/table', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, NotasResolutividade::getNotasTable($request));
    }
]);

$obRouter->get('/notas-cordialidade/delete', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, NotasResolutividade::getDeleteNota($request));
    }
]);

$obRouter->post('/notas-cordialidade/delete', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, NotasResolutividade::setDeleteNotasByGroup($request));
    }
]);

$obRouter->get('/notas-cordialidade/relatorios', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Relatorio::getNotasResolutividadeCSV($request));
    }
]);

$obRouter->get('/notas-cordialidade/graficos', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Relatorio::getGraficosCordialidade($request));
    }
]);