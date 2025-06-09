<?php
require __DIR__ . '/../includes/app.php';

use \App\Model\Entity\Notas as EntityNotas;
use DateTime;

$arquivo = 'notas.csv';

if (($handle = fopen($arquivo, 'r')) !== false) {
    fgetcsv($handle); // pula o cabeçalho

    while (($linha = fgetcsv($handle)) !== false) {
        $dataHora = $linha[0] ?? '';
        $protocolo = $linha[2] ?? '';
        $respostaRaw = $linha[6] ?? '';
        $canalRaw = trim($linha[8] ?? '');

        $nota = 0;
        $equipe = '-';
        $agente = '-';
        $mensagem = 'cliente não deixou resposta';

        if (!empty($respostaRaw)) {
            $partes = array_map('trim', explode('|', $respostaRaw));

            foreach ($partes as $parte) {
                if (preg_match('/nota\s*(\d+)/i', $parte, $matches)) {
                    $nota = (int) $matches[1];
                } elseif (stripos($parte, 'Equipe:') !== false) {
                    $equipeExtraida = trim(str_ireplace('Equipe:', '', $parte));
                    $equipe = trim($equipeExtraida);

                    $equipeUpper = strtoupper(str_replace([' ', '-'], '', $equipe));
                    if (in_array($equipeUpper, ['GGNETABERTURA', 'GGNETCSA'])) {
                        $equipe = 'GGNET - CSA';
                    }

                } elseif (stripos($parte, 'Agent') !== false) {
                    $agenteExtraido = trim(str_ireplace('Agent', '', $parte));
                    $agente = (stripos($agenteExtraido, '{{') !== false) ? '-' : $agenteExtraido;
                } elseif (stripos($parte, 'Motivo da nota:') !== false) {
                    $mensagem = trim(substr($parte, stripos($parte, 'Motivo da nota:') + strlen('Motivo da nota:')));
                }
            }
        }

        // Normalizar canal
        $canal = '';
        $canalLower = strtolower($canalRaw);
        if (in_array($canalLower, ['ggnet telecom', 'itelfibra telecom'])) {
            $canal = 'ggnet';
        } elseif ($canalLower === 'alt - telecom') {
            $canal = 'alt';
        }

        if ($canal != '') {
            // Converter data do CSV para Y-m-d H:i:s
            $dataObj = DateTime::createFromFormat('d/m/Y H:i:s', $dataHora, new \DateTimeZone('America/Sao_Paulo'));
            $dataFormatada = $dataObj ? $dataObj->format('Y-m-d H:i:s') : (new DateTime('now', new \DateTimeZone('America/Sao_Paulo')))->format('Y-m-d H:i:s');

            // Criar entidade e cadastrar
            $obNotas = new EntityNotas;
            $obNotas->protocolo = $protocolo;
            $obNotas->nota = $nota;
            $obNotas->equipe = $equipe;
            $obNotas->mensagem = $mensagem;
            $obNotas->agente = $agente;
            $obNotas->canal = $canal;
            $obNotas->data = $dataFormatada;

            $obNotas->cadastrar();
        }
    }

    fclose($handle);
} else {
    echo "Erro ao abrir o arquivo.\n";
}