<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Tecnicos
{
    public $id;
    public $nome;

    public function cadastrar()
    {
        (new Database('tecnicos'))->insert([
            'id' => $this->id,
            'nome' => $this->nome,
        ]);

        return true;
    }

    public function atualizar()
    {
        return (new Database('tecnicos'))->update('id =' . $this->id, [
            'nome' => $this->nome
        ]);
    }


    public static function getTecnicos($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('tecnicos'))->select($where, $order, $limit, $fields);
    }

    public static function getTecnicosById($id)
    {
        return (new Database('tecnicos'))->select('id = "' . $id . '"')->fetchObject(self::class);

    }

    public static function getOsByFilter($dataInicio, $dataFim, $tecnico)
    {

        return self::getTecnicos('data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59" AND id_tecnico = "' . $tecnico . '"');
    }
}