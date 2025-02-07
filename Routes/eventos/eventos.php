<?php

use \App\http\Response;
use \App\Controller\Evento;

$obRouter->get('/evento/novoTipo', [
    'middlewares' => [
        'required-login'
    ],
    function () {
        return new response(200, Evento\Evento::getNovoEventoTipo());
    }
]);
$obRouter->get('/evento/novo', [
    'middlewares' => [
        'required-login',
        'required-tipo'
    ],
    function ($request) {
        return new response(200, Evento\Evento::getNovoEvento($request));
    }
]);
$obRouter->post('/evento/novo', [
    'middlewares' => [
        'required-login',
        'verify-protocol',
        'verify-ponto'
    ],
    function ($request) {
        return new response(200, Evento\Evento::setNovoEvento($request));
    }
]);

$obRouter->get('/evento/edit', [
    'middlewares' => [
        'required-login',
        'verify-id'
    ],
    function ($request) {
        return new response(200, Evento\Evento::getEditEvento($request));
    }
]);

$obRouter->post('/evento/edit', [
    'middlewares' => [
        'required-login',
        'verify-protocol',
        'verify-ponto',
        'verify-id'
    ],
    function ($request) {
        return new response(200, Evento\Evento::setEditEvento($request));
    }
]);

$obRouter->get('/evento/table', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Evento\Evento::getTable($request));
    }
]);

$obRouter->get('/evento/reagendar', [
    'middlewares' => [
        'required-login',
        'verify-id'
    ],
    function ($request) {
        return new response(200, Evento\Evento::getReagendar($request));
    }
]);

$obRouter->post('/evento/reagendar', [
    'middlewares' => [
        'required-login',
        'verify-id'
    ],
    function ($request) {
        return new response(200, Evento\Evento::setReagendar($request));
    }
]);

$obRouter->get('/evento/aprovar', [
    'middlewares' => [
        'required-login',
        'verify-id'
    ],
    function ($request) {
        return new response(200, Evento\Evento::aprovar($request));
    }
]);

$obRouter->get('/evento/executar', [
    'middlewares' => [
        'required-login',
        'verify-id'
    ],
    function ($request) {
        return new response(200, Evento\Evento::executar($request));
    }
]);

$obRouter->get('/evento/concluir', [
    'middlewares' => [
        'required-login',
        'verify-id'
    ],
    function ($request) {
        return new response(200, Evento\Evento::getConcluir($request));
    }
]);

$obRouter->post('/evento/concluir', [
    'middlewares' => [
        'required-login',
        'verify-id'
    ],
    function ($request) {
        return new response(200, Evento\Evento::setConcluir($request));
    }
]);

$obRouter->get('/evento/email', [
    'middlewares' => [
        'required-login',
        'verify-id'
    ],
    function ($request) {
        return new response(200, Evento\Evento::getEmail($request));
    }
]);

$obRouter->post('/evento/email', [
    'middlewares' => [
        'required-login',
        'verify-id'
    ],
    function ($request) {
        return new response(200, Evento\Evento::setEmail($request));
    }
]);
/*
$obRouter->get('/evento/sync', [
    'middlewares' => [
        'required-login'
    ],
    function () {
        return new response(200, Evento\SyncBanco::syncbanco());
    }
]);*/