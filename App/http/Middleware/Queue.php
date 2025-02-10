<?php

namespace App\http\Middleware;

use App\http\Request;
use App\Http\Response;

class Queue
{

    //Middlewares default, carregados em todas as rotas
    private static $default = [];

    private static $map = [];

    //Fila dos middlewares a serem executados
    private $middlewares = [];

    //Função de execução do controller (Closure)
    private $controller;

    //Argumentos do controller
    private $controllerArgs = [];

    public function __construct($middleware, $controller, $controllerArgs)
    {
        $this->middlewares = array_merge(self::$default, $middleware);
        $this->controller = $controller;
        $this->controllerArgs = $controllerArgs;
    }


    public static function setMap($map)
    {
        self::$map = $map;
    }

    public static function setDefault($default)
    {
        self::$default = $default;
    }
    /**
     * Método responsável por executar o próximo nível da fila dos middlewares
     * @param Request
     * @return Response
     */
    public function next($request)
    {
        //Verifica se a fila está vazia
        if (empty($this->middlewares))
            return call_user_func_array($this->controller, $this->controllerArgs);

        //MIDDLEWARE ATUAL
        $middleware = array_shift($this->middlewares);

        if (!isset(self::$map[$middleware])) {
            throw new \Exception("Problemas ao processar o middleware da requisição", 500);
        }

        //NEXT
        $queue = $this;
        $next = function ($request) use ($queue) {
            return $queue->next($request);
        };

        //Executa o middleware
        return (new self::$map[$middleware])->handle($request, $next);
    }


}