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
    "FILA_CSA_GGNET",
    "FILA_CSA_N2_GGNET",
    "FILA_NOC_CDR_ALT",
    "FILA_NOC_MADRU_ALT",
    "FILA_NOC_SUPERVISAO_ALT",
    "FILA_VOC_GGNET",
    "FILA_TI_GGNET",
    "FILA_SAC_FINANCEIRO_GGNET",
    "FILA_COMERCIAL_CACADOR_GGNET",
    "FILA_COMERCIAL_0800_GGNET",
    "FILA_SAC_RETENCAO_GGNET",
    "FILA_SAC_NEGOCIACAO_GGNET",
    "FILA_COBRANCA_GGNET",
    "FILA_ESTOQUE_CACADOR_GGNET",
    "FILA_CANCELAMENTO_GGNET",
    "FILA_LOGISTICA_CACADOR_GGNET",
    "FILA_MONITORAMENTO_ALT",
    "FILA_COMERCIAL_TREZE_TILIAS_GGNET",
    "FILA_COMERCIAL_SALTO_VELOSO_GGNET",
    "FILA_COMERCIAL_CHAPECO_ALT",
    "FILA_FATURAMENTO_CHAPECO_ALT",
    "FILA_ESTOQUE_SAO_JOSE_ALT",
    "FILA_CSA_N2_ALT",
    "FILA_INADINPLENCIA_ALT",
    "FILA_COMERCIAL_RIO_DO_SUL_GGNET",
    "FILA_COMERCIAL_IRINEOPOLIS_GGNET",
    "FILA_COMERCIAL_PAPANDUVA_GGNET",
    "FILA_COMERCIAL_ITUPORANGA_GGNET",
    "FILA_COMERCIAL_CANOINHAS_GGNET",
    "FILA_COMERCIAL_ITAIOPOLIS_GGNET",
    "FILA_COMERCIAL_MAFRA_ALT",
    "FILA_COMERCIAL_JOACABA_ALT",
    "FILA_COMERCIAL_SAO_MATEUS_DO_SUL_GGNET",
    "FILA_COMERCIAL_SANTA_CECILIA_GGNET",
    "FILA_COMERCIAL_UNIÃO_DA_VITORIA_GGNET",
    "FILA_COMERCIAL_VIDEIRA_GGNET",
    "FILA_COMERCIAL_FRAIBURGO_GGNET",
    "FILA_COMERCIAL_TANGARA_GGNET",
    "FILA_COMERCIAL_PINHEIRO_PRETO_GGNET",
    "FILA_LOGISTICA_VIDEIRA_GGNET",
    "FILA_NOC_COMERCIAL_ALT",
    "FILA_SAC_FINANCEIRO_ALT",
    "FILA_COMERCIAL_CURITIBA_ALT",
    "FILA_COMERCIAL_ITAPERUCU_RBS_ALT",
    "FILA_RETENCAO_ALT",
    "FILA_OUVIDORIA_ALT",
    "FILA_COMERCIAL_0800_ALT",
    "FILA_SUP_0800_C_ALT",
    "FILA_SUP_0800_N_ALT",
    "FILA_COMERCIAL_IBIRAMA_GGNET",
    "FILA_COMERCIAL_PRESIDENTE_GETULIO_GGNET",
    "FILA_COMERCIAL_TRES_BARRAS_GGNET",
    "FILA_COMERCIAL_CATANDUVAS_ALT",
    "FILA_COMERCIAL_FLORIANOPOLIS_ALT",
    "FILA_COMERCIAL_CAMPOS_NOVOS_GGNET",
    "FILA_COMERCIAL_CURITIBANOS_GGNET",
    "FILA_COMERCIAL_ADU_MDT_PYE_QND_ALT",
    "FILA_COMERCIAL_RIO_NEGRINHO_ALT",
    "FILA_COMERCIAL_SAO_BENTO_DO_SUL_ALT",
    "FILA_COMERCIAL_ITAJAI_GGNET",
    "FILA_COMERCIAL_RIO_NEGRO_ALT",
    "FILA_COMERCIAL_ITAPOA_GGNET",
    "FILA_COMERCIAL_IRATI_GGNET"
];

foreach ($filas as $queue) {

    $dataConsulta = new DateTime('yesterday', new DateTimeZone('America/Sao_Paulo'));

    $page = 0;
    $size = 100;
    $totalPages = 1;

    do {

        $response = APISippulse::getChamadas(
            $dataConsulta->format('Y-m-d'),
            $page,
            $size,
            $queue
        );

        $content = $response['content'] ?? [];

        foreach ($content as $ligacao) {

            $obLigacoes = EntityLigacoes::getLigacoesByUuid($ligacao['uuid']);
            if (!$obLigacoes instanceof EntityLigacoes) {
                $obLigacoes = new EntityLigacoes();
                $obLigacoes->data = $ligacao['startStamp'] ?? null;
                $obLigacoes->tempo_fila = $ligacao['waitingDuration'] ?? 0;

                $tempoAtendimento = $ligacao['queueCallDuration'] ?? $ligacao['duration'] ?? '00:00:00';
                $obLigacoes->tempo_atendimento = timeToSeconds($tempoAtendimento);

                $obLigacoes->responsavel = $ligacao['userName'] ?? null;
                $obLigacoes->status = $ligacao['status'] ?? null;
                $obLigacoes->fila = $queue;
                $obLigacoes->numero = $ligacao['callerId'] ?? null;
                $obLigacoes->uuid = $ligacao['uuid'];
                $obLigacoes->cadastrar();
            }
        }

        $totalPages = $response['totalPages'] ?? 1;

        $page++;

    } while ($page < $totalPages);
}

$dataAtual = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
echo "Ligações de: " . $dataConsulta->format('d/m/Y') . " sincronizadas - " . $dataAtual->format('d/m/Y H:i') . "\n";