<?php

namespace App\Model\Rest;

class APIGeogrid
{
    private $url;
    private $token;

    public function __construct()
    {
        $this->url = getenv('GEOGRID_API_URL');
        $this->token = getenv('GEOGRID_API_TOKEN');
    }

    /**
     * Retorna os itens de rede com base nos parâmetros de pesquisa.
     *
     * @param string $pesquisa Código de pesquisa.
     * @return array|string Retorna os dados decodificados ou uma mensagem de erro.
     */
    public static function getCaixas($pesquisa)
    {
        $instance = new self();
        $url = $instance->url . "/itensRede?pesquisa=" . urlencode($pesquisa) . "&item=poste";

        return $instance->makeRequest($url);
    }

    private function makeRequest(string $url)
    {
        // Inicializa o cURL
        $ch = curl_init($url);

        // Configurações do cURL
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'api-key: ' . $this->token
        ]);
        curl_setopt($ch, CURLOPT_COOKIE, 'PHPSESSID=' . $this->token);
        curl_setopt($ch, CURLOPT_TIMEOUT, 90); // Aumentando o timeout para 90 segundos

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
        if (!empty($decodedResponse['registros'])) {
            return $decodedResponse['registros'];
        }
        return [];
    }
}