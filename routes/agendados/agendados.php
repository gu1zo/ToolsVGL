<?php

use \App\http\Response;
use \App\Controller\Agendados\Agendados;

$obRouter->get('/agendados', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Agendados::getAgendados($request));
    }
]);