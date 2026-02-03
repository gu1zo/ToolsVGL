<?php
namespace App\Controller\Ajax;

use App\Controller\Api\EvolutionAPI;
use App\Controller\Agendados\Agendados;
use App\Controller\Api\GoogleChatAPI;
use App\Controller\OrdensServico\OrdensServico;
use \App\Model\Entity\Agendados as EntityAgendados;
use \App\Model\Entity\Fila as EntityFila;
use \App\Model\Entity\User as EntityUser;
use \App\Model\Entity\Tecnicos as EntityTecnicos;
use \App\Session\Login\Login;
use WilliamCosta\DatabaseManager\Pagination;
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

        GoogleChatAPI::sendMessage($mensagem, $number);
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

        GoogleChatAPI::sendMessage($mensagem, $number);
        return true;
    }

    public static function getFila()
    {
        $fila = [];

        $results = EntityFila::getFila();

        while ($obFila = $results->fetchObject(EntityFila::class)) {
            $obUser = EntityUser::getUserById($obFila->id_usuario);
            $posicao = ($obFila->posicao == null) ? 'PAUSA' : $obFila->posicao;
            $motivo = ($obFila->motivo == null) ? '-' : $obFila->motivo;
            $hora_pausa = ($obFila->data_pausa == null) ? '-' : $obFila->data_pausa;
            $pausa = ($obFila->pausa == 0) ? false : true;
            $fila[] = [
                'id' => $obUser->id,
                'usuario' => $obUser->nome,
                'posicao' => $posicao,
                'entrada' => $obFila->data_entrada,
                'motivo_pausa' => $motivo,
                'hora_pausa' => $hora_pausa,
                'isPaused' => $pausa
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
        $isPaused = false;

        $obFila = EntityFila::getFilaById($id_usuario);
        if ($obFila instanceof EntityFila) {
            $naFila = true;
            if ($obFila->posicao == 1) {
                $isFirst = true;
            }
            if ($obFila->pausa == 1) {
                $isPaused = true;
            }
        }

        $response = [
            'naFila' => $naFila,
            'isFirst' => $isFirst,
            'isPaused' => $isPaused
        ];
        header('Content-Type: application/json');
        echo json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit;

    }

    public static function entrarFila($request)
    {
        $id_usuario = Login::getId();

        $results = EntityFila::getFila('id_usuario !="' . $id_usuario . '" AND posicao IS NOT NULL');
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

    public static function pausarFila($request)
    {
        $postVars = $request->getPostVars();
        $motivo = $postVars['motivo'];
        $data = new DateTime('America/Sao_Paulo');

        $id_usuario = Login::getId();

        $obFila = EntityFila::getFilaById($id_usuario);

        $results = EntityFila::getFila('posicao > "' . $obFila->posicao . '" AND posicao IS NOT NULL');

        while ($row = $results->fetchObject(EntityFila::class)) {
            $row->posicao = $row->posicao - 1;
            $row->atualizar();
        }

        $obFila->motivo = $motivo;
        $obFila->pausa = 1;
        $obFila->posicao = null;
        $obFila->data_pausa = $data->format('Y-m-d H:i:s');
        $obFila->atualizar();

        return true;
    }

    public static function despausarFila($request)
    {
        $id_usuario = Login::getId();

        $results = EntityFila::getFila('id_usuario !="' . $id_usuario . '" AND posicao IS NOT NULL');
        while ($obFila = $results->fetchObject(EntityFila::class)) {
            $obFila->posicao = $obFila->posicao + 1;
            $obFila->atualizar();
        }
        $data = new DateTime('America/Sao_Paulo');

        $obFila = EntityFila::getFilaById($id_usuario);
        $obFila->posicao = 1;
        $obFila->motivo = null;
        $obFila->pausa = 0;
        $obFila->data_pausa = null;
        $obFila->atualizar();
        return true;
    }


    public static function sairFila($request)
    {
        $id_usuario = Login::getId();

        $obFila = EntityFila::getFilaById($id_usuario);

        $results = EntityFila::getFila('posicao > "' . $obFila->posicao . '" AND posicao IS NOT NULL');

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
        $results = EntityFila::getFila('posicao > "' . $posicaoAtual . '" AND posicao IS NOT NULL');

        while ($row = $results->fetchObject(EntityFila::class)) {
            $row->posicao = $row->posicao - 1;
            $row->atualizar();
        }

        // Colocar o usuário atual na última posição da fila
        $obFila->posicao = $totalUsuarios;
        $obFila->atualizar();

        return true;
    }

    public static function getTecnicos($request)
    {
        $results = [];

        // Obter os parâmetros da query
        $queryParams = $request->getQueryParams();

        $paginaAtual = $queryParams['page'] ?? 1;
        $search = $queryParams['search'] ?? '';

        // Montar o filtro de busca
        $where = null;
        if (!empty($search)) {
            $where = 'nome LIKE "%' . addslashes($search) . '%"';
        }

        // Obter o total de registros com o filtro
        $quantidadetotal = EntityTecnicos::getTecnicos($where, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        // Configuração da paginação
        $obPagination = new Pagination($quantidadetotal, $paginaAtual, 100);

        // Buscar os registros filtrados
        $res = EntityTecnicos::getTecnicos($where, 'nome ASC', $obPagination->getLimit());

        // Construir a lista de resultados
        while ($obTecnico = $res->fetchObject(EntityTecnicos::class)) {
            $results[] = [
                'id' => $obTecnico->id,
                'text' => $obTecnico->nome
            ];
        }

        // Verificar se há mais páginas
        $hasMore = $paginaAtual * 15 < $quantidadetotal;
        // Estrutura JSON com resultados e paginação
        $response = [
            'results' => $results,
            'pagination' => [
                'more' => $hasMore
            ]
        ];


        // Retornar JSON
        return json_encode($response);
    }

    public static function getOnu($request)
    {
        $queryParams = $request->getQueryParams();
        $pppoe = $queryParams['pppoe'] ?? null;

        if (!$pppoe) {
            return json_encode(['error' => 'PPPoE não informado']);
        }

        $data = OrdensServico::getOnu($pppoe);
        return $data;
    }

    public static function getRoteador($request)
    {
        $queryParams = $request->getQueryParams();
        $pppoe = $queryParams['pppoe'] ?? null;

        if (!$pppoe) {
            return json_encode(['error' => 'PPPoE não informado']);
        }

        $data = OrdensServico::getRoteador($pppoe);
        return $data;
    }


}