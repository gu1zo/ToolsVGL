<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class OrdensServico
{
    public $id;
    public $numero;
    public $data;
    public $id_tecnico;
    public $nome_tecnico;
    public $cliente;
    public $tipo;
    public $confirmacao;

    public function cadastrar()
    {
        $this->id = (new Database('ordens_servico'))->insert([
            'numero' => $this->numero,
            'data' => $this->data,
            'id_tecnico' => $this->id_tecnico,
            'nome_tecnico' => $this->nome_tecnico,
            'cliente' => $this->cliente,
            'tipo' => $this->tipo,
        ]);

        return true;
    }

    public static function getOrdensServico($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('ordens_servico'))->select($where, $order, $limit, $fields);
    }

    public static function getOrdemServicoById($id)
    {
        return self::getOrdensServico('id = "' . $id . '"')->fetchObject(self::class);

    }
    public static function getOrdemServicoByNumber($number)
    {
        return self::getOrdensServico('numero = "' . $number . '"')->fetchObject(self::class);

    }
    public static function getOsByFilter($dataInicio, $dataFim, $tecnico)
    {

        return self::getOrdensServico('data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59" AND id_tecnico = "' . $tecnico . '"');
    }
}