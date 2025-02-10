<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class EventoConclusao
{
    public $evento_id;
    public $motivo;
    public $forca_maior;
    public $comentario;

    public function cadastrar()
    {
        (new Database('evento_conclusao'))->insert([
            'evento_id' => $this->evento_id,
            'motivo' => $this->motivo,
            'forca_maior' => $this->forca_maior,
            'comentario' => $this->comentario,
        ]);

        return true;
    }
    public static function getEventoConclusaoById($id)
    {
        return self::getEventoConclusao('evento_id =' . $id)->fetchObject(self::class);
    }

    public static function getEventoConclusao($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('evento_conclusao'))->select($where, $order, $limit, $fields);
    }
}