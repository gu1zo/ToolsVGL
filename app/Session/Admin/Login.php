<?php

namespace App\Session\Admin;

class Login
{
    /**
     * Métdo responsável por iniciar a sessão
     */
    private static function init()
    {
        if (session_status() != PHP_SESSION_ACTIVE) {
            session_start();
        }
    }
    /**
     * Método responsável por criar o login do usuario
     * @param User $obUser
     * @return boolean
     */
    public static function login($obUser)
    {
        //INICIA A SESSÃO
        self::init();


        $_SESSION['admin']['usuario'] = [
            'id' => $obUser->id,
            'nome' => $obUser->nome,
            'email' => $obUser->email
        ];

        return true;
    }
    public static function isLogged()
    {
        self::init();
        return (isset($_SESSION['admin']['usuario']['id']));
    }

    public static function logout()
    {
        self::init();

        unset($_SESSION['admin']['usuario']);

        return true;
    }
}