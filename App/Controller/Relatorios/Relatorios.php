<?php

namespace App\Controller\Relatorios;

use App\Model\Entity\Evento as EntityEvento;
use App\Model\Entity\EventoConclusao as EntityEventoConclusao;
use App\Model\Entity\Backbone as EntityBackbone;
use App\Model\Entity\Manutencao as EntityManutencao;
use App\Model\Entity\PontoAcesso as EntityPontoAcesso;
use App\Model\Entity\PontoAcessoAfetado as EntityPontoAcessoAfetado;
use DateTime;

class Relatorios
{
    public static function getEventosCSV($request)
    {
        date_default_timezone_set('America/Sao_Paulo');
        $dataAtual = new DateTime();
        $dataInicio = (clone $dataAtual)->modify('-11 months')->modify('first day of this month');
        $dataInicioStr = $dataInicio->format('Y-m-01 00:00:00');
        $dataAtualStr = $dataAtual->format('Y-m-d 23:59:59');

        $resultados = EntityEvento::getEventosByDateAndMonth($dataInicioStr, $dataAtualStr);
        $data = [
            ['protocolo', 'tipo', 'dataInicio', 'dataPrevista', 'dataFim', 'regional', 'total_afetados', 'backbone', 'motivo', 'forca_maior', 'pontosAcesso']
        ];

        while ($obEvento = $resultados->fetchObject(EntityEvento::class)) {
            if ($obEvento->status == 'concluido') {
                $id = $obEvento->id;

                $total_afetados = count(json_decode($obEvento->clientes, true));

                $dataPrevista = '';
                $backbone = '';
                $forca_maior = '';
                $motivo = '';
                $pontosAcesso = '';

                switch ($obEvento->tipo) {
                    case 'manutencao':
                        $obManutencao = EntityManutencao::getManutencaoById($id);

                        if ($obManutencao instanceof EntityManutencao) {
                            $dataPrevista = $obManutencao->dataPrevista;
                        }
                        break;
                    case 'backbone':
                        $obBackbone = EntityBackbone::getBackboneById($id);

                        if ($obBackbone instanceof EntityBackbone) {
                            $backbone = $obBackbone->backbone;
                        }
                        break;
                }

                $obEventoConclusao = EntityEventoConclusao::getEventoConclusaoById($id);
                if (!$obEventoConclusao instanceof EntityEventoConclusao) {
                    continue;
                }
                $forca_maior = $obEventoConclusao->forca_maior == 1 ? 'SIM' : 'NÃO';
                $motivo = $obEventoConclusao->motivo;

                $obPontoAcessoAfetado = EntityPontoAcessoAfetado::getPontoAcessoAfetadoById($id);
                $pontos = [];
                while ($row = $obPontoAcessoAfetado->fetchObject(EntityPontoAcessoAfetado::class)) {
                    $obPontoAcesso = EntityPontoAcesso::getPontoByCode($row->ponto_acesso_codigo);

                    if ($obPontoAcesso instanceof EntityPontoAcesso) {
                        $pontos[] = $obPontoAcesso->nome;
                    }
                }
                if (!empty($pontos)) {
                    $pontosAcesso = implode(', ', $pontos);
                }




                $data[] = [
                    $obEvento->protocolo,
                    $obEvento->tipo,
                    $obEvento->dataInicio,
                    $dataPrevista,
                    $obEvento->dataFim,
                    $obEvento->regional,
                    $total_afetados,
                    $backbone,
                    $motivo,
                    $forca_maior,
                    $pontosAcesso
                ];
            }
        }

        // Nome do arquivo CSV

        $filename = "Relatório " . date('d-m-Y') . ".csv";

        // Definir cabeçalhos para download
        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Abrir saída do PHP como arquivo
        $output = fopen('php://output', 'w');

        // Garantir que o CSV seja gerado com encoding correto
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM para UTF-8

        // Escrever os dados no CSV
        foreach ($data as $row) {
            fputcsv($output, $row, ';'); // Usa ";" como delimitador
        }

        // Fechar arquivo
        fclose($output);
        exit;

    }
}