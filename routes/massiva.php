<?php

use \App\Controller\Massiva\Massiva;
use \App\http\Response;

//ROTA HOME
$obRouter->get('/cidades', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Massiva::getCidades($request));
    }
]);

$obRouter->post('/cidades', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Massiva::setCidades($request));
    }
]);