<?php
use \App\http\Response;
use \App\Controller\Admin;


//ROTA DE LISTAGEM DOS USUÁRIOS
$obRouter->get('/admin/users', [
    'middlewares' => [
        'required-admin-login'
    ],
    function ($request) {
        return new Response(200, Admin\User::getUser($request));
    }
]);

//CADASTRO NOVO USUÁRIO
$obRouter->get('/admin/users/new', [
    'middlewares' => [
        'required-admin-login'
    ],
    function ($request) {
        return new Response(200, Admin\User::getNewUser($request));
    }
]);
//CADASTRO NOVO USUÁRIO POST
$obRouter->post('/admin/users/new', [
    'middlewares' => [
        'required-admin-login'
    ],
    function ($request) {
        return new Response(200, Admin\User::setNewUser($request));
    }
]);

//ROTA DE EDIÇÃO
$obRouter->get('/admin/users/{id}/edit', [
    'middlewares' => [
        'required-admin-login'
    ],
    function ($request, $id) {
        return new Response(200, Admin\User::getEditUser($request, $id));
    }
]);

//ROTA DE EDIÇÃO (post)
$obRouter->post('/admin/users/{id}/edit', [
    'middlewares' => [
        'required-admin-login'
    ],
    function ($request, $id) {
        return new Response(200, Admin\User::setEditUser($request, $id));
    }
]);

//ROTA DE EXCLUSÃO
$obRouter->get('/admin/users/{id}/delete', [
    'middlewares' => [
        'required-admin-login'
    ],
    function ($request, $id) {
        return new Response(200, Admin\User::getDeleteUser($request, $id));
    }
]);


//ROTA DE EXCLUSÃO (POST)
$obRouter->post('/admin/users/{id}/delete', [
    'middlewares' => [
        'required-admin-login'
    ],
    function ($request, $id) {
        return new Response(200, Admin\User::setDeleteUser($request, $id));
    }
]);