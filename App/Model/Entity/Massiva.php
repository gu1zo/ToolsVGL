<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Massiva
{
    public $id;
    public $dataInicio;
    public $dataFim;
    public $evento;

    public function cadastrar()
    {
        $this->id = (new Database('massiva'))->insert([
            'dataInicio' => $this->dataInicio,
            'dataFim' => $this->dataFim,
            'evento' => $this->evento

        ]);

        return true;
    }

    public static function getMassivas($where = null, $order = null, $limit = null, $fields = '*', $group = null)
    {
        return (new Database('massiva'))->select($where, $order, $limit, $fields, $group);
    }
    public static function getMassivaById($id)
    {
        return self::getMassivas('id = "' . $id . '"')->fetchObject(self::class);
    }

    public function atualizar()
    {
        return (new Database('massiva'))->update('id =' . $this->id, [
            'dataInicio' => $this->dataInicio,
            'dataFim' => $this->dataFim,
            'evento' => $this->evento
        ]);
    }

    public function excluir()
    {
        return (new Database('massiva'))->delete('id =' . $this->id);

    }
}