<?php

namespace App\Controller\Usuario;

use \App\Controller\Pages\Page;
use \App\Utils\View;
use \App\Utils\Alert;
use \App\Controller\Email\Email;
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
        $email = $postVars['email'] ?? ';';
        $senha = $postVars['senha'] ?? ';';

        $obUser = EntityUser::getUserByEmail($email);
        if ((!$obUser instanceof EntityUser) || (!password_verify($senha, $obUser->senha))) {
            $request->getRouter()->redirect('/login?status=invalid');
            exit;
        }
        //Cria a sessão de login
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
    public static function getRecuperarSenha($request)
    {
        return View::render('usuario/senha', [
            'status' => self::getStatus($request)
        ]);
    }

    public static function setRecuperarSenha($request)
    {
        $postVars = $request->getPostVars();

        $email = $postVars['email'];

        $obUser = EntityUser::getUserByEmail($email);

        if (!$obUser instanceof EntityUser) {
            $request->getRouter()->redirect('/recuperar-senha?status=no-email');
            exit;
        }
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);

        $obUser->recovery_token = $tokenHash;
        $obUser->atualizar();

        $vars['url'] = URL . '/recuperacao?token=' . $token;
        $vars['assunto'] = 'Recuperação de Senha';

        $cliente['nome'] = $obUser->nome;
        $cliente['e_mail'] = $obUser->email;
        Email::send('trocar-senha', $vars, $cliente);

        $request->getRouter()->redirect('/recuperar-senha?status=email-send');
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
                break;
            case 'email-send':
                return Alert::getSuccess('O E-mail para redefinição de senha foi enviado!');
                break;
            case 'password-changed':
                return Alert::getSuccess('A senha foir trocada com sucesso!');
                break;
            case 'no-email':
                return Alert::getError('Nenhum usuário encontrado');
                break;
            case 'invalid-token':
                return Alert::getError('Token para recuperação inválido');
                break;
            case 'password-error':
                return Alert::getError('As senhas não são iguais!');
                break;
        }
        return '';
    }

    public static function getRecuperar($request)
    {
        $queryParams = $request->getQueryParams();
        $token = $queryParams['token'];
        $tokenHash = hash('sha256', $token);


        $results = EntityUser::getUsers();

        while ($obUser = $results->fetchObject(EntityUser::class)) {
            if (!isset($obUser->recovery_token)) {
                continue;
            }
            if (hash_equals($obUser->recovery_token, $tokenHash)) {

                return View::render('usuario/recuperar-senha', [
                    'status' => self::getStatus($request),
                    'id' => $obUser->id
                ]);
            }
        }
        $request->getRouter()->redirect('recuperar-senha?status=invalid-token');
        exit;
    }

    public static function setRecuperar($request)
    {
        $postVars = $request->getPostVars();
        $senha = $postVars['senha'] ?? '';
        $id = $postVars['id'];

        $obUser = EntityUser::getUserById($id);

        if (!$obUser instanceof EntityUser) {
            $request->getRouter()->redirect('recuperar-senha?status=invalid-token');
            exit;
        }

        $obUser->senha = password_hash($senha, PASSWORD_DEFAULT);
        $obUser->atualizar();

        $request->getRouter()->redirect('/login?status=password-changed');
        exit;
    }

}