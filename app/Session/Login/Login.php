<?php

namespace App\Session\Login;

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


        $_SESSION['usuario'] = [
            'id' => $obUser->id,
            'nome' => $obUser->nome,
            'email' => $obUser->email,
            'setor' => $obUser->setor,
            'privilegio' => $obUser->privilegio
        ];

        return true;
    }

    public static function isLogged()
    {
        self::init();
        return (isset($_SESSION['usuario']['id']));
    }


    public static function isAdmin()
    {
        self::init();
        return $_SESSION['usuario']['privilegio'] == 'admin';
    }

    public static function getId()
    {
        self::init();
        return $_SESSION['usuario']['id'];
    }

    public static function getEmail()
    {
        self::init();
        return $_SESSION['usuario']['email'];
    }

    public static function logout()
    {
        self::init();

        unset($_SESSION['usuario']);

        return true;
    }
}