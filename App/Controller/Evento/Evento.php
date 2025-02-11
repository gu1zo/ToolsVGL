<?php

namespace App\Controller\Evento;

use \App\Controller\Pages\Page;
use \App\Controller\Email\Email;
use \App\Controller\Api\EvolutionAPI;
use \App\Utils\View;
use \App\Utils\Alert;
use \App\Utils\DateManipulation;
use \App\Model\Entity\PontoAcesso as EntityPontoAcesso;
use \App\Model\Entity\PontoAcessoAfetado as EntityPontoAcessoAfetado;
use \App\Model\Entity\Evento as EntityEvento;
use \App\Model\Entity\Manutencao as EntityManutencao;
use \App\Model\Entity\EventoConclusao as EntityEventoConclusao;
use \App\Model\Entity\Alteracoes as EntityAlteracoes;
use \App\Model\Entity\Joins as EntityJoins;
use \App\Session\Login\Login;
use \App\Model\Rest\APIElite;

use \App\Utils\StringManipulation;
use DateTime;
use IntlDateFormatter;

class Evento extends Page
{

    /**
     * Método resposnsável por renderizar a seleção do tipo do evento
     * @return string
     */
    public static function getNovoEventoTipo()
    {
        $content = View::render('eventos/tipo');

        return parent::getPage('Novo Evento > RetisVGL', $content);
    }

    /**
     * Método Responsável por retornar o formulário de cadastro de eventos
     * @param Request $request
     * @return string
     */
    public static function getNovoEvento($request)
    {
        $previsto = '';

        $queryParams = $request->getQueryParams();
        $tipo = $queryParams['tipo'];

        if ($tipo == 'manutencao') {
            $previsto = View::render('eventos/elements/horario/horario-previsto', [
                'horario-previsto' => '',
            ]);
        }

        $horario = View::render('/eventos/elements/horario/horario', [
            'reagendar' => '',
            'previsto' => $previsto,
            'final' => '',
            'tipo' => 'required'
        ]);

        $form = View::render('eventos/form/cadastro', [
            'title' => 'Novo Cadastro',
            'status' => self::getStatus($request),
            'tipo' => $tipo,
            'regionais' => self::getRegional(),
            'horario' => $horario,
            'protocolo' => '',
            'pontos-acesso' => 'pontosAcesso',
            'observacao' => '',
            'button' => '',
            'button-name' => 'Cadastrar'
        ]);

        $content = View::render('eventos/form/form', [
            'form' => $form,
            'comentarios' => ''
        ]);

        return parent::getPage('Novo Evento > RetisVGL', $content);
    }

