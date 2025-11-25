<?php
namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use App\Model\Rest\APIMultifoco;
use App\Model\Entity\Tecnicos as EntityTecnicos;
use DateTime;
use DateTimeZone;


$response = APIMultifoco::getTecnicos();

if ($response['status'] !== 200 || empty($response['body'])) {
    echo "Erro ao buscar técnicos.";
    exit;
}

// Decodifica JSON
$data = json_decode($response['body'], true);

// Verifica se tem "items"
if (!isset($data['items']) || !is_array($data['items'])) {
    echo "Nenhum técnico encontrado.";
    exit;
}

foreach ($data['items'] as $tecnico) {
    // Monta objeto/array para salvar

    $obTecnico = EntityTecnicos::getTecnicosById($tecnico['id']);
    if (!$obTecnico instanceof EntityTecnicos) {
        $obTecnico = new EntityTecnicos();
        $obTecnico->id = $tecnico['id'];
        $obTecnico->nome = $tecnico['name'];
        $obTecnico->cadastrar();
    } else {
        $obTecnico->nome = $tecnico['name'];

        $obTecnico->atualizar();
    }
}

$data = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
echo "Técnicos sincronizados - " . $data->format('d/m/Y H:i') . "\n";