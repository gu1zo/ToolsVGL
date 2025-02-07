<?php

require __DIR__ . '/../vendor/autoload.php';
use \App\Utils\View;
use \WilliamCosta\DotEnv\Environment;
use \WilliamCosta\DatabaseManager\Database;
use \WilliamCosta\DatabaseManager\Database as oldDataBase;
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

View::init([
    'URL' => URL,
]);

//Mapeamento de middlewares
MiddlewareQueue::setMap([
    'maintenance' => \App\Http\Middleware\Maintenance::class,
    'required-logout' => \App\Http\Middleware\RequireLogout::class,
    'required-login' => App\Http\Middleware\RequireLogin::class,
    'api' => App\Http\Middleware\Api::class,
    'password-reset-confirm' => App\Http\Middleware\PasswordResetConfirm::class,
    'required-login-permission' => App\http\Middleware\RequireLoginPermission::class,
    'required-tipo' => App\http\Middleware\RequireTipo::class,
    'verify-protocol' => App\http\Middleware\VerifyProtocol::class,
    'verify-ponto' => App\http\Middleware\VerifyPonto::class,
    'verify-id' => App\http\Middleware\VerifyId::class,
    'required-admin' => App\http\Middleware\RequireAdmin::class,
]);




//SETA OS MIDDLEWARES PADRÃO
MiddlewareQueue::setDefault([
    'maintenance'
]);