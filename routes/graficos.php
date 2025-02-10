<?php

use \App\Controller\Graficos\Graficos;
use \App\http\response;

$obRouter->get('/graficos/geral-ano', [
    'middlewares' => [
        'required-login'
    ],
    function () {
        return new response(200, Graficos::getGraficos());
    }
]);
$obRouter->get('/graficos/dex', [
    'middlewares' => [
        'required-login'
    ],
    function () {
        return new response(200, Graficos::getDEX());
    }
]);
?>