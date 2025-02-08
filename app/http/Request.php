<?php
namespace App\http;

class Request
{
    /**
     * 
     * Instancia do router
     * @var 
     */
    private $router;
    /**
     * Método HTTP
     * @var 
     */
    private $httpMethod;
    /**
     * URI
     * @var 
     */
    private $uri;

    /**
     * Parametros da URL
     * @var array
     */
    private $queryParams = [];

    /**
     * Parametros do POST
     * @var array
     */
    private $postVars = [];

    /**
     * Headers
     * @var array
     */
    private $headers = [];


    /**
     * Construtor
     */

    public function __construct($router)
    {
        $this->router = $router;
        $this->queryParams = $_GET ?? [];
        $this->postVars = $_POST ?? [];
        $this->headers = getallheaders();
        $this->httpMethod = $_SERVER['REQUEST_METHOD'] ?? '';
        $this->setUri();
    }

    private function setUri()
    {
        //URI COM OS GETS
        $this->uri = $_SERVER['REQUEST_URI'] ?? '';

        // REMOVE OS GETS DA URI
        $xUri = explode('?', $this->uri);
        $this->uri = $xUri[0];
    }
    public function getRouter()
    {
        return $this->router;
    }

    public function getHttpMethod()
    {
        return $this->httpMethod;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function getQueryParams()
    {
        return $this->queryParams;
    }

    public function getPostVars()
    {
        return $this->postVars;
    }

    public function getHeaders()
    {
        return $this->headers;
    }
}