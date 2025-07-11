<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class User
{
    public $id;
    public $nome;
    public $login;
    public $privilegio;
    public $setor;
    public $email;
    public $senha;
    public $recovery_token;
    public $ldap;

    public static function getUsers($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('usuarios'))->select($where, $order, $limit, $fields);
    }
    public static function getUserByLogin($login)
    {
        return (new Database('usuarios'))->select('login = "' . $login . '"')->fetchObject(self::class);
    }
    public function cadastrar()
    {
        $this->id = (new Database('usuarios'))->insert([
            'nome' => $this->nome,
            'login' => $this->login,
            'privilegio' => $this->privilegio,
            'ldap' => $this->ldap
        ]);

        return true;
    }

    public function atualizar()
    {
        return (new Database('usuarios'))->update('id =' . $this->id, [
            'nome' => $this->nome,
            'login' => $this->login,
            'privilegio' => $this->privilegio,
            'ldap' => $this->ldap
        ]);
    }

    public static function getUserById($id)
    {
        return self::getUsers('id = "' . $id . '"')->fetchObject(self::class);
    }

    public function excluir()
    {
        return (new Database('usuarios'))->delete('id =' . $this->id);

    }
}