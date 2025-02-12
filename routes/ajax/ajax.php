<?php

use \App\http\Response;
use \App\Controller\Ajax\Ajax;
use \App\Controller\Ajax\Graficos;

$obRouter->get('/ajax/pontoAcesso', [
    function ($request) {
        return new response(200, Ajax::getPontosAcesso($request));
    }
]);
$obRouter->get('/ajax/pontoAcessoEdit', [
    function ($request) {
        return new response(200, Ajax::getPontosAcessoEdit($request));
    }
]);

$obRouter->get('/ajax/comentarios', [
    function ($request) {
        return new response(200, Ajax::getComentarios($request));
    }
]);
$obRouter->post('/ajax/comentarios', [
    function ($request) {
        return new response(200, Ajax::setComentarios($request));
    }
]);
$obRouter->get('/ajax/comentario-detalhado', [
    function ($request) {
        return new response(200, Ajax::getComentario($request));
    }
]);
$obRouter->get('/ajax/alteracoes', [
    function ($request) {
        return new response(200, Ajax::getAlteracoes($request));
    }
]);

$obRouter->get('/ajax/eventos', [
    function ($request) {
        return new response(200, Ajax::getEvents($request));
    }
]);

$obRouter->get('/ajax/graficos/total-eventos', [
    function () {
        return new response(200, Graficos::getGraficosTotalEventos());
    }
]);
$obRouter->get('/ajax/graficos/total-horas', [
    function () {
        return new response(200, Graficos::getGraficosHora());
    }
]);
$obRouter->get('/ajax/graficos/total-clientes', [
    function () {
        return new response(200, Graficos::getGraficosTotalClientes());
    }
]);

$obRouter->get('/ajax/graficos/tempo-medio', [
    function () {
        return new response(200, Graficos::getGraficosHoraMedia());
    }
]);
$obRouter->get('/ajax/graficos/disponibilidade-rede', [
    function () {
        return new response(200, '');
    }
]);

$obRouter->get('/ajax/graficos/forca-maior', [
    function () {
        return new response(200, Graficos::getGraficosForcaMaior());
    }
]);
$obRouter->get('/ajax/graficos/cronograma', [
    function () {
        return new response(200, '');
    }
]);
$obRouter->get('/ajax/graficos/total-horas', [
    function () {
        return new response(200, Graficos::getGraficosHora());
    }
]);
$obRouter->get('/ajax/graficos/top-caixas', [
    function () {
        return new response(200, Graficos::getTopCaixas());
    }
]);
$obRouter->get('/ajax/graficos/top-motivos', [
    function () {
        return new response(200, Graficos::getTopMotivos());
    }
]);
$obRouter->get('/ajax/graficos/dex', [
    function () {
        return new response(200, Graficos::getDEX());
    }
]);
$obRouter->get('/ajax/graficos/heatmap', [
    function () {
        return new response(200, Graficos::getHeatMap());
    }
]);
?>