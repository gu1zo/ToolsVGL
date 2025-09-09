<?php

use \App\http\Response;
use \App\Controller\Ajax\Ajax;
use \App\Controller\Ajax\Graficos;
use \App\Controller\Ajax\GraficosCordialidade;

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
$obRouter->get('/ajax/graficos/notas', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoNotas($request));
    }
]);
$obRouter->get('/ajax/graficos/csat', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoCSAT($request));
    }
]);
$obRouter->get('/ajax/graficos/agentesPositivo', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoElogiosPorAgente($request));
    }
]);
$obRouter->get('/ajax/graficos/agentesNegativo', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoCriticasPorAgente($request));
    }
]);

$obRouter->get('/ajax/graficos/notasAno', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoLinhaNotas($request));
    }
]);

$obRouter->get('/ajax/graficos/mediaNotasAno', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoLinhaMediaNotas($request));
    }
]);





$obRouter->get('/ajax/graficos/notasCordialidade', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosCordialidade::getGraficoNotas($request));
    }
]);
$obRouter->get('/ajax/graficos/csatCordialidade', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosCordialidade::getGraficoCSAT($request));
    }
]);
$obRouter->get('/ajax/graficos/agentesPositivoCordialidade', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosCordialidade::getGraficoElogiosPorAgente($request));
    }
]);
$obRouter->get('/ajax/graficos/agentesNegativoCordialidade', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosCordialidade::getGraficoCriticasPorAgente($request));
    }
]);

$obRouter->get('/ajax/graficos/notasAnoCordialidade', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosCordialidade::getGraficoLinhaNotas($request));
    }
]);

$obRouter->get('/ajax/graficos/mediaNotasAnoCordialidade', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosCordialidade::getGraficoLinhaMediaNotas($request));
    }
]);