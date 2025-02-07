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

$obRouter->get('/usuario/novo', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new response(200, Usuario\Usuario::getNewUser($request));
    }
]);

$obRouter->post('/usuario/novo', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Usuario\Usuario::setNewUser($request));
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

$obRouter->get('/usuario/delete', [
    'middlewares' => [
        'required-login',
        'required-login-permission',
    ],
    function ($request) {
        return new Response(200, Usuario\Usuario::getDeleteUser($request));
    }
]);

$obRouter->post('/usuario/delete', [
    'middlewares' => [
        'required-login',
        'required-login-permission'
    ],
    function ($request) {
        return new Response(200, Usuario\Usuario::setDeleteUser($request));
    }
]);