    /**
     * Método responsável por cadastrar um novo evento
     * @param Request $request
     * @return never
     */
    public static function setNovoEvento($request)
    {
        $postVars = $request->getPostVars();
        $email = 0;
        $tipo = $postVars['tipo'] ?? '';
        $protocolo = $postVars['protocolo'] ?? '';
        $dataInicio = $postVars['horario-inicial'] ?? '';
        $regional = $postVars['regional'] ?? '';
        $observacao = $postVars['observacao'] ?? '';
        $id_usuario_criador = Login::getId();
        $pontosAcesso = self::getPontosAcessoArray($request, $postVars);
        $clientes = self::getClientesByPonto($pontosAcesso);
        if ($tipo == 'evento') {
            $email = true;
            foreach ($clientes as $k) {
                Email::send('incidente', $postVars, $k);
            }

        }
        $clientes = json_encode(array_values($clientes), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $obEvento = new EntityEvento;
        $obManutencao = new EntityManutencao;
        if ($tipo == 'manutencao') {
            $obEvento->status = 'em analise';
            $obManutencao->dataPrevista = $postVars['horario-previsto'] ?? '';
        }
        $obEvento->email = $email;
        $obEvento->tipo = $tipo;
        $obEvento->protocolo = $protocolo;
        $obEvento->dataInicio = $dataInicio;
        $obEvento->regional = $regional;
        $obEvento->observacao = $observacao;
        $obEvento->clientes = $clientes;
        $obEvento->id_usuario_criador = $id_usuario_criador;

        $obEvento->cadastrar();
        $obManutencao->evento_id = $obEvento->id;
        $obManutencao->cadastrar();

        foreach ($pontosAcesso as $item) {
            $obPontoAcessoAfetado = new EntityPontoAcessoAfetado;
            $obPontoAcessoAfetado->evento_id = $obEvento->id;
            $obPontoAcessoAfetado->ponto_acesso_codigo = $item;
            $obPontoAcessoAfetado->cadastrar();
        }

        self::setAlteracao($obEvento->id, "Evento Criado");

        if ($obEvento->tipo != 'manutencao') {
            EvolutionAPI::sendMessage(self::getIndividualMessage($obEvento->id, 'novo'));
        }


        $request->getRouter()->redirect('/evento/edit?id=' . $obEvento->id . '&status=created');
        exit;
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
    /**
     * Método responsável por retornar o formulário de edição renderizado
     * @param mixed $request
     * @return string
     */
    public static function getEditEvento($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obEventos = EntityEvento::getEventoById($id);

        if (!$obEventos instanceof EntityEvento) {
            $request->getRouter()->redirect('/evento');
            exit;
        }

        $previsto = '';
        $final = '';
        $tipo = 'disabled';
        if ($obEventos->tipo == 'manutencao') {
            $obManutencao = EntityManutencao::getManutencaoById($id);
            $previsto = View::render('eventos/elements/horario/horario-previsto', [
                'horario-previsto' => $obManutencao->dataPrevista
            ]);
        }
        $reagendar = View::render('eventos/elements/horario/reagendar-icon', [
            'id' => $id
        ]);


        if ($obEventos->status != 'concluido') {
            $reagendar = View::render('eventos/elements/horario/reagendar-icon', [
                'id' => $id
            ]);
        } else {
            $final = View::render('/eventos/elements/horario/horario-final', [
                'horario-final' => $obEventos->dataFim
            ]);
        }


        $horario = View::render('/eventos/elements/horario/horario', [
            'reagendar' => $reagendar,
            'previsto' => $previsto,
            'final' => $final,
            'tipo' => $tipo
        ]);

        $form = View::render(
            '/eventos/form/cadastro',
            [
                'title' => 'Editar Evento',
                'status' => self::getStatus($request),
                'tipo' => $obEventos->tipo,
                'regionais' => self::getRegional($obEventos->id),
                'horario' => $horario,
                'protocolo' => $obEventos->protocolo,
                'horario-inicial' => $obEventos->dataInicio,
                'pontos-acesso' => 'pontosAcessoEdit',
                'observacao' => $obEventos->observacao,
                'button' => self::getEditButtons($request),
                'button-name' => 'Salvar'
            ]
        );
        $comentarios = View::render('eventos/form/comentarios');

        $content = View::render('eventos/form/form', [
            'form' => $form,
            'comentarios' => $comentarios,
            'modal' => self::getModal()
        ]);

        return parent::getPage('Editar Evento > RetisVGL', $content);

    }

    private static function getModal()
    {
        $modal = '';
        $modal .= View::render('/eventos/elements/modal', [
            'id' => 'modal-novo',
            'title' => 'Cadastrar Comentário',
            'content' => View::render('/eventos/form/comentarios/novo', [
                'comentario' => ''
            ])
        ]);

        $modal .= View::render('/eventos/elements/modal', [
            'id' => 'modal-logs',
            'title' => 'Alterações',
            'content' => View::render('/eventos/elements/alteracoes')
        ]);
        return $modal;
    }
    /**
     * Método responsável por retornar os botóes para o formulário de eventos
     * @param Request $request
     * @return string
     */
    private static function getEditButtons($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];
        $content = View::render('/eventos/elements/button-logs');
        $content .= View::render('/eventos/elements/button', [
            'url' => '/evento/email?id=' . $id,
            'title' => 'Enviar E-mail',
            'icon' => 'bi-envelope',
            'color' => 'primary',
        ]);

        $obEvento = EntityEvento::getEventoById($id);

        switch ($obEvento->status) {
            case 'em analise':
                $content .= View::render('/eventos/elements/button', [
                    'url' => '/evento/aprovar?id=' . $id,
                    'title' => ' Aprovar Manutencao',
                    'icon' => 'bi-clipboard-check',
                    'color' => 'success',
                ]);
                break;
            case 'pendente':
                $content .= View::render('/eventos/elements/button', [
                    'url' => '/evento/executar?id=' . $id,
                    'title' => 'Executar Manutenção',
                    'icon' => 'bi-tools',
                    'color' => 'warning'
                ]);
                break;
            case 'em execucao':
                $content .= View::render('/eventos/elements/button', [
                    'url' => '/evento/concluir?id=' . $id,
                    'title' => 'Concluir Manutenção',
                    'icon' => 'bi-check-lg',
                    'color' => 'success'
                ]);
                break;
        }
        return $content;
    }

    /**
     * Método responsável por realizar a edição dos eventos
     * @param Request $request
     * @return never
     */
    public static function setEditEvento($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obEvento = EntityEvento::getEventoById($id);

        $postVars = $request->getPostVars();

        $tipo = $obEvento->tipo;
        $protocolo = $postVars['protocolo'] ?? $obEvento->protocolo;
        $regional = $postVars['regional'] ?? $obEvento->regional;
        $observacao = $postVars['observacao'] ?? $obEvento->observacao;

        $pontosAcesso = self::getPontosAcessoArray($request, $postVars);
        self::updatePontosAcessoAfetados($id, $pontosAcesso);


        if ($obEvento->status != 'reagendado') {
            $horario_inicial = $postVars['horario-inicial'] ?? $obEvento->dataInicio;
            $obEvento->dataInicio = $horario_inicial;

            if ($tipo == 'manutencao') {
                $obManutencao = EntityManutencao::getManutencaoById($id);
                $obManutencao->dataPrevista = $postVars['horario-previsto'] ?? $obManutencao->dataPrevista;
                $obManutencao->atualizar();
            }
        }

        if ($pontosAcesso != '') {
            $clientes = self::getClientesByPonto($pontosAcesso);
            $clientes = json_encode(array_values($clientes), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

            $obEvento->clientes = $clientes;
        }
        $obEvento->tipo = $tipo;
        $obEvento->protocolo = $protocolo;
        $obEvento->regional = $regional;
        $obEvento->observacao = $observacao;

        self::setAlteracao($obEvento->id, "Evento Editado");

        $obEvento->atualizar();

        EvolutionAPI::sendMessage(self::getIndividualMessage($obEvento->id, 'atualizar'));

        $request->getRouter()->redirect('/evento/edit?id=' . $id . '&status=edited');
        exit;
    }
    /**
     * Método responsável por retornar a tabela de eventos renderizada
     * @return string
     */
    public static function getTable($request)
    {
        return parent::getPage('Eventos > RetisVGL', self::getTableEventos($request, self::getCards()));
    }


    /**
     * Método responsável por retornar a tabela de eventos
     * @return string
     */
    public static function getTableEventos($request, $cards = '')
    {

        $queryParams = $request->getQueryParams();
        $status = $queryParams['evento-status'] ?? '';

        $content = View::render('eventos/table/table', [
            'status' => self::getStatus($request),
            'itens' => '',
            'cards' => $cards
        ]);

        if ($status == 'Clientes Afetados') {
            $content = self::getClientesAfetados();
        }
        return $content;
    }

    /**
     * Método responsável por retornar a row da tabela de eventos
     * @param Request $request
     * @return string
     */
    private static function getTableEventosItens($request)
    {
        $queryParams = $request->getQueryParams();
        $status = $queryParams['evento-status'] ?? 'em execucao';

        if ($status == 'todos') {
            $status = null;
        }

        $itens = '';

        $results = EntityEvento::getEventoByStatus($status);

        while ($obEvento = $results->fetchObject(EntityEvento::class)) {
            $itens .= View::render('eventos/table/item', [
                'status' => str_replace(' ', '-', $obEvento->status),
                'protocolo' => $obEvento->protocolo,
                'tipo' => (new StringManipulation)->formatarTipo($obEvento->tipo),
                'horario-inicial' => $obEvento->dataInicio,
                'pontos-acesso' => self::getPontosAcessoTable($obEvento->id),
                'regional' => $obEvento->regional,
                'observacao' => $obEvento->observacao,
                'email' => $obEvento->email ? '✔' : '❌',
                'id' => $obEvento->id
            ]);
        }


        return $itens;
    }
    /**
     * Método responsável por retornar a string para pontos de acesso na tabela
     * @param int $id
     * @return string
     */
    public static function getPontosAcessoTable($id)
    {
        $pontosAcesso = [];

        $results = (new EntityPontoAcesso)->getCodeAndNameById($id);

        while ($row = $results->fetchObject(EntityPontoAcesso::class)) {
            $pontosAcesso[] = $row->nome;
        }
        $pontosAcesso = implode(', ', $pontosAcesso);
        return $pontosAcesso;
    }

    /**
     * Método responsável por retornar o aviso de sucesso ou erro renderizado
     * @param Request $request
     * @return string
     */
    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'created':
                return Alert::getSuccess('O evento foi cadastrado com sucesso.');
                break;
            case 'edited':
                return Alert::getSuccess('O evento foi editado com sucesso.');
                break;
            case 'no-pontos':
                return Alert::getError('Nenhum ponto de acesso válido foi selecionado. Tente novamente!');
                break;
            case 'approved':
                return Alert::getSuccess('A manutenção foi aprovada com sucesso!');
                break;
            case 'executed':
                return Alert::getSuccess('A manutenção está em execução!');
                break;
            case 'completed':
                return Alert::getSuccess('O evento foi concluído!');
                break;
            case 'no-id':
                return Alert::getError('Evento não encontrado!');
                break;
            case 'email-send':
                return Alert::getSuccess('Os E-mails foram enviados com sucesso!');
                break;
        }
        return '';
    }

