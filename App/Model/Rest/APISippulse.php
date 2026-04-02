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
        return $token;
    }


    public static function getDadosTotalChamadas($fila)
    {
        $instance = new self();
        $token = self::getToken();

        if (empty($token)) {
            return null;
        }

        switch ($fila) {
            case 340:
                $queueName = 'FILA_CSA_GGNET';
                break;
            case 347:
                $queueName = 'FILA_SAC_FINANCEIRO_GGNET';
                break;
            case 341:
                $queueName = 'FILA_CSA_N2_GGNET';
                break;
        }

        $domain = "unificado01.brasiltecpar.com.br";

        $startDate = date('Y-m-d') . ' 00:00:00';
        $endDate = date('Y-m-d') . ' 23:59:59';

        $totalRecebidas = 0;
        $totalAtendidas = 0;
        $totalPerdidas = 0;

        $page = 0;
        $totalPages = 1;

        while ($page < $totalPages) {

            $url = $instance->url . '/v2/reports/queueCdrs'
                . '?domain=' . urlencode($domain)
                . '&queueName=' . urlencode($queueName)
                . '&direction=inbound'
                . '&startDate=' . urlencode($startDate)
                . '&endDate=' . urlencode($endDate)
                . '&page=' . $page;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                "Authorization: {$token}",
                "Accept: application/json"
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                curl_close($ch);
                return null;
            }

            curl_close($ch);

            $data = json_decode($response, true);

            if (!isset($data['content'])) {
                break;
            }

            // atualiza total de páginas
            $totalPages = $data['totalPages'];

            foreach ($data['content'] as $call) {

                $totalRecebidas++;

                if ($call['status'] === 'answered') {
                    $totalAtendidas++;
                } else if ($call['status'] === 'abandoned') {
                    $totalPerdidas++;
                }
            }

            $page++;
        }

        return [
            'total_recebidas' => $totalRecebidas,
            'total_atendidas' => $totalAtendidas,
            'total_perdidas' => $totalPerdidas
        ];
    }
    public static function getDadosChamadas($queue)
    {
        $instance = new self();
        $token = self::getToken();

        if (empty($token)) {
            return null;
        }

        $page = 0;
        $size = 100;

        $total = 0;
        $answered = 0;
        $waiting = 0;

        do {

            $url = $instance->url . '/v2/memberfreeswitch/params?domain=unificado01.brasiltecpar.com.br'
                . '&queueId=' . urlencode($queue)
                . '&page=' . $page
                . '&size=' . $size;

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
            if (!$data || !isset($data['content'])) {
                return null;
            }

            foreach ($data['content'] as $item) {

                $total++;

                if ($item['state'] === 'Answered') {
                    $answered++;
                }

                if ($item['state'] === 'Waiting') {
                    $waiting++;
                }
            }

            $page++;

        } while (!$data['last']);

        return [
            'total' => $total,
            'answered' => $answered,
            'waiting' => $waiting
        ];
    }
    public static function getAgentesDisponiveis($queueId)
    {
        $instance = new self();
        $token = self::getToken();

        if (empty($token)) {
            return null;
        }

        $domain = "unificado01.brasiltecpar.com.br";

        $url = $instance->url . '/v2/dashboard/agent/analyticsDashboard'
            . '?queueId=' . urlencode($queueId)
            . '&domain=' . urlencode($domain);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: {$token}",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $data = json_decode($response, true);

        if (!$data) {
            return null;
        }

        $totalAgentes = 0;
        $agentesDisponiveis = 0;

        foreach ($data as $agent) {

            $totalAgentes++;

            $parsed = self::parseAgentStatus($agent);

            if ($parsed['status'] === 'available') {
                $agentesDisponiveis++;
            }
        }

        return [
            'total_agentes' => $totalAgentes,
            'agentes_disponiveis' => $agentesDisponiveis
        ];
    }

    public static function getAgentes($queueId)
    {
        $instance = new self();
        $token = self::getToken();

        if (empty($token)) {
            return null;
        }

        $domain = "unificado01.brasiltecpar.com.br";

        $url = $instance->url . '/v2/dashboard/agent/analyticsDashboard'
            . '?queueId=' . urlencode($queueId)
            . '&domain=' . urlencode($domain);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: {$token}",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $data = json_decode($response, true);

        if (!$data) {
            return null;
        }

        $agents = [];

        foreach ($data as $agent) {

            $parsed = self::parseAgentStatus($agent);

            $seconds = $agent['secondsElapsedStatus'] ?? 0;

            $agents[] = [
                "name" => $agent['user']['firstName'] ?? 'Desconhecido',
                "status" => $parsed['status'],
                "pause" => $parsed['pause'],
                "time" => gmdate("H:i:s", $seconds)
            ];
        }

        usort($agents, function ($a, $b) {

            $order = [
                'available' => 1,
                'call' => 2,
                'pause' => 3
            ];

            return $order[$a['status']] <=> $order[$b['status']];
        });

        return $agents;
    }

    private static function parseAgentStatus($agent)
    {
        if (($agent['currentAgentStatus'] ?? null) === 'ON_BREAK') {
            return [
                'status' => 'pause',
                'pause' => $agent['queueAgentBreakName'] ?? 'Pausa'
            ];
        }

        if (($agent['currentAgentState'] ?? null) === 'IN_A_QUEUE_CALL') {
            return [
                'status' => 'call',
                'pause' => null
            ];
        }

        if (
            ($agent['currentAgentStatus'] ?? null) === 'AVAILABLE' &&
            ($agent['currentAgentState'] ?? null) === 'WAITING'
        ) {
            return [
                'status' => 'available',
                'pause' => null
            ];
        }

        return [
            'status' => 'pause',
            'pause' => 'Indisponível'
        ];
    }

    public static function getChamadas($date, $page, $size, $queue)
    {
        $instance = new self();
        $token = self::getToken();

        if (empty($token)) {
            return null;
        }

        $domain = "unificado01.brasiltecpar.com.br";
        $startDate = $date . ' 00:00:00';
        $endDate = $date . ' 23:59:59';
        $url = $instance->url . '/v2/reports/queueCdrs'
            . '?domain=' . urlencode($domain)
            . '&direction=inbound'
            . '&startDate=' . urlencode($startDate)
            . '&endDate=' . urlencode($endDate)
            . '&page=' . $page
            . '&size=' . $size
            . '&queueName=' . $queue;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: {$token}",
            "Accept: application/json"
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            curl_close($ch);
            return null;
        }

        curl_close($ch);

        $data = json_decode($response, true);

        return $data;
    }
}