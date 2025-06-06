<?php

use \App\http\Response;
use \App\Controller\Api\Api;

$obRouter->post('/api/setNota', [
    'middlewares' => [
    ],
    function ($request) {
        return new response(200, Api::setNota($request));
    }
]);