    /**
     * Método responsável por retornar as regionais renderizadas
     * @param int $id
     * @return string
     */
    private static function getRegional($id = null)
    {
        // Carregar setores da variável de ambiente
        $regional = explode(',', strtolower(getenv('REGIONAIS')));
        $selectedRegionais = array_fill_keys($regional, ''); // Inicializa todos os setores sem seleção

        $default = 'selected'; // Seleção padrão

        if (isset($id)) {
            $obProativo = EntityEvento::getEventoById($id);
            if ($obProativo instanceof EntityEvento) {
                $regional = $obProativo->regional;
                $regional = strtolower($regional);
                $default = '';
                if (array_key_exists($regional, $selectedRegionais)) {
                    $selectedRegionais[$regional] = 'selected';
                }
            }
        }
        // Preparar os dados para renderizar a visão
        $data = ['default' => $default];
        foreach ($selectedRegionais as $regional => $selected) {
            $data[$regional] = $selected;
        }
        $content = View::render('eventos/elements/select', [
            'itens' => self::getRegionalItens()
        ]);
        ;

        return View::render('eventos/elements/regionais', [
            'select' => StringManipulation::processTemplate($content, $data)
        ]);
    }


    /**
     * Método responsável por retornar os itens individuais da regional
     * @return string
     */
    private static function getRegionalItens()
    {
        $item = '';
        $regionais = explode(',', getenv('REGIONAIS'));
        foreach ($regionais as $k) {
            $item .= View::render('eventos/elements/item', [
                'regional' => strtoupper($k)
            ]);
        }
        return $item;
    }

