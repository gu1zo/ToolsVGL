<?php

require __DIR__ . '/../vendor/autoload.php';
use \App\Utils\View;
use \WilliamCosta\DotEnv\Environment;
use \WilliamCosta\DatabaseManager\Database;
use \App\http\Middleware\Queue as MiddlewareQueue;


Environment::load(__DIR__ . '/../');

Database::config(
    getenv('DB_HOST'),
    getenv('DB_NAME'),
    getenv('DB_USER'),
    getenv('DB_PASS'),
    getenv('DB_PORT'),
);


define('URL', getenv('URL'));
define('NUMBER_DIGITAL', getenv('EVO_API_NUMBER_DIGITAL'));
define('NUMBER_SUPORTE', getenv('EVO_API_NUMBER_SUPORTE'));

View::init([
    'URL' => URL,
]);

//Mapeamento de middlewares
MiddlewareQueue::setMap([
    'maintenance' => \App\http\Middleware\Maintenance::class,
    'required-logout' => \App\http\Middleware\RequireLogout::class,
    'required-login' => App\http\Middleware\RequireLogin::class,
    'password-reset-confirm' => App\http\Middleware\PasswordResetConfirm::class,
    'required-login-permission' => App\http\Middleware\RequireLoginPermission::class,
    'required-admin' => App\http\Middleware\RequireAdmin::class,
    'required-admin-fila' => App\http\Middleware\RequireAdminFila::class
]);




//SETA OS MIDDLEWARES PADRÃO
MiddlewareQueue::setDefault([
    'maintenance'
]);