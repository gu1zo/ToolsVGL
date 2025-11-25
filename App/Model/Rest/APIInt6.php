<?php
namespace App\Model\Rest;

class APIInt6
{
    private $url;
    private $user;
    private $pass;

    public function __construct()
    {
        $this->url = getenv('API_URL_INT6');
        $this->user = getenv('API_USER_INT6');
        $this->pass = getenv('API_PASS_INT6');
    }

    public static function getToken()
    {
        $instance = new self();
        $url = $instance->url . '/api/auth/v2/request_token';
        $data = [
            "username" => $instance->user,
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

    /**
     * Faz uma requisição GET para o endpoint do status do roteador.
     *
     * @param string $pppoeUsername Nome de usuário PPPoE.
     * @return array|null Retorna os dados extraídos ou null se falhar.
     */
    public static function getRouterStatus($pppoeUsername)
    {
        $instance = new self();
        $token = self::getToken();

        if (empty($token)) {
            return null;
        }

        $url = $instance->url . '/api/diag/v2/live/acs_router_status?pppoe_username=' . urlencode($pppoeUsername);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$token}",
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

        // 🔹 Exemplo: extraindo apenas alguns campos da resposta
        if (isset($data['wifi_networks'])) {
            foreach ($data['wifi_networks'] as $wifi) {
                // Ignora redes que não são principais
                if (empty($wifi['is_main_wlan'])) {
                    continue;
                }

                // 2.4 GHz principal
                if ($wifi['wifi_type'] === '2.4G') {
                    $wifi24 = $wifi['ssid'];
                }

                // 5 GHz principal
                if ($wifi['wifi_type'] === '5G') {
                    $wifi5 = $wifi['ssid'];
                }
            }
        }

        $response = [
            'modelo' => $data['device_info']['model'] ?? 'N/A',
            'firmware' => $data['device_info']['firmware_version'] ?? 'N/A',
            'wifi24' => $wifi24 ?? 'N/A',
            'wifi5' => $wifi5 ?? 'N/A',
            'dns' => $data['dhcpv4_server'][0]['dnss'] ?? 'N/A',
        ];

        return $response;

    }

    private static function getGponStatus($pppoeUsername)
    {
        $instance = new self();
        $token = self::getToken();

        if (empty($token)) {
            return null;
        }

        $url = $instance->url . '/api/diag/v2/live/gpon_status?pppoe_username=' . urlencode($pppoeUsername);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$token}",
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

        return $data;
    }
    private static function getPonLink($pppoeUsername)
    {
        $instance = new self();
        $token = self::getToken();

        if (empty($token)) {
            return null;
        }

        $url = $instance->url . '/api/info/v2/gpon_info?pppoe_username=' . urlencode($pppoeUsername);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer {$token}",
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

        if (isset($data['error'])) {
            return 'N/A';
        }
        if (!isset($data['olt_hostname'])) {
            return 'N/A';
        }
        return $data['olt_hostname'] . ' ' . $data['ponlink'] . ' ONU: ' . $data['onu'];
    }

    public static function getOnu($pppoeUsername)
    {
        $gponStatus = self::getGponStatus($pppoeUsername);
        $ponlik = self::getPonLink($pppoeUsername);

        $response = [
            'modelo' => $gponStatus['onu_info']['onu_model']['text'] ?? 'N/A',
            'firmware' => $gponStatus['onu_info']['onu_firmware']['text'] ?? 'N/A',
            'sinal' => $gponStatus['onu_attenuation']['text'] ?? 'N/A',
            'ponlink' => $ponlik
        ];

        return $response;
    }
}