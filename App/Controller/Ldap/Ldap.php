<?php

namespace App\Controller\Ldap;

use stdClass;
class Ldap
{
    private $uri;
    private $login;
    private $pass;
    private $base_dn;
    private $domain;

    private function __construct()
    {
        $this->uri = getenv('LDAP_URI');
        $this->domain = getenv('LDAP_DOMAIN');
        $this->login = getenv('LDAP_DOMAIN') . "\\" . getenv('LDAP_LOGIN');
        $this->pass = getenv('LDAP_PASS');
        $this->base_dn = getenv('LDAP_BASE_DN');

    }

    public static function login($login, $pass)
    {
        $instance = new self();

        $ldap_conn = ldap_connect($instance->uri);

        if (!$ldap_conn) {
            return false;
        }

        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);

        $ldap_user = $instance->domain . "\\" . $login;

        if (!@ldap_bind($ldap_conn, $ldap_user, $pass)) {
            ldap_unbind($ldap_conn);  // ainda que falhe, faz unbind para segurança
            return false;
        }

        ldap_unbind($ldap_conn);
        return true;
    }

    public static function getUsers()
    {
        $instance = new self();

        $ldap_conn = ldap_connect($instance->uri);

        ldap_set_option($ldap_conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        //ldap_set_option($ldap_conn, LDAP_OPT_REFERRALS, 0);

        if (!@ldap_bind($ldap_conn, $instance->login, $instance->pass)) {
            die("Falha na autenticação LDAP");
        }

        // Filtro para usuários ativos
        $filter = "(&(objectClass=user)(objectCategory=person)(!(userAccountControl:1.2.840.113556.1.4.803:=2)))";

        // Atributos para buscar
        $attributes = ["cn", "samaccountname", "dn"];

        $search = ldap_search($ldap_conn, $instance->base_dn, $filter, $attributes);
        if (!$search) {
            die("Erro na pesquisa LDAP");
        }

        $entries = ldap_get_entries($ldap_conn, $search);

        $usuarios = [];

        for ($i = 0; $i < $entries["count"]; $i++) {
            $user = $entries[$i];

            $obj = new stdClass();
            $obj->nome = $user["cn"][0] ?? "";
            $obj->login = $user["samaccountname"][0] ?? "";

            $usuarios[] = $obj;
        }

        ldap_unbind($ldap_conn);

        return $usuarios;
    }
}