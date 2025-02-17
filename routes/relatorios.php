<?php

use \App\Controller\Relatorios\Relatorios;
use \App\http\Response;

//ROTA HOME
$obRouter->get('/relatorios', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Relatorios::getEventosCSV($request));
    }
]);