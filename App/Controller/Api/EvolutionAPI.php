<?php

namespace App\Controller\Api;

class EvolutionAPI
{
    private $url;
    private $key;
    private $instanceName;
    private $number;

    private function __construct()
    {
        $this->url = getenv('EVO_API_URL');
        $this->key = getenv('EVO_API_KEY');
        $this->instanceName = getenv('EVO_API_INSTANCE_NAME');
        $this->number = getenv('EVO_API_NUMBER');
    }

    public static function sendMessage($text)
    {
        $instance = new self();
        $number = $instance->number;

        $apiUrl = "{$instance->url}/message/sendText/{$instance->instanceName}";

        $data = [
            "number" => $number,
            "textMessage" => [
                "text" => $text
            ]
        ];

        $headers = [
            "Content-Type: application/json",
            "apikey: {$instance->key}"
        ];

        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

        // Ativar depuração
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_STDERR, fopen('php://stderr', 'w')); // Direciona erros para o log

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        // Adiciona uma verificação para erros do cURL
        if (curl_errno($ch)) {
            return ["error" => "Erro cURL: " . curl_error($ch)];
        }

        curl_close($ch);

        // Verifique o status do HTTP e retorne a resposta
        if ($httpCode == 200 || $httpCode == 201) {
            return json_decode($response, true);
        } else {
            return [
                "error" => "Erro ao enviar mensagem",
                "status" => $httpCode,
                "response" => $response
            ];
        }
    }
}