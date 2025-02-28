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
            case 'passwordError':
                return Alert::getError('As senhas não são iguais');
                break;
            case 'no-permission':
                return Alert::getError('Você não tem permissão');
                break;
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
                'email' => $obUser->email,
                'privilegio' => $obUser->privilegio
            ]);
        }
        return $itens;
    }

    /**
     * Método responsável por retornar a view do formulário para cadastro de usuário renderuzada
     * @param Request $request
     * @return string
     */
    public static function getNewUser($request)
    {
        $content = View::render('/usuario/form', [
            'title' => 'Novo Usuário',
            'nome' => '',
            'email' => '',
            'privilegio' => self::getPrivilegio(),
            'status' => self::getStatus($request),
            'buttons' => ''
        ]);

        return parent::getPage('Novo Usuario > ToolsVGL', $content);
    }


    /**
     * Métido responsável por cadastrar um novo usuário
     * @param Request $request
     * @return never
     */
    public static function setNewUser($request)
    {

        $postVars = $request->getPostVars();

        $email = $postVars['email'] ?? '';
        $nome = $postVars['nome'] ?? '';
        $senha = bin2hex(random_bytes(32));
        $privilegio = $postVars['privilegio'] ?? '';

        $obUser = EntityUser::getUserByEmail($email);

        if ($obUser instanceof EntityUser) {
            $request->getRouter()->redirect('/usuario/novo?status=duplicated');
            exit;
        }


        $obUser = new EntityUser;
        $obUser->nome = trim($nome);
        $obUser->email = trim($email);
        $obUser->privilegio = $privilegio;
        $obUser->senha = password_hash($senha, PASSWORD_DEFAULT);

        $obUser->cadastrar();


        $request->getRouter()->redirect('/usuario/edit?id=' . $obUser->id . '&status=created');
        exit;
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
            'email' => $obUser->email,
            'privilegio' => $privilegio,
            'status' => self::getStatus($request),
            'buttons' => self::getButtons($request)
        ]);
        return parent::getPage('Editar Usuario > ToolsVGL', $content);
    }

    private static function getButtons($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];
        $obUser = EntityUser::getUserById(Login::getId());
        if (Login::isLogged() && $id == $obUser->id) {
            return View::render('usuario/elements/button');
        }
        return '';
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
        $nome = $postVars['nome'] ?? $obUser->nome;
        $email = $postVars['email'] ?? $obUser->email;
        $privilegio = $postVars['privilegio'] ?? $obUser->privilegio;

        $obUserEmail = EntityUser::getUserByEmail($email);

        if (isset($obUserEmail->id)) {
            if (($obUser instanceof EntityUser) && ($id != $obUserEmail->id)) {
                $request->getRouter()->redirect('/usuario/edit?id=' . $id . '&status=duplicated');
                exit;
            }
        }
        //Atualização da instancia

        $obUser->nome = $nome;
        $obUser->email = $email;
        $obUser->privilegio = $privilegio;


        $obUser->atualizar();

        if ($id == SessionLogin::getId()) {
            $_SESSION['usuario']['privilegio'] = $privilegio;
        }

        $request->getRouter()->redirect('/usuario/edit?id=' . $id . '&status=updated');
        exit;
    }


    /**
     * Método responsável por retornar a view com o formulário de remoção do usuário
     * @param Request $request
     * @return string
     */
    public static function getDeleteUser($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];


        $obUser = EntityUser::getUserById($id);

        if (!$obUser instanceof EntityUser) {
            $request->getRouter()->redirect('usuario');
            exit;
        }

        $content = View::render('usuario/delete', [
            'nome' => $obUser->nome,
            'email' => $obUser->email
        ]);

        //Retorna a página
        return parent::getPage('Excluir usuário > ToolsVGL', $content);
    }


    /**
     * Método responsável por realizar a remoção do usuário do banco de Ddados
     * @param Request $request
     * @return never
     */
    public static function setDeleteUser($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];
        $obUser = EntityUser::getUserById($id);

        if (!$obUser instanceof EntityUser) {
            $request->getRouter()->redirect('/usuario');
            exit;
        }
        $obUser->excluir();

        $request->getRouter()->redirect('/usuario?status=deleted');
        exit;
    }

}