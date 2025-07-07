<?php

use \App\http\Response;
use \App\Controller\Emails\Emails;

$obRouter->get('/emails', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Emails::getNovoEmail($request));
    }
]);

$obRouter->post('/emails', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Emails::setNovoEmail($request));
    }
]);

$obRouter->get('/emails/table', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Emails::getEmailsTable($request));
    }
]);

$obRouter->post('/emails/table', [
    'middlewares' => [
        'required-login',
        'required-admin'
    ],
    function ($request) {
        return new response(200, Emails::setEmailsTable($request));
    }
]);