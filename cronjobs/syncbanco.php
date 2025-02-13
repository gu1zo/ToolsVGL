#!/usr/bin/php
<?php

namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\PontoAcesso as EntityPontoAcesso;
use \App\Model\Entity\Cidades as EntityCidades;
use \App\Model\Rest\APIElite;
use App\Model\Rest\APIGeogrid;
use \App\Utils\StringVerify;
use DateTime;
use DateTimeZone;


require_once __DIR__ . '/../App/Model/Rest/APIElite.php';
function syncbanco()
{
    $nomesInvalidos = ["APAGAR", "Infopasa", ""];
    $termosProibidos = ['VEÍCULO', 'VEICULO'];

    $objResults = APIElite::getCidades();
    foreach ($objResults as $item) {
        $cidade = $item['nome_cid'];

        $obCidade = EntityCidades::getCidadesByName($cidade);

        if ($obCidade instanceof EntityCidades) {
            continue;
        }

        $obCidade = new EntityCidades;
        $obCidade->nome = $cidade;
        $obCidade->massiva = 0;
        $obCidade->cadastrar();
    }


    //$objResults = APIElite::getPontosAcesso('');

    $json1 = APIGeogrid::getCaixas('');
    $json2 = APIElite::getPontosAcesso('');
    $map1 = [];
    foreach ($json1 as $item) {
        $sigla = $item["dados"]["sigla"];
        $map1[$sigla] = [
            "latitude" => $item["dados"]["latitude"],
            "longitude" => $item["dados"]["longitude"]
        ];
    }

    // Processar JSON 2 com filtragem
    $resultado = [];
    foreach ($json2 as $item) {
        $nomeCon = $item["nome_con"];

        // Se o nome estiver na lista de nomes inválidos, pula para o próximo
        if (in_array($nomeCon, $nomesInvalidos)) {
            continue;
        }

        // Se o nome contiver qualquer termo proibido, pula para o próximo
        foreach ($termosProibidos as $termo) {
            if (stripos($nomeCon, $termo) !== false) {
                continue 2; // Sai direto do loop principal
            }
        }

        // Se existir no JSON 1, usa os dados de lá
        if (isset($map1[$nomeCon])) {
            $resultado[] = [
                "codcon" => $item["codcon"],
                "nome_con" => $item["nome_con"],
                "latitude" => $map1[$nomeCon]["latitude"],
                "longitude" => $map1[$nomeCon]["longitude"]
            ];
        } else {
            // Caso contrário, usa os dados do próprio JSON 2
            $resultado[] = [
                "codcon" => $item["codcon"],
                "nome_con" => $item["nome_con"],
                "latitude" => $item["latitude"],
                "longitude" => $item["longitude"]
            ];
        }
    }

    foreach ($resultado as $item) {
        $codigo = $item['codcon'];
        $nome = $item['nome_con'];
        $latitude = $item['latitude'];
        $longitude = $item['longitude'];

        // Verifica se já existe no banco
        $obPontoAcesso = EntityPontoAcesso::getPontoByCode($codigo);
        if ($obPontoAcesso instanceof EntityPontoAcesso) {
            $obPontoAcesso->nome = $nome;
            $obPontoAcesso->codigo = $codigo;
            $obPontoAcesso->latitude = $latitude;
            $obPontoAcesso->longitude = $longitude;
            $obPontoAcesso->atualizar();
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