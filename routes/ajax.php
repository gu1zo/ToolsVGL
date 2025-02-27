<?php

use \App\http\Response;
use \App\Controller\Ajax\Ajax;

$obRouter->get('/ajax/agendados', [
    'middlewares' => [
    ],
    function ($request) {
        return new response(200, Ajax::getAgendados($request));
    }
]);

$obRouter->post('/ajax/agendados', [
    'middlewares' => [
    ],
    function ($request) {
        return new response(200, Ajax::setAgendados($request));
    }
]);
$obRouter->post('/ajax/agendados/excluir', [
    'middlewares' => [
    ],
    function ($request) {
        return new response(200, Ajax::concluirAgendamento($request));
    }
]);

$obRouter->get('/ajax/fila', [
    'middlewares' => [
    ],
    function () {
        return new response(200, Ajax::getFila());
    }
]);

$obRouter->get('/ajax/fila/usuario', [
    'middlewares' => [
    ],
    function ($request) {
        return new response(200, Ajax::getFilaUser($request));
    }
]);

$obRouter->post('/ajax/fila/entrar', [
    'middlewares' => [
    ],
    function ($request) {
        return new response(200, Ajax::entrarFila($request));
    }
]);

$obRouter->post('/ajax/fila/sair', [
    'middlewares' => [
    ],
    function ($request) {
        return new response(200, Ajax::sairFila($request));
    }
]);
$obRouter->post('/ajax/fila/passar', [
    'middlewares' => [
    ],
    function ($request) {
        return new response(200, Ajax::passarVez($request));
    }
]);