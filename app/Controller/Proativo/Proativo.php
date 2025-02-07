<?php

namespace App\Controller\Proativo;

use \App\Controller\Pages\Page;
use App\http\Request;
use \App\Utils\View;
use \App\Session\Login\Login;
use \App\Utils\Alert;
use \App\Utils\StringManipulation;
use \App\Model\Entity\Proatividade as EntityProativo;
use \App\Model\Entity\User as EntityUser;
class Proativo extends Page
{

    /**
     * Método responsável por retornar a view renderizada da tabela de proatividades
     * @param Request $request
     * @return string
     */
    public static function getProatividade($request)
    {
        $content = View::render('proativo/table/table', [
            'itens' => self::getProatividadeItens($request),
            'status' => self::getStatus($request)
        ]);
        //Retorna a página
        return parent::getPage('Proatividade > RetisVGL', $content);
    }

    public static function getProatividadeItens($request)
    {
        $itens = '';

        //TOTAL DE REGISTROS
        $quantidadetotal = EntityProativo::getProatividade(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        if ($quantidadetotal <= 0) {
            $itens = '';
        }

        $results = EntityProativo::getProatividade(null, 'id DESC');

        while ($obProativo = $results->fetchObject(EntityProativo::class)) {
            $obUser = EntityUser::getUserById($obProativo->id_usuario_criador);
            $usuarioCriador = $obUser->nome;
            $itens .= View::render('/proativo/table/item', [
                'id' => $obProativo->id,
                'protocolo' => $obProativo->protocolo,
                'data' => $obProativo->data,
                'regional' => $obProativo->regional,
                'host' => $obProativo->host,
                'usuario-criador' => $usuarioCriador
            ]);
        }
        return $itens;
    }
    /**
     * Método responsável por renderizar a view do formulário
     * @param Request $request
     * @return string
     */
    public static function getNovoProativo($request)
    {

        $content = View::render('proativo/form', [
            'title' => 'Nova Proatividade',
            'regionais' => self::getRegional(),
            'status' => self::getStatus($request),
            'protocolo' => '',
            'host' => '',
            'data' => '',
            'observacao' => ''
        ]);

        return parent::getPage('Novo Proativo > RetisVGL', $content);
    }

    /**
     * Método responsável por cadastrar uma nova proatividade
     * @param Request $request
     * @return never
     */
    public static function setNovoProativo($request)
    {
        $postVars = $request->getPostVars();
        $protocolo = $postVars['protocolo-isp'] ?? '';
        $data = $postVars['data'] ?? '';
        $regional = $postVars['regional'] ?? '';
        $host = $postVars['host'] ?? '';
        $observacao = $postVars['observacao'] ?? '';
        $id_usuario_criador = Login::getId() ?? '';

        $obProativo = EntityProativo::getProatividadeByProtocol($protocolo);

        if ($obProativo instanceof EntityProativo) {
            $request->getRouter()->redirect('/proatividade/novo?status=duplicated');
            exit;
        }

        $obProativo = new EntityProativo;
        $obProativo->protocolo = $protocolo;
        $obProativo->data = $data;
        $obProativo->regional = $regional;
        $obProativo->host = $host;
        $obProativo->observacao = $observacao;
        $obProativo->id_usuario_criador = $id_usuario_criador;

        $obProativo->cadastrar();

        $request->getRouter()->redirect('/proatividade/edit?id=' . $obProativo->id . '&status=created');
        exit;
    }

    /**
     * Método responsável por renderizar o formulário de edição
     * @param Request $request
     * @return mixed
     */
    public static function getEditProativo($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obProativo = EntityProativo::getProatividadeById($id);

        if (!$obProativo instanceof EntityProativo) {
            $request->getRouter()->redirect('/proatividade?status=no-id');
            exit;
        }


        $content = View::render('proativo/form', [
            'title' => 'Editar Proatividade',
            'regionais' => self::getRegional($id),
            'status' => self::getStatus($request),
            'protocolo' => $obProativo->protocolo,
            'host' => $obProativo->host,
            'data' => $obProativo->data,
            'observacao' => $obProativo->observacao

        ]);

        return parent::getPage('Editar Proativo > RetisVGL', $content);
    }

    public static function setEditProativo($request)
    {

        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obProativo = EntityProativo::getProatividadeById($id);

        if (!$obProativo instanceof EntityProativo) {
            $request->getRouter()->redirect('/proatividade');
            exit;
        }

        $postVars = $request->getPostVars();
        $protocolo = $postVars['protocolo-isp'] ?? '';
        $data = $postVars['data'] ?? '';
        $regional = $postVars['regional'] ?? '';
        $host = $postVars['host'] ?? '';
        $observacao = $postVars['observacao'] ?? '';

        $obProativoProtocol = EntityProativo::getProatividadeByProtocol($protocolo);

        if (isset($obProativoProtocol->id)) {
            if (($obProativo instanceof EntityProativo) && ($id != $obProativoProtocol->id)) {
                $request->getRouter()->redirect('/proatividade/edit?id=' . $id . '&status=duplicated');
                exit;
            }
        }
        //Atualização da instancia

        $obProativo->protocolo = $protocolo;
        $obProativo->data = $data;
        $obProativo->regional = $regional;
        $obProativo->host = $host;
        $obProativo->observacao = $observacao;
        $obProativo->id_usuario_criador = $obProativoProtocol->id_usuario_criador;


        $obProativo->atualizar();

        $request->getRouter()->redirect('/proatividade/edit?id=' . $id . '&status=updated');
        exit;
    }

    /**
     * Método responsável por renderizar o dropdown das regionais
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
            $obProativo = EntityProativo::getProatividadeById($id);
            if ($obProativo instanceof EntityProativo) {
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
        $content = View::render('proativo/select', [
            'itens' => self::getRegionalItens()
        ]);
        ;

        return View::render('proativo/regionais', [
            'select' => StringManipulation::processTemplate($content, $data)
        ]);
    }

    private static function getRegionalItens()
    {
        $item = '';
        $regionais = explode(',', getenv('REGIONAIS'));
        foreach ($regionais as $k) {
            $item .= View::render('proativo/item', [
                'regional' => strtoupper($k)
            ]);
        }
        return $item;
    }
    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'created':
                return Alert::getSuccess('A proatividade foi cadastrada com sucesso.');
                break;
            case 'duplicated':
                return Alert::getError('O Protocolo informado já foi cadastrado.');
                break;
            case 'updated':
                return Alert::getSuccess('A proatividade foi atualizada com sucesso.');
                break;
            case 'no-id':
                return Alert::getError('O Protocolo não foi encontrado!');
                break;
        }
        return '';
    }

    public static function getDeleteProatividade($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];


        $obProativo = EntityProativo::getProatividadeById($id);

        if (!$obProativo instanceof EntityProativo) {
            $request->getRouter()->redirect('proatividade?status=no-id');
            exit;
        }

        $content = View::render('proativo/delete', [
            'protocolo' => $obProativo->protocolo
        ]);

        //Retorna a página
        return parent::getPage('Excluir Proatividade > RetisVGL', $content);
    }

    public static function setDeleteProatividade($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];
        $obProativo = EntityProativo::getProatividadeById($id);

        if (!$obProativo instanceof EntityProativo) {
            $request->getRouter()->redirect('/proatividade?status=no-id');
            exit;
        }
        $obProativo->excluir($id);

        $request->getRouter()->redirect('/proatividade?status=deleted');
        exit;
    }

}