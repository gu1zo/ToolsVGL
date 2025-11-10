<?php
namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use App\Model\Rest\APIMultifoco;
use App\Model\Entity\OrdensServico as EntityOrdemServico;
use App\Model\Entity\ImagensOrdensServico as EntityImagensOrdemServico;
use App\Model\Entity\EquipamentosOrdensServico as EntityEquipamentosOrdemServico;
use DateTime;
use DateTimeZone;

$data = new DateTime('yesterday', new DateTimeZone('America/Sao_Paulo'));


$response = APIMultifoco::getOrdensServicoByDate($data->format('Y-m-d'), 'F');

foreach ($response as $item) {
    $OrdemServico = APIMultifoco::getOsById($item);
    $imagens = APIMultifoco::getImagesOsById($item);
    $equipamentos = APIMultifoco::getEquipamentosOsById($item);
    $obOrdemServico = EntityOrdemServico::getOrdemServicoByNumber($item);

    if (!$obOrdemServico instanceof EntityOrdemServico) {
        $obOrdemServico = new EntityOrdemServico();
        $obOrdemServico->numero = $OrdemServico['numero'];
        $data = DateTime::createFromFormat('d/m/Y H:i', $OrdemServico['data']);
        $obOrdemServico->data = $data->format('Y-m-d H:i:s');
        $obOrdemServico->id_tecnico = $OrdemServico['id-tecnico'];
        $obOrdemServico->nome_tecnico = $OrdemServico['nome-tecnico'];
        $obOrdemServico->cliente = $OrdemServico['cliente'];
        $obOrdemServico->tipo = $OrdemServico['tipo'];
        $obOrdemServico->obs = $OrdemServico['obs'];
        $obOrdemServico->pppoe = $OrdemServico['pppoe'];
        $obOrdemServico->solicitado = $OrdemServico['solicitado'];
        $obOrdemServico->plano = $OrdemServico['plano'];
        $obOrdemServico->tipo_fechamento = $OrdemServico['tipo_fechamento'];
        $obOrdemServico->tempo = $OrdemServico['tempo'];
        $obOrdemServico->cadastrar();

        foreach ($imagens as $i) {
            $obImagensOrdemServico = new EntityImagensOrdemServico();
            $obImagensOrdemServico->idOs = $obOrdemServico->id;
            $obImagensOrdemServico->url = $i['url'];
            $obImagensOrdemServico->descricao = $i['title'];
            $obImagensOrdemServico->cadastrar();

        }
        foreach ($equipamentos as $e) {
            $obEquipamentosOrdemServico = new EntityEquipamentosOrdemServico();
            $obEquipamentosOrdemServico->idOs = $obOrdemServico->id;
            $obEquipamentosOrdemServico->item = $e['nome'];
            $obEquipamentosOrdemServico->qtd = is_numeric($e['qtd']) ? (double) $e['qtd'] : 0.0;
            $obEquipamentosOrdemServico->cadastrar();
        }

    }
}

$dataAtual = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
echo "OSs de ontem sincronizadas - " . $dataAtual->format('d/m/Y H:i') . "\n";