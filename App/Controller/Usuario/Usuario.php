<?php

namespace App\Controller\Usuario;

use \App\Controller\Pages\Page;
use \App\Session\Login\Login;
use \App\Utils\View;
use \WilliamCosta\DatabaseManager\Pagination;
use \App\Utils\Alert;
use \App\Model\Entity\User as EntityUser;
use \App\Session\Login\Login as SessionLogin;

class Usuario extends Page
{
    /**
     * Método responsável por retornar a mensagem do status
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
                return Alert::getSuccess('Usuário criado com sucesso!');
            case 'updated':
                return Alert::getSuccess('Usuário atualizado com sucesso!');
            case 'deleted':
                return Alert::getSuccess('Usuário excluido com sucesso!');
            case 'duplicated':
                return Alert::getError('O e-mail informado já está sendo utilizado por outro usuário.');
            case 'passwordError':
                return Alert::getError('As senhas não são iguais');
            case 'no-permission':
                return Alert::getError('Você não tem permissão');
        }
        return '';
    }

    /**
     * Método responsável por retornar a view de listagem de usuários
     * @param Request $request
     * @return string
     */
    public static function getUser($request)
    {
        //Conteúdo da home
        $content = View::render('usuario/table/table', [
            'itens' => self::getUserItems($request),
            'status' => self::getStatus($request)
        ]);
        //Retorna a página
        return parent::getPage('Usuários > ToolsVGL', $content);
    }

    /**
     * Método responsável por retornar os usuários cadastrados no banco
     * @param Request $request
     * @param Pagination $obPagination
     * @return string
     */
    private static function getUserItems($request)
    {
        $itens = '';

        //TOTAL DE REGISTROS
        $quantidadetotal = EntityUser::getUsers(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        if ($quantidadetotal <= 0) {
            $itens = '';
        }

        $results = EntityUser::getUsers(null, 'id DESC');

        while ($obUser = $results->fetchObject(EntityUser::class)) {
            $itens .= View::render('/usuario/table/item', [
                'id' => $obUser->id,
                'nome' => $obUser->nome,
                'login' => $obUser->login,
                'privilegio' => $obUser->privilegio
            ]);
        }
        return $itens;
    }

    /**
     * Método responsável por retornar o formulário de edição
     * @param Request $results
     * @param  int
     * @return string
     */
    public static function getEditUser($request)
    {

        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $privilegio = '';

        if (Login::isAdmin()) {
            $privilegio = self::getPrivilegio($id);
        }

        $obUser = EntityUser::getUserById($id);

        if (!$obUser instanceof EntityUser) {
            $request->getRouter()->redirect('/usuario');
            exit;
        }

        $content = View::render('/usuario/form', [
            'title' => 'Editar Usuário',
            'nome' => $obUser->nome,
            'login' => $obUser->login,
            'privilegio' => $privilegio,
            'status' => self::getStatus($request),
        ]);
        return parent::getPage('Editar Usuario > ToolsVGL', $content);
    }
    /**
     * Método responsável por retornar a view renderizada dos radio button de privilegio
     * @param Request $request
     * @param int $id
     * @return string
     */
    private static function getPrivilegio($id = null)
    {
        $admin = '';
        $normal = '';
        if (isset($id)) {
            $obUser = EntityUser::getUserById($id);
            if ($obUser instanceof EntityUser) {
                $privilegio = $obUser->privilegio;

                switch ($privilegio) {
                    case 'admin':
                        $admin = 'checked';
                        break;
                    case 'normal':
                        $normal = 'checked';
                        break;
                }
            }
        }
        return View::render('/usuario/elements/privilegio', [
            'admin' => $admin,
            'normal' => $normal
        ]);
    }

    /**
     * Método responsável por realizar a atualização do usuário
     * @param Request $request
     * @return never
     */
    public static function setEditUser($request)
    {

        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obUser = EntityUser::getUserById($id);

        if (!$obUser instanceof EntityUser) {
            $request->getRouter()->redirect('/usuario');
            exit;
        }

        $postVars = $request->getPostVars();
        $privilegio = $postVars['privilegio'] ?? $obUser->privilegio;

        $obUser->privilegio = $privilegio;


        $obUser->atualizar();

        if ($id == SessionLogin::getId()) {
            $_SESSION['usuario']['privilegio'] = $privilegio;
        }

        $request->getRouter()->redirect('/usuario/edit?id=' . $id . '&status=updated');
        exit;
    }

}