<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Avaliacoes
{
    public $id;
    public $idOs;
    public $idTecnico;
    public $nota;
    public $descricao;

    public function cadastrar()
    {
        $this->id = (new Database('avaliacoes'))->insert([
            'id_os' => $this->idOs,
            'id_tecnico' => $this->idTecnico,
            'nota' => $this->nota,
            'descricao' => $this->descricao,
        ]);

        return true;
    }

    public static function getAvaliacoes($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('avaliacoes'))->select($where, $order, $limit, $fields);
    }

    public static function getAvaliacoesByIdOS($idOs)
    {
        return self::getAvaliacoes('id_os = "' . $idOs . '"')->fetchObject(self::class);

    }
}