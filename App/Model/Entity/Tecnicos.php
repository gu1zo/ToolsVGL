<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Tecnicos
{
    public $id;
    public $nome;

    public function cadastrar()
    {
        $this->id = (new Database('tecnicos'))->insert([
            'nome' => $this->nome,
        ]);

        return true;
    }

    public static function getTecnicos($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('tecnicos'))->select($where, $order, $limit, $fields);
    }

    public static function getTecnicosById($id)
    {
        return (new Database('tecnicos'))->select('id = "' . $id . '"')->fetchObject(self::class);

    }
}