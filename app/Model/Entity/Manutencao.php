<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Manutencao
{
    public $evento_id;
    public $dataPrevista;

    public function cadastrar()
    {
        (new Database('manutencoes'))->insert([
            'evento_id' => $this->evento_id,
            'dataPrevista' => $this->dataPrevista
        ]);

        return true;
    }
    public function atualizar()
    {
        (new Database('manutencoes'))->update('evento_id =' . $this->evento_id, [
            'dataPrevista' => $this->dataPrevista
        ]);
    }

    public static function getManutencaoById($evento_id)
    {
        return self::getManutencao('evento_id =' . $evento_id)->fetchObject(self::class);
    }

    public static function getManutencao($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('manutencoes'))->select($where, $order, $limit, $fields);
    }
}