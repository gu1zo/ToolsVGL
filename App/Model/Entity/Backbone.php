<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Backbone
{
    public $evento_id;
    public $backbone;

    public function cadastrar()
    {
        (new Database('backbone'))->insert([
            'evento_id' => $this->evento_id,
            'backbone' => $this->backbone
        ]);

        return true;
    }
    public function atualizar()
    {
        (new Database('backbone'))->update('evento_id =' . $this->evento_id, [
            'backbone' => $this->backbone
        ]);
    }

    public static function getBackboneById($evento_id)
    {
        return self::getBackbone('evento_id =' . $evento_id)->fetchObject(self::class);
    }

    public static function getBackbone($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('backbone'))->select($where, $order, $limit, $fields);
    }
}