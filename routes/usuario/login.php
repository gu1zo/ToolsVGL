<?php
use \App\http\Response;
use \App\Controller\Usuario;


$obRouter->get('/login', [
    'middlewares' => [
        'required-logout'
    ],
    function ($request) {
        return new Response(200, Usuario\Login::getLogin($request));
    }
]);

//Rotea de login POST
$obRouter->post('/login', [
    'middlewares' => [
        'required-logout'
    ],
    function ($request) {

        return new Response(200, Usuario\Login::setLogin($request));
    }
]);

$obRouter->get('/logout', [
    'middlewares' => [
        'required-login'
    ],
    function ($request) {
        return new Response(200, Usuario\Login::setLogout($request));
    }
]);