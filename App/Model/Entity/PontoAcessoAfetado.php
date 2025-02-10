<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class PontoAcessoAfetado
{
    public $ponto_acesso_codigo;
    public $evento_id;

    public function cadastrar()
    {
        (new Database('pontos_acesso_afetados'))->insert([
            'evento_id' => $this->evento_id,
            'ponto_acesso_codigo' => $this->ponto_acesso_codigo
        ]);

        return true;
    }

    public static function getPontoAcessoAfetadoById($id)
    {
        return self::getPontoAcessoAfetado('evento_id =' . $id);
    }
    public static function getPontoAcessoAfetadoByIdAndCode($id, $codigo)
    {
        return self::getPontoAcessoAfetado('evento_id ="' . $id . '"AND ponto_acesso_codigo ="' . $codigo . '"')->fetchObject(self::class);
    }

    public static function getPontoAcessoAfetado($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('pontos_acesso_afetados'))->select($where, $order, $limit, $fields);
    }
    public static function excluir($evento_id, $ponto_acesso_codigo)
    {
        return (new Database('pontos_acesso_afetados'))->delete("evento_id =" . $evento_id . " AND ponto_acesso_codigo =" . $ponto_acesso_codigo);

    }

}