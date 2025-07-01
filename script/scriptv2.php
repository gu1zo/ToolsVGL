<?php
$arquivo = 'notas.csv';

$notasPorMes = [];

if (($handle = fopen($arquivo, 'r')) !== false) {
    // Ler cabeçalho
    $cabecalho = fgetcsv($handle, 0, ",");

    while (($linha = fgetcsv($handle, 0, ",")) !== false) {
        $data = trim($linha[0] ?? '');
        $equipe = strtolower(trim($linha[6] ?? ''));
        $nota = intval($linha[5] ?? 0);

        // Verificar se a equipe contém NOC ou CSA
        if (str_contains($equipe, 'financeiro')) {
            // Converter data para mês/ano
            $dataObj = DateTime::createFromFormat('j/n/Y', $data);
            if ($dataObj) {
                $mesAno = $dataObj->format('m/Y');

                // Agrupar as notas por mês
                if (!isset($notasPorMes[$mesAno])) {
                    $notasPorMes[$mesAno] = [
                        'total' => 0,
                        'quantidade' => 0
                    ];
                }

                $notasPorMes[$mesAno]['total'] += $nota;
                $notasPorMes[$mesAno]['quantidade'] += 1;
            }
        }
    }
    fclose($handle);
}

// Calcular e exibir a média por mês
foreach ($notasPorMes as $mes => $dados) {
    $media = $dados['total'] / $dados['quantidade'];
    echo "Mês $mes - Média da Nota: " . number_format($media, 2, ',', '.') . PHP_EOL;
}
?>