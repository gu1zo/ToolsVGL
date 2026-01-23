<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Massiva
{
    public $id;
    public $dataInicio;
    public $dataFim;
    public $evento;
    public $int6;
    public $qtd;
    public $regional;
    public $tipo;

    public function cadastrar()
    {
        $this->id = (new Database('massiva'))->insert([
            'dataInicio' => $this->dataInicio,
            'dataFim' => $this->dataFim,
            'evento' => $this->evento,
            'int6' => $this->int6,
            'qtd' => $this->qtd,
            'regional' => $this->regional,
            'tipo' => $this->tipo

        ]);

        return true;
    }

    public static function getMassivas($where = null, $order = null, $limit = null, $fields = '*', $group = null)
    {
        return (new Database('massiva'))->select($where, $order, $limit, $fields, $group);
    }

    public static function getMassivasByFilter($dataInicio, $dataFim)
    {
        return self::getMassivas('dataInicio BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59"');
    }
    public static function getMassivasByFilterTable($dataInicio, $dataFim, $regional)
    {
        return self::getMassivas('dataInicio BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59" AND regional= "' . $regional . '"');
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
            'evento' => $this->evento,
            'int6' => $this->int6,
            'qtd' => $this->qtd,
            'regional' => $this->regional,
            'tipo' => $this->tipo
        ]);
    }

    public function excluir()
    {
        return (new Database('massiva'))->delete('id =' . $this->id);

    }
}