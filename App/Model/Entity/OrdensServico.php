<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class OrdensServico
{
    public $id;
    public $numero;
    public $data;
    public $idTecnico;
    public $nomeTecnico;
    public $cliente;
    public $tipo;
    public $confirmacao;

    public function cadastrar()
    {
        $this->id = (new Database('tecnicos'))->insert([
            'numero' => $this->numero,
            'data' => $this->data,
            'id-tecnico' => $this->idTecnico,
            'nome-tecnico' => $this->nomeTecnico,
            'cliente' => $this->cliente,
            'tipo' => $this->tipo,
        ]);

        return true;
    }

    public static function getOrdensServico($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('ordens-servico'))->select($where, $order, $limit, $fields);
    }

    public static function getOrdemServicoById($id)
    {
        return self::getOrdensServico('id = "' . $id . '"')->fetchObject(self::class);

    }
}