<?php

use \App\http\Response;
use \App\Controller\Apps\Apps;

$obRouter->get('/apps', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Apps::getApps($request));
    }
]);

$obRouter->get('/apps/novo', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Apps::getNewApp($request));
    }
]);

$obRouter->post('/apps/novo', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Apps::setNewApp($request));
    }
]);

$obRouter->get('/apps/excluir', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Apps::getDeleteApp($request));
    }
]);

$obRouter->post('/apps/excluir', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Apps::setDeleteApp($request));
    }
]);