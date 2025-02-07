<?php

namespace App\Controller\Evento;

use App\Model\Entity\Evento as EntityEvento;
use App\Model\Entity\Manutencao as EntityManutencao;
use App\Model\Entity\PontoAcessoAfetado as EntityPontoAcessoAfetado;
use App\Model\Entity\PontoAcesso as EntityPontoAcesso;
use App\Model\Entity\EventoConclusao as EntityConclusao;
use Exception;
use DateTime;

class SyncBanco
{

    public static function syncbanco()
    {

        function formatDate($dateString)
        {
            // Tenta criar um objeto DateTime a partir de diferentes formatos conhecidos
            $dateFormats = ["Y/m/d H:i", "Y-m-d H:i", "Y-m-d\TH:i", "d/m/Y H:i"];

            foreach ($dateFormats as $format) {
                $dateTime = DateTime::createFromFormat($format, $dateString);
                if ($dateTime && $dateTime->format($format) === $dateString) {
                    return $dateTime->format("Y-m-d\TH:i");
                }
            }

            return null; // Retorna null se a data não for válida
        }
        define('HOST', 'ws4.altcloud.net.br');
        define('USER', 'ggnet_manutencao');
        define('PASS', 'pD1!bmuR8iotgR?l');
        define('BASE', 'ggnet_manutencao');


        // Conectar ao banco de dados
        $conn = mysqli_connect(HOST, USER, PASS, BASE);

        // Verificar a conexão
        if (!$conn) {
            die("Falha na conexão: " . mysqli_connect_error());
        }
        $sql = "SELECT * from manutencao";
        $res = $conn->query($sql);
        $qtd = $res->num_rows;

        if ($qtd > 0) {
            while ($obOld = $res->fetch_object()) {
                $obEvento = new EntityEvento;

                if ($obOld->tipo == 'Manutencao' || $obOld->tipo == 'Evento' || $obOld->tipo == 'Solicitação emergencial') {
                    if ($obOld->tipo == 'Solicitação emergencial') {
                        $obOld->tipo = 'emergencial';
                    }
                    $obEvento->tipo = $obOld->tipo;
                } else {
                    $obEvento->tipo = 'Manutencao';
                }
                $obEvento->protocolo = $obOld->protocolo;
                $obEvento->dataInicio = formatDate($obOld->dataInicio);
                $obEvento->dataFim = $obOld->dataFim != null ? formatDate($obOld->dataFim) : formatDate($obOld->dataInicio);
                $obEvento->regional = $obOld->regional;
                $obEvento->observacao = $obOld->observacao;
                $obEvento->id_usuario_criador = 1;
                if ($obOld->status = 'Concluida') {
                    $obEvento->status = 'concluido';
                } else if ($obOld->status = 'Em analise(Regional)') {
                    $obEvento->status = 'em analise';
                } else if ($obOld->status = 'Em analise(Interno)') {
                    $obEvento->status = 'em analise';
                }


                $obEvento->email = $obOld->email;
                $obEvento->clientes = $obOld->afetados ?? '[]';
                $obEvento->cadastrar();
                $obEvento->atualizar();

                if ($obEvento->tipo = 'Manutencao') {
                    $obManutencao = new EntityManutencao;

                    $obManutencao->evento_id = $obEvento->id;
                    $obManutencao->dataPrevista = formatDate($obOld->dataPrevista);
                    $obManutencao->cadastrar();
                }


                $pontosAcesso = explode(', ', $obOld->pontoAcesso);

                foreach ($pontosAcesso as $k) {
                    try {
                        $obPontoAcesso = EntityPontoAcesso::getCodeByName(trim($k));

                        if ($obPontoAcesso) {
                            $obPontoAcessoAfetado = new EntityPontoAcessoAfetado;

                            $obPontoAcessoAfetado->evento_id = $obEvento->id;
                            $obPontoAcessoAfetado->ponto_acesso_codigo = $obPontoAcesso->codigo;
                            $obPontoAcessoAfetado->cadastrar();
                        }
                    } catch (Exception $e) {
                        echo '<pre>';
                        print_r($e);
                        echo '</pre>';
                    }
                }
            }
        }

        $sql = "SELECT * FROM manutencaoConclusao";
        $res = $conn->query($sql);
        $qtd = $res->num_rows;

        if ($qtd > 0) {
            while ($obOldConclusao = $res->fetch_object()) {
                $obEvento = EntityEvento::getEventoByProtocol($obOldConclusao->protocolo);


                if ($obEvento instanceof EntityEvento) {
                    $obConclusao = EntityConclusao::getEventoConclusaoById($obEvento->id);

                    if (!$obConclusao instanceof EntityConclusao) {
                        $obConclusao = new EntityConclusao;
                        $obConclusao->evento_id = $obEvento->id;
                        $obConclusao->motivo = $obOldConclusao->motivo;
                        $obConclusao->forca_maior = $obOldConclusao->forca_maior == 1 ? 1 : 0;
                        $obConclusao->comentario = $obOldConclusao->comentario;

                        $obConclusao->cadastrar();
                    }
                }
            }
        }

        echo 'DEU BOA PIA';
    }

}