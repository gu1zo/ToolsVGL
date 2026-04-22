<?php
namespace App\Model\Rest;

class APISippulse
{
    private $url;
    private $user;
    private $pass;
    private static $tokenCache = null;
    private static $tokenTime = 0;

    public function __construct()
    {
        $this->url = getenv('API_URL_SIPPULSE');
        $this->user = getenv('API_USER_SIPPULSE');
        $this->pass = getenv('API_PASS_SIPPULSE');
    }

    public static function getToken()
    {
        // 🔥 cache por 60s
        if (self::$tokenCache && (time() - self::$tokenTime) < 60) {
            return self::$tokenCache;
        }

        $instance = new self();
        $url = $instance->url . '/login';

        $data = [
            "user" => $instance->user,
            "password" => $instance->pass
        ];

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $responseData = json_decode($response, true);

        self::$tokenCache = $responseData['token'] ?? null;
        self::$tokenTime = time();

        return self::$tokenCache;
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
            case 404:
                $queueName = 'FILA_CSA_EVO';
                break;
            case 405:
                $queueName = 'FILA_SAC_FINANCEIRO_EVO';
                break;
            case 342:
                $queueName = 'FILA_NOC_CDR_ALT';
                break;
            case 343:
                $queueName = 'FILA_NOC_MADRU_ALT';
                break;
            case 344:
                $queueName = 'FILA_NOC_SUPERVISAO_ALT';
                break;
            case 380:
                $queueName = 'FILA_NOC_COMERCIAL_ALT';
                break;
        }
        $domain = "unificado01.brasiltecpar.com.br";
        date_default_timezone_set('America/Sao_Paulo');
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
        $ramais = [];

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
                    $partes = explode('@', $item['servingAgent']);
                    $ramalExtraido = $partes[0] ?? null;

                    if ($ramalExtraido) {
                        $ramais[] = $ramalExtraido;
                    }
                }
            }

            $page++;

        } while (!$data['last']);

        return [
            'total' => $total,
            'answered' => $answered,
            'waiting' => $waiting,
            'ramais' => $ramais
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

    public static function getNota($date, $page, $size, $ivrId, $numero, $uuid)
    {
        $instance = new self();
        $token = self::getToken();

        if (empty($token) || empty($ivrId) || empty($numero) || empty($uuid)) {
            return null;
        }

        $domain = "unificado01.brasiltecpar.com.br";
        $startDate = $date . ' 00:00:00';
        $endDate = $date . ' 23:59:59';

        $url = $instance->url . '/v2/ivrLog/findByFilters'
            . '?domain=' . urlencode($domain)
            . '&startDate=' . urlencode($startDate)
            . '&endDate=' . urlencode($endDate)
            . '&page=' . $page
            . '&size=' . $size
            . '&ivrId=' . $ivrId
            . '&caller=' . $numero;

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

        if (!isset($data['content']) || !is_array($data['content'])) {
            return null;
        }

        // 🔎 Procura pelo UUID correto
        foreach ($data['content'] as $item) {
            if (isset($item['uuid']) && $item['uuid'] === $uuid) {
                return $item['digit'] ?? null;
            }
        }

        return null;
    }

    public static function getDadosFilasParalelo($filas)
    {
        $instance = new self();
        $token = self::getToken();

        if (empty($token)) {
            return null;
        }

        $domain = "unificado01.brasiltecpar.com.br";

        $multi = curl_multi_init();
        $handles = [];

        foreach ($filas as $fila) {

            // =============================
            // chamadas
            // =============================
            $urlChamadas = $instance->url . "/v2/memberfreeswitch/params?domain={$domain}&queueId={$fila}&page=0&size=100";

            $chChamadas = curl_init();
            curl_setopt_array($chChamadas, [
                CURLOPT_URL => $urlChamadas,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: {$token}",
                    "Accept: application/json"
                ]
            ]);

            curl_multi_add_handle($multi, $chChamadas);

            // =============================
            // agentes
            // =============================
            $urlAgentes = $instance->url . "/v2/dashboard/agent/analyticsDashboard?queueId={$fila}&domain={$domain}";

            $chAgentes = curl_init();
            curl_setopt_array($chAgentes, [
                CURLOPT_URL => $urlAgentes,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    "Authorization: {$token}",
                    "Accept: application/json"
                ]
            ]);

            curl_multi_add_handle($multi, $chAgentes);

            $handles[] = [
                'chamadas' => $chChamadas,
                'agentes' => $chAgentes
            ];
        }

        // 🚀 executa paralelo
        do {
            curl_multi_exec($multi, $running);
        } while ($running);

        $chamadas = [
            'waiting' => 0,
            'answered' => 0,
            'ramais' => []
        ];

        $agentes = [
            'total_agentes' => 0,
            'agentes_disponiveis' => 0
        ];

        foreach ($handles as $h) {

            // chamadas
            $respChamadas = curl_multi_getcontent($h['chamadas']);
            $dataChamadas = json_decode($respChamadas, true);

            if (isset($dataChamadas['content'])) {
                foreach ($dataChamadas['content'] as $item) {

                    if ($item['state'] === 'Answered') {
                        $chamadas['answered']++;
                    }

                    if ($item['state'] === 'Waiting') {
                        $chamadas['waiting']++;

                        $partes = explode('@', $item['servingAgent']);
                        $ramal = $partes[0] ?? null;

                        if ($ramal) {
                            $chamadas['ramais'][] = $ramal;
                        }
                    }
                }
            }

            // agentes
            $respAgentes = curl_multi_getcontent($h['agentes']);
            $dataAgentes = json_decode($respAgentes, true);

            if ($dataAgentes) {
                foreach ($dataAgentes as $agent) {

                    $agentes['total_agentes']++;

                    $parsed = self::parseAgentStatus($agent);

                    if ($parsed['status'] === 'available') {
                        $agentes['agentes_disponiveis']++;
                    }
                }
            }

            curl_multi_remove_handle($multi, $h['chamadas']);
            curl_multi_remove_handle($multi, $h['agentes']);
        }

        curl_multi_close($multi);

        return [
            'chamadas' => $chamadas,
            'agentes' => $agentes
        ];
    }
}