    /**
     * Método responsável por retornar os clientes com base no ponto de acesso
     * @param array $pontosAcesso
     * @return array{codcli: mixed|string, e_mail: mixed|string, nome: mixed|string[]}
     */
    private static function getClientesByPonto($pontosAcesso)
    {
        $clientes = [];
        foreach ($pontosAcesso as $item) {
            if ($dados = APIElite::getDadosByCodcon($item)) {
                foreach ($dados as $k) {
                    if ($k['descri_est'] === 'Serviço Habilitado') {
                        // Verifica se o cliente já foi adicionado com base no codcli
                        if (!array_key_exists($k['codcli'], $clientes)) {
                            if (filter_var($k['e_mail'], FILTER_VALIDATE_EMAIL)) {
                                $clientes[$k['codcli']] = [
                                    'codcli' => $k['codcli'],
                                    'nome' => $k['nome_cli'],
                                    'e_mail' => $k['e_mail'],
                                    'ponto' => $item
                                ];
                            }
                        }
                    }
                }
            }
        }
        return $clientes;
    }

    /**
     * Método responsável por retornar o array de pontos de acesso com base no campo opcional
     * @param array $postVars
     */
    private static function getPontosAcessoArray($request, $postVars)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'] ?? '';
        $tipo = $postVars['tipo'];

