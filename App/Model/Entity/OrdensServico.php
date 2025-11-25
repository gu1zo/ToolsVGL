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
    public $obs;
    public $pppoe;
    public $solicitado;
    public $plano;
    public $tipo_fechamento;
    public $tempo;
    public $qtd;

    public function cadastrar()
    {
        $this->id = (new Database('ordens_servico'))->insert([
            'numero' => $this->numero,
            'data' => $this->data,
            'id_tecnico' => $this->id_tecnico,
            'nome_tecnico' => $this->nome_tecnico,
            'cliente' => $this->cliente,
            'tipo' => $this->tipo,
            'obs' => $this->obs,
            'pppoe' => $this->pppoe,
            'solicitado' => $this->solicitado,
            'plano' => $this->plano,
            'tipo_fechamento' => $this->tipo_fechamento,
            'tempo' => $this->tempo,
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
    public static function getOsByFilter($dataInicio, $dataFim, $tecnico, $avaliado = false)
    {
        if ($avaliado) {
            return self::getOrdensServico('data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59" AND id_tecnico = "' . $tecnico . '"');
        } else {
            return self::getOrdensServico('data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59" AND id_tecnico = "' . $tecnico . '" AND confirmacao = 0');

        }

    }

    public static function getTotalOsByFilter($dataInicio, $dataFim, $tecnico)
    {
        return self::getOrdensServico('data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59" AND id_tecnico = "' . $tecnico . '"', null, null, 'COUNT(*) as qtd')->fetchObject(self::class)->qtd;
    }

    public function atualizar()
    {
        return (new Database('ordens_servico'))->update('id =' . $this->id, [
            'numero' => $this->numero,
            'data' => $this->data,
            'id_tecnico' => $this->id_tecnico,
            'nome_tecnico' => $this->nome_tecnico,
            'cliente' => $this->cliente,
            'tipo' => $this->tipo,
            'obs' => $this->obs,
            'pppoe' => $this->pppoe,
            'solicitado' => $this->solicitado,
            'plano' => $this->plano,
            'tipo_fechamento' => $this->tipo_fechamento,
            'tempo' => $this->tempo,
            'confirmacao' => $this->confirmacao
        ]);
    }
}