<?php

namespace App\Controller\Admin;
use \App\Model\Entity\User as EntityUser;
use \WilliamCosta\DatabaseManager\Pagination;
use \App\http\Request;
use \App\Utils\View;

class User extends Page
{

    /**
     * Método responsável por retornar a view de listagem de usuários
     * @param Request $request
     * @return string
     */
    public static function getUser($request)
    {
        //Conteúdo da home
        $content = View::render('admin/modules/users/index', [
            'itens' => self::getUserItems($request, $obPagination),
            'pagination' => parent::getPagination($request, $obPagination),
            'status' => self::getStatus($request)
        ]);

        //Retorna a página
        return parent::getPanel('USUÁRIOS > RetisVGL', $content, 'users');
    }

    private static function getUserItems($request, &$obPagination)
    {
        $itens = '';

        //TOTAL DE REGISTROS
        $quantidadetotal = EntityUser::getUsers(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        //PAGINA ATUAL
        $queryParams = $request->getQueryParams();
        $paginaAtual = $queryParams['page'] ?? 1;

        $obPagination = new Pagination($quantidadetotal, $paginaAtual, 5);

        $results = EntityUser::getUsers(null, 'id DESC', $obPagination->getLimit());

        while ($obUser = $results->fetchObject(EntityUser::class)) {
            $itens .= View::render('admin/modules/users/item', [
                'id' => $obUser->id,
                'nome' => $obUser->nome,
                'email' => $obUser->email
            ]);
        }
        return $itens;
    }

    /**
     * Retornar o cadastro de novo usuário
     * @param Request $results
     * @return string
     */
    public static function getNewUser($request)
    {
        $content = View::render('admin/modules/users/form', [
            'title' => 'Cadastrar Usuário',
            'nome' => '',
            'email' => '',
            'status' => self::getStatus($request)
        ]);

        //Retorna a página
        return parent::getPanel('CADASTRAR USUÁRIO > RetisVGL', $content, 'users');
    }

    public static function setNewUser($request)
    {

        $postVars = $request->getPostVars();
        $email = $postVars['email'] ?? '';
        $nome = $postVars['nome'] ?? '';
        $senha = $postVars['senha'] ?? '';

        $obUser = EntityUser::getUserByEmail($email);

        if ($obUser instanceof EntityUser) {
            $request->getRouter()->redirect('/admin/users/new?status=duplicated');
        }

        $obUser = new EntityUser;
        $obUser->nome = $nome;
        $obUser->email = $email;
        $obUser->senha = password_hash($senha, PASSWORD_DEFAULT);
        $obUser->cadastrar();


        $request->getRouter()->redirect('/admin/users/' . $obUser->id . '/edit?status=created');
        exit;
    }

    /**
     * Método responsável por retornar o formulário de edição
     * @param Request $results
     * @param  int
     * @return string
     */
    public static function getEditUser($request, $id)
    {
        $obUser = EntityUser::getUserById($id);

        if (!$obUser instanceof EntityUser) {
            $request->getRouter()->redirect('/admin/users');
            exit;
        }

        $content = View::render('admin/modules/users/form', [
            'title' => 'Editar Usuário',
            'nome' => $obUser->nome,
            'email' => $obUser->email,
            'status' => self::getStatus($request)
        ]);

        //Retorna a página
        return parent::getPanel('EDITAR USUÁRIO > RetisVGL', $content, 'users');
    }

    /**
     * Método responsável por editar um usuário
     * @param Request $results
     * @param  int
     * @return string
     */
    public static function setEditUser($request, $id)
    {
        $obUser = EntityUser::getUserById($id);

        if (!$obUser instanceof EntityUser) {
            $request->getRouter()->redirect('/admin/users');
            exit;
        }

        $postVars = $request->getPostVars();
        $nome = $postVars['nome'];
        $email = $postVars['email'];
        $senha = password_hash($postVars['senha'] ?? '', PASSWORD_DEFAULT);

        $obUserEmail = EntityUser::getUserByEmail($email);

        if ($obUser instanceof EntityUser && $obUserEmail->id != $id) {
            $request->getRouter()->redirect('/admin/users/' . $id . '/edit?status=duplicated');
        }
        //Atualização da instancia

        $obUser->nome = $nome;
        $obUser->email = $email;
        $obUser->senha = $senha;

        $obUser->atualizar();

        $request->getRouter()->redirect('/admin/users/' . $obUser->id . '/edit?status=updated');
        exit;
    }
    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'created':
                return Alert::getSuccess('Usuário criado com sucesso!');
                break;
            case 'updated':
                return Alert::getSuccess('Usuário atualizado com sucesso!');
                break;
            case 'deleted':
                return Alert::getSuccess('Usuário excluido com sucesso!');
                break;
            case 'duplicated':
                return Alert::getError('O e-mail informado já está sendo utilizado por outro usuário.');
                break;
        }
    }

    public static function getDeleteUser($request, $id)
    {
        $obUser = EntityUser::getUserById($id);

        if (!$obUser instanceof EntityUser) {
            $request->getRouter()->redirect('/admin/users');
            exit;
        }

        $content = View::render('admin/modules/users/delete', [
            'nome' => $obUser->nome,
            'email' => $obUser->email
        ]);

        //Retorna a página
        return parent::getPanel('EXCLUIR USUÁRIO > RetisVGL', $content, 'users');
    }

    public static function setDeleteUser($request, $id)
    {
        $obUser = EntityUser::getUserById($id);

        if (!$obUser instanceof EntityUser) {
            $request->getRouter()->redirect('/admin/users');
            exit;
        }
        $obUser->excluir($id);

        $request->getRouter()->redirect('/admin/users?status=deleted');
        exit;
    }
}