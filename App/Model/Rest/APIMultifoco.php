<?php

namespace App\Model\Rest;

class APIMultifoco
{
    private $uri;
    private $token;

    private function __construct()
    {
        $this->uri = getenv('MULTIFOCO_URI');
        $this->token = getenv('MULTIFOCO_TOKEN');

    }
    private static function makeRequest(string $method, string $url, array $headers = [], $body = null): array
    {
        $ch = curl_init();



        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, strtoupper($method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Se houver payload
        if ($body !== null) {
            if (is_array($body)) {
                $body = json_encode($body);
                $headers[] = 'Content-Type: application/json';
            }
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        }

        // Headers
        if (!empty($headers)) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        return [
            'status' => $statusCode,
            'body' => $response,
            'error' => $error ?: null,
        ];
    }

    public static function getTecnicos()
    {
        $instance = new self();

        $headers = [
            'Authorization: Bearer ' . $instance->token
        ];
        $params = [
            'status' => true
        ];


        $url = $instance->uri . 'users/filter/technician' . self::buildQueryParams($params);
        return self::makeRequest('get', $url, $headers);
    }

    public static function getOrdensServicoByDate($date, $type)
    {
        $instance = new self();

        $headers = [
            'Authorization: Bearer ' . $instance->token
        ];
        $params = [
            'type' => $type,
            'serviceExecution[0]' => $date,
            'model' => [1, 10, 27, 37]
        ];

        $urlBase = $instance->uri . 'serviceOrders' . self::buildQueryParams($params);

        $ids = [];
        $page = 1;
        $lastPage = 1;

        do {
            $url = $urlBase . '&page=' . $page;
            $response = self::makeRequest('get', $url, $headers);

            $data = json_decode($response['body'], true);

            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    if (isset($item['id'])) {
                        $ids[] = $item['id'];
                    }
                }
            }

            // Atualiza o total de páginas
            if (isset($data['meta']['last_page'])) {
                $lastPage = (int) $data['meta']['last_page'];
            }

            $page++;
        } while ($page <= $lastPage);

        return $ids;
    }

    public static function getImagesOsById($id)
    {
        $instance = new self();

        $headers = [
            'Authorization: Bearer ' . $instance->token
        ];

        $url = $instance->uri . 'serviceOrders/' . $id . '/view/photos';

        $response = self::makeRequest('get', $url, $headers);
        $data = json_decode($response['body'], true);

        $images = [];

        if (!empty($data['items'])) {
            foreach ($data['items'] as $item) {
                $images[] = [
                    'title' => $item['title'] ?? '',
                    'url' => $item['file'] ?? ''
                ];
            }
        }

        return $images;
    }

    public static function getOsById($id)
    {
        $instance = new self();

        $headers = [
            'Authorization: Bearer ' . $instance->token
        ];

        $url = $instance->uri . 'serviceOrders/' . $id;

        $response = self::makeRequest('get', $url, $headers);
        $data = json_decode($response['body'], true);

        if (!empty($data['items'])) {
            $item = $data['items'];
            $osData = [
                'numero' => $item['number'] ?? '',
                'data' => $item['schedule']['schedulingEndFormatted'] ?? '',
                'id-tecnico' => $item['technician']['id'] ?? '',
                'nome-tecnico' => $item['technician']['name'] ?? '',
                'cliente' => $item['client']['name'] ?? '',
                'tipo' => $item['modelDoc']['name'] ?? '',
            ];
        }

        return $osData;
    }

    public static function getEquipamentosOsById($id)
    {
        $instance = new self();

        $headers = [
            'Authorization: Bearer ' . $instance->token
        ];

        $url = $instance->uri . 'serviceOrders/' . $id . '/view/productsServices';

        $response = self::makeRequest('get', $url, $headers);
        $data = json_decode($response['body'], true);

        $equipamentos = [];

        if (!empty($data['items'])) {
            foreach ($data['items']['productsServices'] as $item) {
                $equipamentos[] = [
                    'nome' => $item['name'] ?? '',
                    'qtd' => $item['amount'] ?? ''
                ];
            }
        }

        return $equipamentos;
    }


    private static function buildQueryParams(array $params): string
    {
        if (empty($params)) {
            return '';
        }
        return '?' . http_build_query($params);
    }

}