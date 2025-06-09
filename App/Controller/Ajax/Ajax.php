<?php
namespace App\Controller\Ajax;

use App\Controller\Api\EvolutionAPI;
use App\Controller\Agendados\Agendados;
use \App\Model\Entity\Agendados as EntityAgendados;
use \App\Model\Entity\Fila as EntityFila;
use \App\Model\Entity\User as EntityUser;
use \App\Session\Login\Login;
use DateTime;

class Ajax
{


    public static function getAgendados($request)
    {
        $queryParams = $request->getQueryParams();

        $tipo = $queryParams['tipo'];
        $agendados = [];

        $results = EntityAgendados::getAgendadosByTipoAndStatus($tipo, 'agendado');

        while ($obAgendados = $results->fetchObject(EntityAgendados::class)) {
            $obUser = EntityUser::getUserById($obAgendados->id_usuario);
            $agendados[] = [
                'id' => $obAgendados->id,
                'protocolo' => $obAgendados->protocolo,
                'data' => $obAgendados->data,
                'observacao' => $obAgendados->observacao,
                'usuario' => $obUser->nome
            ];
        }
        return json_encode($agendados, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function setAgendados($request)
    {


        $postVars = $request->getPostVars();
        $tipo = $postVars['tipo'];


        $obAgendado = new EntityAgendados;
        $obAgendado->protocolo = $postVars['protocolo'];
        $obAgendado->data = $postVars['data'];
        $obAgendado->observacao = $postVars['observacao'];
        $obAgendado->tipo = $tipo;
        $obAgendado->status = 'agendado';
        $obAgendado->id_usuario = Login::getId();

        $obAgendado->cadastrar();

        switch ($tipo) {
            case 'digital':
                $number = NUMBER_DIGITAL;
                break;
            case 'suporte':
                $number = NUMBER_SUPORTE;
                break;
        }
        $mensagem = Agendados::getMessage($tipo);

        EvolutionAPI::sendMessage($mensagem, $number);
        return true;
    }

    public static function concluirAgendamento($request)
    {
        $postVars = $request->getPostVars();
        $id = $postVars['id'];

        $obAgendados = EntityAgendados::getAgendadosById($id);

        if (!$obAgendados instanceof EntityAgendados) {
            return false;
        }
        $obAgendados->status = 'concluido';
        $obAgendados->atualizar();
        $tipo = $obAgendados->tipo;

        switch ($tipo) {
            case 'digital':
                $number = NUMBER_DIGITAL;
                break;
            case 'suporte':
                $number = NUMBER_SUPORTE;
                break;
        }
        $mensagem = Agendados::getMessage($tipo);

        EvolutionAPI::sendMessage($mensagem, $number);
        return true;
    }

    public static function getFila()
    {
        $fila = [];

        $results = EntityFila::getFila();

        while ($obFila = $results->fetchObject(EntityFila::class)) {
            $obUser = EntityUser::getUserById($obFila->id_usuario);
            $fila[] = [
                'id' => $obUser->id,
                'usuario' => $obUser->nome,
                'posicao' => $obFila->posicao,
                'entrada' => $obFila->data_entrada,
            ];
        }
        return json_encode($fila, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function getFilaUser($request)
    {
        $queryParamas = $request->getQueryParams();
        $id_usuario = Login::getId();

        $isFirst = false;
        $naFila = false;

        $obFila = EntityFila::getFilaById($id_usuario);
        if ($obFila instanceof EntityFila) {
            $naFila = true;
            if ($obFila->posicao == 1) {
                $isFirst = true;
            }
        }

        $response = [
            'naFila' => $naFila,
            'isFirst' => $isFirst
        ];
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;

    }

    public static function entrarFila($request)
    {
        $id_usuario = Login::getId();

        $results = EntityFila::getFila('id_usuario !="' . $id_usuario . '"');
        while ($obFila = $results->fetchObject(EntityFila::class)) {
            $obFila->posicao = $obFila->posicao + 1;
            $obFila->atualizar();
        }
        $data = new DateTime('America/Sao_Paulo');

        $obFila = new EntityFila;
        $obFila->id_usuario = $id_usuario;
        $obFila->posicao = 1;
        $obFila->data_entrada = $data->format('Y-m-d H:i:s');
        $obFila->cadastrar();

        return true;
    }

    public static function sairFila($request)
    {
        $id_usuario = Login::getId();

        $obFila = EntityFila::getFilaById($id_usuario);

        $results = EntityFila::getFila('posicao > "' . $obFila->posicao . '"');

        while ($row = $results->fetchObject(EntityFila::class)) {
            $row->posicao = $row->posicao - 1;
            $row->atualizar();
        }

        $obFila->excluir();

        return true;
    }

    public static function passarVez($request)
    {
        $id_usuario = Login::getId();

        // Obter o usuário atual na fila
        $obFila = EntityFila::getFilaById($id_usuario);
        if (!$obFila) {
            return false; // usuário não encontrado
        }

        $posicaoAtual = $obFila->posicao;

        // Obter total de usuários na fila para definir a nova posição
        $totalUsuarios = EntityFila::getTotalUsuarios(); // Presumindo que exista método que retorna total

        // Atualizar posições dos usuários atrás do atual
        $results = EntityFila::getFila('posicao > "' . $posicaoAtual . '"');

        while ($row = $results->fetchObject(EntityFila::class)) {
            $row->posicao = $row->posicao - 1;
            $row->atualizar();
        }

        // Colocar o usuário atual na última posição da fila
        $obFila->posicao = $totalUsuarios;
        $obFila->atualizar();

        return true;
    }

}