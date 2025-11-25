<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;
use DateTime;
class Avaliacoes
{
    public $id;
    public $idOs;
    public $id_os;
    public $idTecnico;
    public $nota;
    public $descricao;
    public $data;

    public function cadastrar()
    {
        date_default_timezone_set('America/Sao_Paulo');
        $data = (new DateTime())->format('Y-m-d H:i:s');

        $this->id = (new Database('avaliacoes'))->insert([
            'id_os' => $this->idOs,
            'id_tecnico' => $this->idTecnico,
            'nota' => $this->nota,
            'descricao' => $this->descricao,
            'data' => $data
        ]);

        return true;
    }

    public static function getTecnicos()
    {
        return self::getAvaliacoes(null, null, null, '*', 'id_tecnico');
    }

    public static function getAvaliacoesByTecnico($idTecnico)
    {
        return self::getAvaliacoes('id_tecnico ="' . $idTecnico . '"');
    }

    public static function getAvaliacoes($where = null, $order = null, $limit = null, $fields = '*', $group = null)
    {
        return (new Database('avaliacoes'))->select($where, $order, $limit, $fields, $group);
    }

    public static function getAvaliacoesByIdOS($idOs)
    {
        return self::getAvaliacoes('id_os = "' . $idOs . '"')->fetchObject(self::class);

    }
}