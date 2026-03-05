<?php
namespace App\Model\Rest;

class APISippulse
{
    private $url;
    private $user;
    private $pass;

    public function __construct()
    {
        $this->url = getenv('API_URL_SIPPULSE');
        $this->user = getenv('API_USER_SIPPULSE');
        $this->pass = getenv('API_PASS_SIPPULSE');
    }

    public static function getToken()
    {
        $instance = new self();
        $url = $instance->url . '/login';
        $data = [
            "user" => $instance->user,
            "password" => $instance->pass
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Erro no cURL: ' . curl_error($ch);
        } else {
            $responseData = json_decode($response, true);
            $token = $responseData['token'] ?? '';
        }

        curl_close($ch);
        return $token;
    }
    public static function getDadosFila($queue)
    {
        $instance = new self();
        $token = self::getToken();

        if (empty($token)) {
            return null;
        }

        $url = $instance->url . '/v2/extension-dashboard?domain=unificado01.brasiltecpar.com.br&isConected=true&departmentId=' . urlencode($queue);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: {$token}",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Erro no cURL: ' . curl_error($ch);
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $data = json_decode($response, true);
        if (!$data) {
            return null;
        }

        if (!isset($data['statistics'])) {
            return null;
        }

        return $data;

    }
}