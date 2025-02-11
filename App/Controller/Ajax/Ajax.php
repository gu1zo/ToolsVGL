<?php
namespace App\Controller\Ajax;

use App\Model\Entity\PontoAcesso as EntityPontoAcesso;
use App\Model\Entity\Comentarios as EntityComentarios;
use App\Model\Entity\Evento as EntityEvento;
use App\Model\Entity\Alteracoes as EntityAlteracoes;
use App\Model\Entity\User as EntityUser;
use App\Utils\StringManipulation;
use App\Session\Login\Login;
use App\Controller\Evento\Evento;
use App\Controller\Api\EvolutionAPI;
use WilliamCosta\DatabaseManager\Pagination;
use DateTime;


class Ajax
{
    /**
     * Método responsável por retornar os dados para o select2 de pontos de Acesso
     * @param Request $request
     * @return json
     */
    public static function getPontosAcesso($request)
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
        $quantidadetotal = EntityPontoAcesso::getPontosAcesso($where, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        // Configuração da paginação
        $obPagination = new Pagination($quantidadetotal, $paginaAtual, 100);

        // Buscar os registros filtrados
        $res = EntityPontoAcesso::getPontosAcesso($where, 'nome ASC', $obPagination->getLimit());

        // Construir a lista de resultados
        while ($obPontoAcesso = $res->fetchObject(EntityPontoAcesso::class)) {
            $results[] = [
                'id' => $obPontoAcesso->codigo,
                'text' => $obPontoAcesso->nome
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

    public static function getEvents($request)
    {
        // Inicializar resultados
        $results = [];

        // Obter os parâmetros da query
        $queryParams = $request->getQueryParams();
        $status = $queryParams['status'];

        // Parâmetros para paginação e pesquisa
        $paginaAtual = $queryParams['start'] ?? 0;  // 'start' é o índice da primeira linha da página
        $length = $queryParams['length'] ?? 10;    // 'length' é a quantidade de itens por página
        $search = $queryParams['search'] ?? '';  // 'search.value' é o termo de busca

        // Montar o filtro de busca
        $where = null;
        if ($status != 'todos') {
            $where = 'status ="' . $status . '"';
        }
        if (!empty($search)) {
            $where = 'protocolo LIKE "%' . addslashes($search) . '%"';
        }

        // Obter o total de registros com o filtro
        $quantidadetotal = EntityEvento::getEvento($where, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        // Configuração da paginação
        $obPagination = new Pagination($quantidadetotal, $paginaAtual, $length); // Paginação ajustada

        // Buscar os registros filtrados e com a paginação aplicada
        $res = EntityEvento::getEvento($where, 'protocolo ASC', $obPagination->getLimit());
        // Construir a lista de resultados
        while ($obEvento = $res->fetchObject(EntityEvento::class)) {
            $results[] = [
                'status' => str_replace(' ', '-', $obEvento->status),
                'protocolo' => $obEvento->protocolo,
                'tipo' => (new StringManipulation)->formatarTipo($obEvento->tipo),
                'horario-inicial' => $obEvento->dataInicio,
                'pontos-acesso' => Evento::getPontosAcessoTable($obEvento->id),
                'regional' => $obEvento->regional,
                'observacao' => $obEvento->observacao,
                'email' => $obEvento->email ? '✔' : '❌',
                'id' => $obEvento->id
            ];
        }

        // Determinar se há mais registros
        $hasMore = ($paginaAtual + 1) * $length < $quantidadetotal;

        // Construir a resposta para DataTables
        $response = [
            'draw' => $queryParams['draw'] ?? 1, // O valor de "draw" enviado no request
            'recordsTotal' => $quantidadetotal,  // Total de registros sem filtro
            'recordsFiltered' => $quantidadetotal, // Total de registros após filtro (pode ser ajustado se houver filtro)
            'data' => $results,  // Dados da tabela
            'pagination' => [
                'more' => $hasMore
            ]
        ];

        // Retornar a resposta em formato JSON
        return json_encode($response);
    }

    /**
     * Método responsável por retornar os pontos de acesso selecionados de evento x
     * @param Request $request
     * @return bool|string
     */
    public static function getPontosAcessoEdit($request)
    {
        $response = [];
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obPontoAcesso = new EntityPontoAcesso;
        $results = $obPontoAcesso->getCodeAndNameById($id);

        while ($row = $results->fetchObject(EntityPontoAcesso::class)) {
            $response[] = [
                'id' => $row->codigo,
                'text' => $row->nome
            ];
        }
        // Retornar JSON
        return json_encode($response);
    }

    public static function getComentarios($request)
    {
        $queryParams = $request->getQueryParams();

        $id = $queryParams['id'];
        $comentarios = [];
        $num = 1;

        $results = EntityComentarios::getComentariosByEventoId($id);

        while ($obComentarios = $results->fetchObject(EntityComentarios::class)) {
            $obUser = EntityUser::getUserById($obComentarios->id_usuario_criador);
            $comentarios[] = [
                'id' => $obComentarios->id,
                'num' => $num,
                'data' => $obComentarios->data,
                'autor' => $obUser->nome
            ];
            $num++;
        }
        return json_encode($comentarios, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
    public static function setAlteracao($id, $alteracao)
    {
        $data = new DateTime('America/Sao_Paulo');
        $obAlteracao = new EntityAlteracoes;
        $obAlteracao->evento_id = $id;
        $obAlteracao->alteracao = $alteracao;
        $obAlteracao->data = $data->format('Y-m-d H:i');
        $obAlteracao->id_usuario_criador = Login::getId();
        $obAlteracao->cadastrar();
    }
    public static function setComentarios($request)
    {
        $queryParams = $request->getQueryParams();
        $postVars = $request->getPostVars();

        $id = $queryParams['id'];
        $comentario = $postVars['comentario'] ?? '';
        $id_usuario_criador = Login::getId();
        $data = (new DateTime('America/Sao_Paulo'))->format('Y-m-d H:i');

        $obComentario = new EntityComentarios;

        $obComentario->evento_id = $id;
        $obComentario->comentario = $comentario;
        $obComentario->data = $data;
        $obComentario->id_usuario_criador = $id_usuario_criador;

        $obComentario->cadastrar();
        self::setAlteracao($id, "Adicionado Comentário");
        EvolutionAPI::sendMessage(Evento::getIndividualMessage($id, 'atualizar'));
        return true;
    }
    public static function getComentario($request)
    {
        $queryParams = $request->getQueryParams();

        $id = $queryParams['id'];

        $obComentario = EntityComentarios::getComentarioById($id);
        $response = ['comentario' => nl2br($obComentario->comentario)];
        return json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    }

    public static function getAlteracoes($request)
    {
        $queryParams = $request->getQueryParams();

        $id = $queryParams['id'];
        $alteracoes = [];

        $results = EntityAlteracoes::getAlteracoesByEventoId($id);

        while ($obAlteracoes = $results->fetchObject(EntityAlteracoes::class)) {
            $obUser = EntityUser::getUserById($obAlteracoes->id_usuario_criador);
            $alteracoes[] = [
                'id' => $obAlteracoes->id,
                'alteracao' => $obAlteracoes->alteracao,
                'data' => $obAlteracoes->data,
                'autor' => $obUser->nome
            ];
        }
        return json_encode($alteracoes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}