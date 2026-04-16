<?php

use \App\http\Response;
use \App\Controller\Ajax\Ajax;
use \App\Controller\Ajax\Graficos;
use \App\Controller\Ajax\GraficosUra;

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
$obRouter->post('/ajax/fila/pausar', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Ajax::pausarFila($request));
    }
]);
$obRouter->post('/ajax/fila/despausar', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Ajax::despausarFila($request));
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

$obRouter->get('/ajax/graficos/mediaAgentes', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getMediaNotasPorAgente($request));
    }
]);






$obRouter->get('/ajax/graficos/notasUra', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosUra::getGraficoNotas($request));
    }
]);
$obRouter->get('/ajax/graficos/csatUra', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosUra::getGraficoCSAT($request));
    }
]);
$obRouter->get('/ajax/graficos/agentesPositivoUra', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosUra::getGraficoElogiosPorAgente($request));
    }
]);
$obRouter->get('/ajax/graficos/agentesNegativoUra', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosUra::getGraficoCriticasPorAgente($request));
    }
]);

$obRouter->get('/ajax/graficos/mediaAgentesUra', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosUra::getMediaNotasPorAgente($request));
    }
]);
$obRouter->get('/ajax/graficos/notasAnoUra', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosUra::getGraficoLinhaNotas($request));
    }
]);
$obRouter->get('/ajax/graficos/mediaNotasAnoUra', [
    'middlewares' => [],
    function ($request) {
        return new response(200, GraficosUra::getGraficoLinhaMediaNotas($request));
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

$obRouter->get('/ajax/massivas/graficoMassivasClientes', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoPizzaClientesPorRegional($request));
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


$obRouter->get('/ajax/massivas/graficoMassivasHistClientes', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Graficos::getGraficoLinhaClientes($request));
    }
]);


$obRouter->get('/ajax/ligacoes/dash', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Ajax::getDadosFila($request));
    }
]);

$obRouter->get('/ajax/ligacoes/dash/agentes', [
    'middlewares' => [],
    function ($request) {
        return new response(200, Ajax::getDadosAgentesFila($request));
    }
]);