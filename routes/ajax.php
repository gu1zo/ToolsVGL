<?php

use \App\http\Response;
use \App\Controller\Ajax\Ajax;

$obRouter->get('/ajax/agendados', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Ajax::getAgendados($request));
    }
]);

$obRouter->post('/ajax/agendados', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Ajax::setAgendados($request));
    }
]);
$obRouter->post('/ajax/agendados/excluir', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Ajax::concluirAgendamento($request));
    }
]);

$obRouter->get('/ajax/fila', [
    'middlewares' => [
        'required-login'
    ],
    function () {
        return new response(200, Ajax::getFila());
    }
]);

$obRouter->get('/ajax/fila/usuario', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Ajax::getFilaUser($request));
    }
]);

$obRouter->post('/ajax/fila/entrar', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Ajax::entrarFila($request));
    }
]);

$obRouter->post('/ajax/fila/sair', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Ajax::sairFila($request));
    }
]);
$obRouter->post('/ajax/fila/passar', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Ajax::passarVez($request));
    }
]);