<?php

namespace App\Model\Rest;

class APIElite
{
    private $url;
    private $user;
    private $pass;

    public function __construct()
    {
        $this->url = getenv('API_URL');
        $this->user = getenv('API_USER');
        $this->pass = getenv('API_PASS');
    }

    /**
     * Retorna os dados dos clientes com base no ponto de acesso.
     *
     * @param string $codcon Código do ponto de acesso.
     * @return array|string Retorna os dados decodificados ou uma mensagem de erro.
     */
    public static function getDadosByCodcon($codcon = '')
    {
        $instance = new self();

        // Dados do payload
        $data = [
            "request" => [
                "sendRequest" => "integrator.server",
                "method" => "list",
                "submethod" => "view.execute",
                "params" => [
                    "_user" => $instance->user,
                    "_passwd" => $instance->pass,
                    "_consulta" => "010C12AXXX",
                    "codcon" => $codcon
                ]
            ]
        ];

        return $instance->makeRequest($data);
    }

    /**
     * Retorna os pontos de acesso de acordo com o nome.
     *
     * @param string $con_nome Nome do ponto de acesso.
     * @return array|string Retorna os dados decodificados ou uma mensagem de erro.
     */
    public static function getPontosAcesso($con_nome = '')
    {
        $instance = new self();
        // Dados do payload
        $data = [
            "request" => [
                "sendRequest" => "integrator.server",
                "method" => "execute",
                "submethod" => "condominio.list",
                "params" => [
                    "_user" => $instance->user,
                    "_passwd" => $instance->pass,
                    "ignora_inativo" => true,
                    "con_nome" => $con_nome
                ]
            ]
        ];

        return $instance->makeRequest($data);
    }

    public static function getCidades()
    {
        $instance = new self();
        // Dados do payload
        $data = [
            "request" => [
                "sendRequest" => "integrator.server",
                "method" => "execute",
                "submethod" => "cidades.list",
                "params" => [
                    "_user" => $instance->user,
                    "_passwd" => $instance->pass
                ]
            ]
        ];

        return $instance->makeRequest($data);
    }

    private function makeRequest(array $data)
    {
        // Inicializa o cURL
        $ch = curl_init($this->url);

        // Configurações do cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: APIElite/1.0'
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Executa a requisição
        $response = curl_exec($ch);
        // Verifica erros de conexão
        if (curl_errno($ch)) {
            $error = "Erro na requisição: " . curl_error($ch);
            curl_close($ch);
            return $error;
        }

        // Fecha a conexão cURL
        curl_close($ch);

        // Decodifica a resposta JSON
        $decodedResponse = json_decode($response, true);

        // Verifica se a decodificação foi bem-sucedida
        if (is_null($decodedResponse)) {
            return "Erro ao decodificar a resposta da API: " . $response;
        }
        if (!$decodedResponse['error']) {
            return $decodedResponse['data']['results'];
        }
        return false;
    }
}