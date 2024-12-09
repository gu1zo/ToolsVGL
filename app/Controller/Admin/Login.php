<?php

namespace App\Controller\Admin;

use \App\Utils\View;
use \App\Model\Entity\User;
use \App\Session\Admin\Login as SesisonAdminLogin;

class Login extends Page
{
    public static function getLogin($request, $errorMessage = null)
    {
        $status = !is_null($errorMessage) ? Alert::getError($errorMessage) : '';

        $content = View::render('admin/login', [
            'status' => $status
        ]);

        return parent::getPage('LOGIN > RetisVGL', $content);
    }

    public static function setLogin($request)
    {
        //POST VARS
        $postVars = $request->getPostVars();
        $email = $postVars['email'] ?? ';';
        $senha = $postVars['senha'] ?? ';';


        $obUser = User::getUserByEmail($email);

        if (!$obUser instanceof User) {
            return self::getLogin($request, 'Email ou senha inválidos');
        }

        if (!password_verify($senha, $obUser->senha)) {
            return self::getLogin($request, 'Email ou senha inválidos');
        }

        //Cria a sessão de login
        SesisonAdminLogin::login($obUser);

        $request->getRouter()->redirect('/admin');
        exit;
    }

    public static function setLogout($request)
    {
        SesisonAdminLogin::logout();

        $request->getRouter()->redirect('/admin/login');
        exit;
    }

}