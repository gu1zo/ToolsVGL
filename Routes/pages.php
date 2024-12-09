<?php

use \App\Controller\Pages;
use \App\http\response;


//ROTA HOME
$obRouter->get('/', [
    function () {
        return new response(200, Pages\Home::getHome());
    }
]);

//ROTA SOBRE
$obRouter->get('/sobre', [
    function () {
        return new response(200, Pages\About::getAbout());
    }
]);

$obRouter->get('/depoimentos', [
    function ($request) {
        return new response(200, Pages\Testimony::getTestimonies($request));
    }
]);

//ROTA POST
$obRouter->post('/depoimentos', [
    function ($request) {
        return new response(200, Pages\Testimony::insertTestimony($request));
    }
]);



//ROTA DINAMICA
/*
$obRouter->get('/pagina/{idPagina}/{acao}', [
    function ($idPagina, $acao) {
        return new response(200, 'Pagina' . $idPagina . ' - ' . $acao);
    }
]);*/