<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Perdidas
{
    public $id;

    public $motivo;

    public $espera;

    public $posicao;

    public $originador;

    public $data;
    public $dnis;

    public static function getPerdidas($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('perdidas'))->select($where, $order, $limit, $fields);
    }

    public function cadastrar()
    {
        $this->id = (new Database('perdidas'))->insert([
            'motivo' => $this->motivo,
            'data' => $this->data,
            'espera' => $this->espera,
            'posicao' => $this->posicao,
            'originador' => $this->originador,
            'dnis' => $this->dnis
        ]);

        return true;
    }
}