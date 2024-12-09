<?php

namespace App\http;


use \Closure;
use \Exception;
use \ReflectionFunction;
use \App\http\Middleware\Queue as MiddlewareQueue;
use Stringable;

class Router
{
    private $url = '';

    private $prefix = '';

    private $routes = [];

    /**
     * Instancia do request
     * @var request
     */
    private $request;

    private $contentType = 'text/html';


    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }


    /**
     * Método responsável por definir uma rota de get
     * @param string $route
     * @param array $params
     */
    public function get($route, $params = [])
    {
        return $this->addRoute('GET', $route, $params);
    }


    /**
     * Método responsável por definir uma rota de POST
     * @param string $route
     * @param array $params
     */
    public function post($route, $params = [])
    {
        return $this->addRoute('POST', $route, $params);
    }

    /**
     * Método responsável por definir uma rota de PUT
     * @param string $route
     * @param array $params
     */
    public function put($route, $params = [])
    {
        return $this->addRoute('PUT', $route, $params);
    }


    /**
     * Método responsável por definir uma rota de DELETE
     * @param string $route
     * @param array $params
     */
    public function delete($route, $params = [])
    {
        return $this->addRoute('DELETE', $route, $params);
    }

    public function __construct($url)
    {
        $this->request = new request($this);
        $this->url = $url;
        $this->setPrefix();
    }

    /**
     * Define o prefixo das rotas
     */
    private function setPrefix()
    {
        $parseUrl = parse_url($this->url);
        $this->prefix = $parseUrl['path'] ?? '';
    }

    /**
     * Método responsável por adicionar uam rota na classe
     * @param string $method
     * @param string $route
     * @param array $params
     */
    private function addRoute($method, $route, $params = [])
    {
        //VALIDAÇÃO DOS PARAMETROS
        foreach ($params as $key => $value) {
            if ($value instanceof Closure) {
                $params['controller'] = $value;
                unset($params[$key]);
                continue;
            }
        }

        //middlewares da rota
        $params['middlewares'] = $params['middlewares'] ?? [];

        //Variáveis da rota

        $params['variables'] = [];

        //PADRÃO VALIDACAO VARIAVEIS
        $patternVariable = '/{(.*?)}/';
        if (preg_match_all($patternVariable, $route, $matches)) {
            $route = preg_replace($patternVariable, '(.*?)', $route);
            $params['variables'] = $matches[1];
        }

        $route = rtrim($route, '/');

        //PADRÃO DE VALIDAÇÃO DA URL
        $patternRoute = '/^' . str_replace('/', '\/', $route) . '$/';
        //ADICIONA ROTA DENTRO DA CLASSE
        $this->routes[$patternRoute][$method] = $params;
    }

    /**
     * Retorna a URI sem o prefix
     * @return string
     */
    private function getUri()
    {
        $uri = $this->request->getUri();


        $xUri = strlen($this->prefix) ? explode($this->prefix, $uri) : [$uri];

        return rtrim(end($xUri), '/');
    }

    /**
     * Retora os dados da rota atual
     * @return array
     */
    private function getRoute()
    {
        $uri = $this->getUri();

        $httpMethod = $this->request->getHttpMethod();

        foreach ($this->routes as $patternRoute => $methods) {
            if (preg_match($patternRoute, $uri, $matches)) {
                if (isset($methods[$httpMethod])) {
                    unset($matches[0]);


                    //Variaveis processadas
                    $keys = $methods[$httpMethod]['variables'];
                    $methods[$httpMethod]['variables'] = array_combine($keys, $matches);
                    $methods[$httpMethod]['variables']['request'] = $this->request;

                    return $methods[$httpMethod];
                }
                throw new Exception("Método não é permitido", 405);
            }
        }

        throw new Exception("URL não encontrada", 404);
    }



    /**
     * Método responsável por executar a rota atual
     * @return Response
     */

    public function run()
    {
        try {
            //OBTÉM A ROTA ATUAL
            $route = $this->getRoute();

            if (!isset($route['controller'])) {
                throw new Exception('A URL não pode ser processada', 500);
            }

            $args = [];

            $reflection = new ReflectionFunction($route['controller']);
            foreach ($reflection->getParameters() as $parameter) {
                $name = $parameter->getName();
                $args[$name] = $route['variables'][$name] ?? '';
            }

            return (new MiddlewareQueue($route['middlewares'], $route['controller'], $args))->next($this->request);

        } catch (Exception $e) {
            return new Response($e->getCode(), $this->getErrorMessage($e->getMessage()), $this->contentType);
        }
    }

    /**
     * Método responsável por retornar a mensagem de erro de acordo com o contentType
     * @param String $message
     * @return mixed
     */
    private function getErrorMessage($message)
    {
        switch ($this->contentType) {
            case 'application/json':
                return [
                    'error' => $message
                ];
            default:
                return $message;
        }
    }

    /**
     * Método responsável por Retornar a url atual
     * @return string
     */
    public function getCurrentUrl()
    {
        return $this->url . $this->getUri();
    }


    /**
     * Método responsável por redirecionar a URL
     * @param string $route
     */
    public function redirect($route)
    {
        $url = $this->url . $route;

        //EXECUTA O REDIRECT
        header('Location:' . $url);
    }
}