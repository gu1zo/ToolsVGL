<?php

use \App\http\Response;
use \App\Controller\Notas\NotasUra;
use \App\Controller\Relatorios\Relatorio;

$obRouter->get('/notas-ura', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, NotasUra::getNotas($request));
    }
]);

$obRouter->get('/notas-ura/table', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, NotasUra::getNotasTable($request));
    }
]);

$obRouter->get('/notas-ura/delete', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, NotasUra::getDeleteNota($request));
    }
]);

$obRouter->post('/notas-ura/delete', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, NotasUra::setDeleteNotasByGroup($request));
    }
]);

$obRouter->get('/notas-ura/relatorios', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Relatorio::getNotasUraCSV($request));
    }
]);

$obRouter->get('/notas-ura/media/relatorios', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Relatorio::getMediaNotasPorAgenteUraCSV($request));
    }
]);

$obRouter->get('/notas-ura/graficos', [
    'middlewares' => [
        'required-login',
        'required-admin-nota'
    ],
    function ($request) {
        return new response(200, Relatorio::getGraficosUra($request));
    }
]);