<?php

use \App\http\Response;
use \App\Controller\Massiva\Massiva;
use \App\Controller\Relatorios\Relatorio;

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

$obRouter->get('/massivas/form/edit', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Massiva::getEditMassiva($request));
    }
]);

$obRouter->post('/massivas/form/edit', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Massiva::setEditMassiva($request));
    }
]);

$obRouter->get('/massivas/delete', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Massiva::getDeleteMassiva($request));
    }
]);

$obRouter->post('/massivas/delete', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Massiva::setDeleteMassiva($request));
    }
]);

$obRouter->get('/massivas/relatorios', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Relatorio::getMassivasCSV($request));
    }
]);