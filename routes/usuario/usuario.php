<?php

use \App\http\Response;
use \App\Controller\Usuario;

$obRouter->get('/usuario', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Usuario\Usuario::getUser($request));
    }
]);

$obRouter->get('/usuario/edit', [
    'middlewares' => [
        'required-login',
        'required-login-permission'
    ],
    function ($request) {
        return new Response(200, Usuario\Usuario::getEditUser($request));
    }
]);

$obRouter->post('/usuario/edit', [
    'middlewares' => [
        'required-login',
        'required-login-permission'
    ],
    function ($request) {
        return new Response(200, Usuario\Usuario::setEditUser($request));
    }
]);