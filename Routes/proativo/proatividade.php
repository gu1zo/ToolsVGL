<?php
use \App\http\Response;
use \App\Controller\Proativo\Proativo;

$obRouter->get('/proatividade', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Proativo::getProatividade($request));
    }
]);

$obRouter->get('/proatividade/novo', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Proativo::getNovoProativo($request));
    }
]);

$obRouter->post('/proatividade/novo', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Proativo::setNovoProativo($request));
    }
]);

$obRouter->get('/proatividade/edit', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Proativo::getEditProativo($request));
    }
]);

$obRouter->post('/proatividade/edit', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Proativo::setEditProativo($request));
    }
]);

$obRouter->get('/proatividade/delete', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Proativo::getDeleteProatividade($request));
    }
]);
$obRouter->post('/proatividade/delete', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Proativo::setDeleteProatividade($request));
    }
]);