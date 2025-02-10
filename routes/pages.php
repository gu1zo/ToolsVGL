<?php

use \App\Controller\Pages;
use \App\http\Response;

//ROTA HOME
$obRouter->get('/', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Pages\Home::getHome($request));
    }
]);


//ROTA DINAMICA
/*
$obRouter->get('/pagina/{idPagina}/{acao}', [
    function ($idPagina, $acao) {
        return new response(200, 'Pagina' . $idPagina . ' - ' . $acao);
    }
]);*/