        $pontosAcesso = $postVars['pontos-acesso-opcional'] ?? '';
        if ($pontosAcesso != '') {
            $pontosAcesso = array_map('trim', explode(',', $postVars['pontos-acesso-opcional']));
            $pontosAcessoFiltrados = [];

            foreach ($pontosAcesso as $ponto) {
                $obPonto = EntityPontoAcesso::getPontoByName($ponto);
                if ($obPonto instanceof EntityPontoAcesso) {
                    $pontosAcessoFiltrados[] = $obPonto->codigo;
                }
            }
            if (empty($pontosAcessoFiltrados)) {
                $request->getRouter()->redirect($request->getUri() . '?tipo=' . $tipo . '&status=no-pontos&id=' . $id);
                exit;
            }
            $pontosAcesso = $pontosAcessoFiltrados;
        } else {
            $pontosAcesso = $postVars['pontosAcesso'] ?? '';
        }

        return $pontosAcesso;
    }


    /**
     * Método responsável por atualizar os pontos de acesso afetados
     * @param int $id
     * @param array $pontosAcesso
     * @return bool
     */
    private static function updatePontosAcessoAfetados($id, $pontosAcesso)
    {
        $res = EntityPontoAcessoAfetado::getPontoAcessoAfetadoById($id);
        while ($row = $res->fetchObject(EntityPontoAcessoAfetado::class)) {
            if (!in_array($row->ponto_acesso_codigo, $pontosAcesso)) {
                $obPonto = new EntityPontoAcessoAfetado;
                $obPonto->excluir($id, $row->ponto_acesso_codigo);
            }
        }
        foreach ($pontosAcesso as $item) {

            $obPonto = EntityPontoAcessoAfetado::getPontoAcessoAfetadoByIdAndCode($id, $item);
            if (!$obPonto instanceof EntityPontoAcessoAfetado) {
                $obPonto = new EntityPontoAcessoAfetado;
                $obPonto->evento_id = $id;
                $obPonto->ponto_acesso_codigo = $item;
                $obPonto->cadastrar();
            }
        }
        return true;
    }

    /**
     * Método responsável por retornar a view do reangedamento renderizada
     * @param Request $request
     * @return string
     */
    public static function getReagendar($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obEvento = EntityEvento::getEventoById($id);

        if ($obEvento->status != 'reagendado') {
            $data = strtotime($obEvento->dataInicio);
            $content = View::render('/eventos/elements/horario/reagendar', [
                'id' => $id,
                'protocolo' => $obEvento->protocolo,
                'data' => date('d/m/Y H:i', timestamp: $data),
            ]);
        } else {
            $previsto = '';
            if ($obEvento->tipo == 'manutencao') {
                $obManutencao = EntityManutencao::getManutencaoById($id);
                $previsto = View::render('eventos/elements/horario/horario-previsto', [
                    'horario-previsto' => $obManutencao->dataPrevista
                ]);
            }
            $horario = View::render('/eventos/elements/horario/horario', [
                'reagendar' => '',
                'final' => '',
                'previsto' => $previsto,
                'tipo' => 'required'
            ]);
            $content = View::render('eventos/elements/horario/reagendar-data', [
                'horario' => $horario,
                'id' => $id
            ]);
        }
        return parent::getPage('Reagendar Evento > RetisVGL', $content);
    }

    /**
     * Método responsável por realizar o reagendamento
     * @param Request $request
     * @return never
     */
    public static function setReagendar($request)
    {
        $queryParams = $request->getQueryParams();
        $postVars = $request->getPostVars();
        $id = $queryParams['id'];

        $obEvento = EntityEvento::getEventoById($id);
        $obManutencao = EntityManutencao::getManutencaoById($id);

        if ($obEvento->status != 'reagendado') {
            EvolutionAPI::sendMessage(self::getIndividualMessage($obEvento->id, 'reagendar'));
            $obEvento->dataInicio = null;
            $obManutencao->dataPrevista = null;
            $obEvento->status = 'reagendado';

            $obEvento->atualizar();
            $obManutencao->atualizar();
            self::setAlteracao($obEvento->id, "Evento aguardando reagendamento");
        } else {
            $obEvento->dataInicio = $postVars['horario-inicial'];
            $obEvento->status = 'em execucao';

            if ($obEvento->tipo == 'manutencao') {
                $obManutencao->dataPrevista = $postVars['horario-previsto'];
                $obManutencao->atualizar();
                $obEvento->status = 'em analise';
            }

            $obEvento->atualizar();
            $data = new DateTime($obEvento->dataInicio);
            $alteracao = "Evento reagendado para " . $data->format('Y/m/d H:i');
            EvolutionAPI::sendMessage(self::getIndividualMessage($obEvento->id, 'reagendar'));

            self::setAlteracao($obEvento->id, $alteracao);
        }

        $request->getRouter()->redirect('/evento/edit?id=' . $id . '&status=edited');
        exit;
    }

    /**
     * Método responsável por aprovar o evento
     * @param Request $request
     * @return never
     */
    public static function aprovar($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obEvento = EntityEvento::getEventoById($id);

        $obEvento->status = 'pendente';
        $obEvento->atualizar();

        self::setAlteracao($obEvento->id, "Evento aprovado");

        $request->getRouter()->redirect('/evento/edit?id=' . $id . '&status=approved');
        exit;
    }


    /**
     * Método responsável por executar o evento
     * @param Request $request
     * @return never
     */
    public static function executar($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obEvento = EntityEvento::getEventoById($id);

        $obEvento->status = 'em execucao';
        $obEvento->atualizar();

        self::setAlteracao($obEvento->id, "Evento em Execução");

        $request->getRouter()->redirect('/evento/edit?id=' . $id . '&status=executed');
        exit;
    }


    /**
     * Método responsável por retornar o formulário de conclusão de um evento
     * @param Request $request
     * @return string
     */
    public static function getConcluir($request)
    {
        $content = View::render('eventos/concluir');

        return parent::getPage('Novo Evento > RetisVGL', $content);
    }

    /**
     * Método responsável por concluir o atendimento
     * @param Request $request
     * @return never
     */
    public static function setConcluir($request)
    {
        $queryParams = $request->getQueryParams();
        $postVars = $request->getPostVars();
        $id = $queryParams['id'];

        $motivo = $postVars['motivo'] ?? '';
        $forca_maior = isset($postVars['forca-maior']) ? 1 : 0;

        $comentario = $postVars['comentario'] ?? '';

        $obEvento = EntityEvento::getEventoById($id);

        $data = new DateTime('America/Sao_Paulo');

        $obEvento->status = 'concluido';
        $obEvento->dataFim = $data->format('Y-m-d H:i');
        $obEvento->atualizar();
        self::setAlteracao($obEvento->id, "Evento Concluído");

        $obConclusao = new EntityEventoConclusao;
        $obConclusao->evento_id = $id;
        $obConclusao->motivo = $motivo;
        $obConclusao->forca_maior = $forca_maior;
        $obConclusao->comentario = $comentario;

        $obConclusao->cadastrar();
        EvolutionAPI::sendMessage(self::getIndividualMessage($obEvento->id, 'concluir'));
        $request->getRouter()->redirect('/evento/edit?id=' . $id . '&status=completed');
        exit;
    }

    public static function getEmail($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];
        $content = View::render(
            'eventos/email',
            [
                'id' => $id
            ]
        );

        return parent::getPage('Enviar E-mails > RetisVGL', $content);
    }

    public static function setEmail($request)
    {
        $vars = [];
        $queryParams = $request->getQueryParams();
        $postVars = $request->getPostVars();
        $id = $queryParams['id'];
        $tipo = $postVars['email'];


        $obEvento = EntityEvento::getEventoById($id);
        $clientes = json_decode($obEvento->clientes, true);
        $vars['horario-inicial'] = $obEvento->dataInicio;

        if ($obEvento->tipo == 'manutencao') {
            $obManutencao = EntityManutencao::getManutencaoById($id);
            $vars['horario-previsto'] = $obManutencao->dataPrevista;
        }


        foreach ($clientes as $k) {
            Email::send($tipo, $vars, $k);
        }

        $obEvento->email = true;
        $obEvento->atualizar();
        self::setAlteracao($obEvento->id, "Enviado E-mails");

        $request->getRouter()->redirect('/evento/edit?id=' . $id . '&status=email-send');
        exit;

    }

    private static function getClientesAfetados()
    {
        $itens = '';

        $results = EntityEvento::getEventoByStatus('em execucao');
        while ($obEvento = $results->fetchObject(EntityEvento::class)) {

            $clientes = json_decode($obEvento->clientes, true);

            foreach ($clientes as $k) {
                $obPonto = EntityPontoAcesso::getPontoByCode($k['ponto']);

                $itens .= View::render('eventos/table/item-clientes', [
                    'protocolo' => $obEvento->protocolo,
                    'codcli' => $k['codcli'],
                    'nome' => $k['nome'],
                    'ponto' => $obPonto->nome
                ]);
            }


        }
        return View::render('eventos/table/table-clientes', [
            'itens' => $itens
        ]);
    }

    private static function getCards()
    {
        $content = '';
        $status = [
            [
                'name' => 'Em Análise',
                'color' => 'gray',
            ],
            [

                'name' => 'Reagendado',
                'color' => 'lightblue',
            ],
            [

                'name' => 'Pendente',
                'color' => 'darkblue',
            ],
            [

                'name' => 'Em Execução',
                'color' => 'yellow',
            ],
            [

                'name' => 'Concluído',
                'color' => 'green',
            ]
        ];

        foreach ($status as $card) {
            $content .= View::render('/eventos/elements/card-item', [
                'status' => $card['name'],
                'color' => $card['color']
            ]);
        }

        return $content;
    }

    public static function getIndividualMessage($id, $tipo)
    {
        $obEvento = EntityJoins::getEventoById($id);

        date_default_timezone_set('America/Sao_Paulo');
        $dataAtual = new DateTime();

        $duracao = DateManipulation::gethourDiff($obEvento->dataInicio, $dataAtual);
        $lastupdate = $duracao;
        $usuario = $obEvento->usuario_nome;
        $info = $obEvento->observacao;

        $obComentario = EntityJoins::getLastInfoById($obEvento->id);
        if ($obComentario instanceof EntityJoins) {
            $lastupdate = DateManipulation::gethourDiff($obComentario->data, $dataAtual);
            $usuario = $obComentario->usuario_nome;
            $info = $obComentario->comentario;
        }
        $string = '';
        switch ($tipo) {
            case 'concluir':
                $string .= "*CONCLUÍDO* ✅\n";
                break;
            case 'atualizar':
                $string .= "*ATUALIZAÇÃO* ✍️\n";
                break;
            case 'novo':
                $string .= "*NOVO EVENTO* ⚠️\n";
                break;
            case 'reagendar':
                $string .= "*REAGENDADO* 🕑\n";
                break;
        }

        $string .= "_EVENTO_ *" . $obEvento->protocolo . " " . Evento::getPontosAcessoTable($obEvento->id) . "*\n";
        $string .= "_duration:_ " . $duracao . "\n";
        $string .= "_last update:_ " . $lastupdate . " por " . $usuario . "\n";
        $string .= "_last info:_ " . $info;
        return $string;
    }

}