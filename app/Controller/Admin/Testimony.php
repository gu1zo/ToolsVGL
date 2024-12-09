<?php

namespace App\Controller\Admin;
use \App\Model\Entity\Testimony as EntityTestimony;
use \WilliamCosta\DatabaseManager\Pagination;
use \App\http\Request;
use \App\Utils\View;

class Testimony extends Page
{

    /**
     * Método responsável por retornar a view de listagem de depoimentos
     * @param Request $request
     * @return string
     */
    public static function getTestimonies($request)
    {
        //Conteúdo da home
        $content = View::render('admin/modules/testimonies/index', [
            'itens' => self::getTestimonyItems($request, $obPagination),
            'pagination' => parent::getPagination($request, $obPagination),
            'status' => self::getStatus($request)
        ]);

        //Retorna a página
        return parent::getPanel('DEPOIMENTOS > RetisVGL', $content, 'testimonies');
    }

    private static function getTestimonyItems($request, &$obPagination)
    {
        $itens = '';

        //TOTAL DE REGISTROS
        $quantidadetotal = EntityTestimony::getTestimonies(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        //PAGINA ATUAL
        $queryParams = $request->getQueryParams();
        $paginaAtual = $queryParams['page'] ?? 1;

        $obPagination = new Pagination($quantidadetotal, $paginaAtual, 5);

        $results = EntityTestimony::getTestimonies(null, 'id DESC', $obPagination->getLimit());

        while ($obTestimony = $results->fetchObject(EntityTestimony::class)) {
            $itens .= View::render('admin/modules/testimonies/item', [
                'id' => $obTestimony->id,
                'nome' => $obTestimony->nome,
                'mensagem' => $obTestimony->mensagem,
                'data' => date('d/m/Y H:i:s', strtotime($obTestimony->data))
            ]);
        }
        return $itens;
    }

    /**
     * Retornar o cadastro de novo depoimento
     * @param Request $results
     * @return string
     */
    public static function getNewTestimony($request)
    {
        $content = View::render('admin/modules/testimonies/form', [
            'title' => 'Cadastrar Depoimento',
            'nome' => '',
            'mensagem' => '',
            'status' => ''
        ]);

        //Retorna a página
        return parent::getPanel('CADASTRAR DEPOIMENTO > RetisVGL', $content, 'testimonies');
    }

    public static function setNewTestimony($request)
    {
        $postVars = $request->getPostVars();

        $obTestimony = new EntityTestimony;
        $obTestimony->nome = $postVars['nome'] ?? '';
        $obTestimony->mensagem = $postVars['mensagem'] ?? '';
        $obTestimony->cadastrar();


        $request->getRouter()->redirect('/admin/testimonies/' . $obTestimony->id . '/edit?status=created');
        exit;
    }

    /**
     * Método responsável por retornar o formulário de edição
     * @param Request $results
     * @param  int
     * @return string
     */
    public static function getEditTestimony($request, $id)
    {
        $obTestimony = EntityTestimony::getTestimonyById($id);

        if (!$obTestimony instanceof EntityTestimony) {
            $request->getRouter()->redirect('/admin/testimonies');
            exit;
        }

        $content = View::render('admin/modules/testimonies/form', [
            'title' => 'Editar Depoimento',
            'nome' => $obTestimony->nome,
            'mensagem' => $obTestimony->mensagem,
            'status' => self::getStatus($request)
        ]);

        //Retorna a página
        return parent::getPanel('EDITAR DEPOIMENTO > RetisVGL', $content, 'testimonies');
    }

    /**
     * Método responsável por editar um depoimento
     * @param Request $results
     * @param  int
     * @return string
     */
    public static function setEditTestimony($request, $id)
    {
        $obTestimony = EntityTestimony::getTestimonyById($id);

        if (!$obTestimony instanceof EntityTestimony) {
            $request->getRouter()->redirect('/admin/testimonies');
            exit;
        }

        $postVars = $request->getPostVars();

        //Atualização da instancia

        $obTestimony->nome = $postVars['nome'] ?? $obTestimony->nome;
        $obTestimony->mensagem = $postVars['mensagem'] ?? $obTestimony->mensagem;

        $obTestimony->atualizar();

        $request->getRouter()->redirect('/admin/testimonies/' . $obTestimony->id . '/edit?status=updated');
        exit;
    }
    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'created':
                return Alert::getSuccess('Depoimento criado com sucesso!');
                break;
            case 'updated':
                return Alert::getSuccess('Depoimento atualizado com sucesso!');
                break;
            case 'deleted':
                return Alert::getSuccess('Depoimento excluido com sucesso!');
                break;
        }
    }

    public static function getDeleteTestimony($request, $id)
    {
        $obTestimony = EntityTestimony::getTestimonyById($id);

        if (!$obTestimony instanceof EntityTestimony) {
            $request->getRouter()->redirect('/admin/testimonies');
            exit;
        }

        $content = View::render('admin/modules/testimonies/delete', [
            'nome' => $obTestimony->nome,
            'mensagem' => $obTestimony->mensagem
        ]);

        //Retorna a página
        return parent::getPanel('EXCLUIR DEPOIMENTO > RetisVGL', $content, 'testimonies');
    }

    public static function setDeleteTestimony($request, $id)
    {
        $obTestimony = EntityTestimony::getTestimonyById($id);

        if (!$obTestimony instanceof EntityTestimony) {
            $request->getRouter()->redirect('/admin/testimonies');
            exit;
        }
        $obTestimony->excluir($id);

        $request->getRouter()->redirect('/admin/testimonies?status=deleted');
        exit;
    }
}