<?php
namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';
use App\Model\Rest\APISippulse;
use App\Model\Entity\Ligacoes as EntityLigacoes;
use DateTime;
use DateTimeZone;
function timeToSeconds($time)
{
    if (!$time)
        return 0;
    $parts = explode(':', $time);
    if (count($parts) !== 3)
        return 0;
    return ($parts[0] * 3600) + ($parts[1] * 60) + $parts[2];
}
$filas = [
    ["fila" => "FILA_CSA_GGNET", "ivrId" => 15],
    ["fila" => "FILA_CSA_N2_GGNET", "ivrId" => 15],
    ["fila" => "FILA_CSA_N2_ALT", "ivrId" => 20],
    ["fila" => "FILA_SAC_FINANCEIRO_GGNET", "ivrId" => 24],
    ["fila" => "FILA_SAC_FINANCEIRO_ALT", "ivrId" => 21],
    ["fila" => "FILA_OPERACIONAL_EVO", "ivrId" => null],
    ["fila" => "FILA_SAC_FINANCEIRO_EVO", "ivrId" => null],
    /* 
        ["fila" => "FILA_SAC_NEGOCIACAO_GGNET", "ivrId" => null],
        ["fila" => "FILA_SAC_RETENCAO_GGNET", "ivrId" => null], ["fila" => "FILA_RETENCAO_ALT", "ivrId" => null], ["fila" => "FILA_COMERCIAL_CACADOR_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_0800_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_TREZE_TILIAS_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_SALTO_VELOSO_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_RIO_DO_SUL_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_IRINEOPOLIS_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_PAPANDUVA_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_ITUPORANGA_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_CANOINHAS_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_ITAIOPOLIS_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_SAO_MATEUS_DO_SUL_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_SANTA_CECILIA_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_UNIÃO_DA_VITORIA_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_VIDEIRA_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_FRAIBURGO_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_TANGARA_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_PINHEIRO_PRETO_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_IBIRAMA_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_PRESIDENTE_GETULIO_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_TRES_BARRAS_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_CAMPOS_NOVOS_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_CURITIBANOS_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_ITAJAI_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_ITAPOA_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_IRATI_GGNET", "ivrId" => 16], ["fila" => "FILA_COMERCIAL_CHAPECO_ALT", "ivrId" => 25], ["fila" => "FILA_COMERCIAL_CURITIBA_ALT", "ivrId" => 25], ["fila" => "FILA_COMERCIAL_ITAPERUCU_RBS_ALT", "ivrId" => 25], ["fila" => "FILA_COMERCIAL_0800_ALT", "ivrId" => 25], ["fila" => "FILA_COMERCIAL_CATANDUVAS_ALT", "ivrId" => 25], ["fila" => "FILA_COMERCIAL_FLORIANOPOLIS_ALT", "ivrId" => 25], ["fila" => "FILA_COMERCIAL_ADU_MDT_PYE_QND_ALT", "ivrId" => 25], ["fila" => "FILA_COMERCIAL_RIO_NEGRINHO_ALT", "ivrId" => 25], ["fila" => "FILA_COMERCIAL_SAO_BENTO_DO_SUL_ALT", "ivrId" => 25], ["fila" => "FILA_COMERCIAL_RIO_NEGRO_ALT", "ivrId" => 25], // Demais (null) ["fila" => "FILA_NOC_CDR_ALT", "ivrId" => null], ["fila" => "FILA_NOC_MADRU_ALT", "ivrId" => null], ["fila" => "FILA_NOC_SUPERVISAO_ALT", "ivrId" => null], ["fila" => "FILA_VOC_GGNET", "ivrId" => null], ["fila" => "FILA_TI_GGNET", "ivrId" => null], ["fila" => "FILA_COBRANCA_GGNET", "ivrId" => null], ["fila" => "FILA_ESTOQUE_CACADOR_GGNET", "ivrId" => null], ["fila" => "FILA_CANCELAMENTO_GGNET", "ivrId" => null], ["fila" => "FILA_LOGISTICA_CACADOR_GGNET", "ivrId" => null], ["fila" => "FILA_MONITORAMENTO_ALT", "ivrId" => null], ["fila" => "FILA_FATURAMENTO_CHAPECO_ALT", "ivrId" => null], ["fila" => "FILA_ESTOQUE_SAO_JOSE_ALT", "ivrId" => null], ["fila" => "FILA_INADINPLENCIA_ALT", "ivrId" => null], ["fila" => "FILA_LOGISTICA_VIDEIRA_GGNET", "ivrId" => null], ["fila" => "FILA_NOC_COMERCIAL_ALT", "ivrId" => null], ["fila" => "FILA_OUVIDORIA_ALT", "ivrId" => null], ["fila" => "FILA_SUP_0800_C_ALT", "ivrId" => null], ["fila" => "FILA_SUP_0800_N_ALT", "ivrId" => null], */
];
foreach ($filas as $item) {
    $queue = $item['fila'];
    $ivrId = $item['ivrId'];
    $dataConsulta = new DateTime('yesterday', new DateTimeZone('America/Sao_Paulo'));
    $page = 0;
    $size = 100;
    $totalPages = 1;
    do {
        $response = APISippulse::getChamadas($dataConsulta->format('Y-m-d'), $page, $size, $queue);
        $content = $response['content'] ?? [];
        foreach ($content as $ligacao) {
            $status = $ligacao['status'] ?? null;

            if ($status != 'answered' && $status != 'abandoned')
                continue;

            $obLigacoes = EntityLigacoes::getLigacoesByUuid($ligacao['uuid']);
            if (!$obLigacoes instanceof EntityLigacoes) {
                $nota = null;
                if ($ivrId !== null) {
                    $nota = APISippulse::getNota($dataConsulta->format('Y-m-d'), 0, 20, $ivrId, $ligacao['callerId'], $ligacao['uuid']);
                    if ($nota == 't') {
                        $nota = null;
                    }
                }
                $obLigacoes = new EntityLigacoes();
                $obLigacoes->data = $ligacao['startStamp'] ?? null;
                $obLigacoes->tempo_fila = $ligacao['waitingDuration'] ?? 0;
                $tempoAtendimento = $ligacao['queueCallDuration'] ?? $ligacao['duration'] ?? '00:00:00';
                $obLigacoes->tempo_atendimento = timeToSeconds($tempoAtendimento);
                $obLigacoes->responsavel = $ligacao['userName'] ?? null;
                $obLigacoes->status = $status;
                $obLigacoes->fila = $queue;
                $obLigacoes->numero = $ligacao['callerId'] ?? null;
                $obLigacoes->uuid = $ligacao['uuid'];
                $obLigacoes->nota = $nota;
                $obLigacoes->cadastrar();
            }
        }
        $totalPages = $response['totalPages'] ?? 1;
        $page++;
    } while ($page < $totalPages);
}
$dataAtual = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
echo "Ligações de: " . $dataConsulta->format('d/m/Y') . " sincronizadas - " . $dataAtual->format('d/m/Y H:i') . "\n";