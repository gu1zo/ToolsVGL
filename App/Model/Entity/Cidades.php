<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Cidades
{
    public $id;
    public $nome;
    public $massiva;

    public static function getCidades($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('cidades'))->select($where, $order, $limit, $fields);
    }

    public static function getCidadesByName($name)
    {
        return self::getCidades('nome = "' . $name . '"')->fetchObject(self::class);
    }

    public static function getCidadesById($id)
    {
        return self::getCidades('id = "' . $id . '"')->fetchObject(self::class);
    }


    public function cadastrar()
    {
        $this->id = (new Database('cidades'))->insert([
            'nome' => $this->nome,
            'massiva' => $this->massiva
        ]);

        return true;
    }

    public function atualizar()
    {
        return (new Database('cidades'))->update('id =' . $this->id, [
            'nome' => $this->nome,
            'massiva' => $this->massiva
        ]);
    }
}