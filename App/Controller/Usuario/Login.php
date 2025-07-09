<?php

namespace App\Controller\Usuario;

use \App\Controller\Pages\Page;
use \App\Utils\View;
use \App\Utils\Alert;
use \App\Controller\Ldap\Ldap;
use \App\Model\Entity\User as EntityUser;
use \App\Session\Login\Login as SessionLogin;

class Login extends Page
{

    /**
     * Método responsável por retornar o formulário de login
     * @param Request request
     * @return string
     */
    public static function getLogin($request)
    {
        return View::render('/usuario/login', [
            'status' => self::getStatus($request)
        ]);
    }

    /**
     * Método responsvel por enviar o login
     * @param Request $request
     * @return string
     */
    public static function setLogin($request)
    {
        //POST VARS
        $postVars = $request->getPostVars();
        $login = $postVars['login'] ?? ';';
        $senha = $postVars['senha'] ?? ';';

        $obUser = EntityUser::getUserByLogin($login);
        if ((!$obUser instanceof EntityUser)) {
            $request->getRouter()->redirect('/login?status=invalid');
            exit;
        }
        //Cria a sessão de login

        if (!Ldap::login($login, $senha)) {
            $request->getRouter()->redirect('/login?status=invalid');
            exit;
        }
        SessionLogin::login($obUser);

        $request->getRouter()->redirect('/');
        exit;
    }

    /**
     * Método responsável por deslogar o usuário
     * @param Requset $request
     * @return never
     */
    public static function setLogout($request)
    {
        SessionLogin::logout();

        $request->getRouter()->redirect('/login');
        exit;
    }
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
            case 'invalid':
                return Alert::getError('Usuário ou senha inválida!');
            case 'email-send':
                return Alert::getSuccess('O E-mail para redefinição de senha foi enviado!');
            case 'password-changed':
                return Alert::getSuccess('A senha foir trocada com sucesso!');
            case 'no-email':
                return Alert::getError('Nenhum usuário encontrado');
            case 'invalid-token':
                return Alert::getError('Token para recuperação inválido');
            case 'password-error':
                return Alert::getError('As senhas não são iguais!');
        }
        return '';
    }

}