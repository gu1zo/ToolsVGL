#!/usr/bin/php
<?php

namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\PontoAcesso as EntityPontoAcesso;
use \App\Model\Rest\APIElite;
use \App\Utils\StringVerify;
use DateTime;
use DateTimeZone;


require_once __DIR__ . '/../App/Model/Rest/APIElite.php';
function syncbanco()
{
    $nomesInvalidos = ["APAGAR", "Infopasa", ""];
    $termosProibidos = ['VEÍCULO', 'VEICULO'];

    $objResults = APIElite::getPontosAcesso('');
    foreach ($objResults as $item) {
        $codigo = $item['codcon'];
        $nome = $item['nome_con'];
        $latitude = $item['latitude'];
        $longitude = $item['longitude'];
        // Verifica se o nome é inválido
        if (empty($nome) || in_array($nome, $nomesInvalidos) || StringVerify::verificaTermoProibido($nome, $termosProibidos)) {
            continue;
        }
        // Verifica se já existe no banco
        $obPontoAcesso = EntityPontoAcesso::getPontoByCode($codigo);
        if ($obPontoAcesso instanceof EntityPontoAcesso) {
            continue;
        }

        $obPontoAcesso = new EntityPontoAcesso;
        $obPontoAcesso->nome = $nome;
        $obPontoAcesso->codigo = $codigo;
        $obPontoAcesso->latitude = $latitude;
        $obPontoAcesso->longitude = $longitude;

        $obPontoAcesso->cadastrar();
    }
    $data = new DateTime('now', new DateTimeZone('America/Sao_Paulo')); // Definir o fuso horário de Brasília
    echo "Banco sincronizado com sucesso - " . $data->format('d/m/Y H:i') . "\n"; // Formatar para o padrão brasileiro

}

//echo APIElite::getPontosAcesso();
echo syncbanco();