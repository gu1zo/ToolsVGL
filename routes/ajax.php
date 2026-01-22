<?php

use \App\http\Response;
use \App\Controller\Ajax\Ajax;
use \App\Controller\Ajax\Graficos;
use \App\Controller\Ajax\GraficosResolutividade;

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
        return new response(200, GraficosResolutividade::getGraficoNotas($request));
    }
]);
$obRouter->get('/ajax/graficos/csatCordialidade', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosResolutividade::getGraficoResolutividade($request));
    }
]);
$obRouter->get('/ajax/graficos/agentesPositivoCordialidade', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosResolutividade::getGraficoResolutividadePorAgente($request));
    }
]);
$obRouter->get('/ajax/graficos/agentesNegativoCordialidade', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosResolutividade::getGraficoNResolutividadePorAgente($request));
    }
]);

$obRouter->get('/ajax/graficos/notasAnoCordialidade', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosResolutividade::getGraficoLinhaResolutividadeIndividual($request));
    }
]);

$obRouter->get('/ajax/graficos/mediaNotasAnoCordialidade', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosResolutividade::getGraficoLinhaResolutividade($request));
    }
]);

$obRouter->get('/ajax/tecnicos', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Ajax::getTecnicos($request));
    }
]);

$obRouter->get('/ajax/os/router', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Ajax::getRoteador($request));
    }
]);

$obRouter->get('/ajax/os/onu', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Ajax::getOnu($request));
    }
]);

$obRouter->get('/ajax/os/graficoNotas', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoNotasOs($request));
    }
]);

$obRouter->get('/ajax/os/graficoTecnicosPositividade', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoTecnicosPositividade($request));
    }
]);

$obRouter->get('/ajax/os/graficoTecnicosNegatividade', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoTecnicosNegatividade($request));
    }
]);

$obRouter->get('/ajax/os/graficoLinhaOs', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoLinhaOs($request));
    }
]);

$obRouter->get('/ajax/massivas/graficoMassivasRegionais', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoRegionais($request));
    }
]);

$obRouter->get('/ajax/massivas/graficoMassivasTipos', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficosTipo($request));
    }
]);

$obRouter->get('/ajax/massivas/graifcoMassivasHistRegionais', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoLinhaRegionais($request));
    }
]);

$obRouter->get('/ajax/massivas/graficoMassivasHistTipos', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoLinhaTipos($request));
    }
]);