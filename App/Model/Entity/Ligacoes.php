<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Ligacoes
{
    public $id;
    public $data;
    public $tempo_fila;
    public $tempo_atendimento;
    public $responsavel;
    public $status;
    public $fila;
    public $numero;
    public $uuid;
    public $nota;

    public static function getLigacoes($where = null, $order = null, $limit = null, $fields = '*', $group = null)
    {
        return (new Database('ligacoes'))->select($where, $order, $limit, $fields, $group);
    }


    public function cadastrar()
    {
        $this->id = (new Database('ligacoes'))->insert([
            'data' => $this->data,
            'tempo_fila' => $this->tempo_fila,
            'tempo_atendimento' => $this->tempo_atendimento,
            'responsavel' => $this->responsavel,
            'status' => $this->status,
            'fila' => $this->fila,
            'numero' => $this->numero,
            'uuid' => $this->uuid,
            'nota' => $this->nota
        ]);

        return true;
    }

    public function atualizar()
    {
        return (new Database('ligacoes'))->update('id =' . $this->id, [
            'data' => $this->data,
            'tempo_fila' => $this->tempo_fila,
            'tempo_atendimento' => $this->tempo_atendimento,
            'responsavel' => $this->responsavel,
            'status' => $this->status,
            'fila' => $this->fila,
            'numero' => $this->numero,
            'uuid' => $this->uuid,
            'nota' => $this->nota
        ]);
    }
    public function excluir()
    {
        return (new Database('ligacoes'))->delete('id =' . $this->id);

    }

    public static function getLigacoesByUuid($uuid)
    {
        return self::getLigacoes('uuid ="' . $uuid . '"')->fetchObject(self::class);
    }

    public static function getLigacoesById($id)
    {
        return self::getLigacoes('id ="' . $id . '"')->fetchObject(self::class);
    }

    public static function getLigacoesByFilter($dataInicio, $dataFim, $fila)
    {
        $periodo = 'data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59"';

        if ($fila == 'todas') {
            return self::getLigacoes($periodo, null, null, '*');
        }
        return self::getLigacoes($periodo . ' AND fila = "' . $fila . '"', null, null, '*');
    }

    public static function getNotasByFilter($dataInicio, $dataFim, $fila)
    {
        $where = 'data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59" AND nota is not null';
        if ($fila == 'todas') {
            return self::getLigacoes($where, null, null, 'nota, fila, responsavel, data, numero');
        }

        return self::getLigacoes($where . ' AND fila = "' . $fila . '"', null, null, 'nota, fila, responsavel, data, numero');
    }

    public static function getNotasByAgente($agente, $dataInicio, $dataFim, $equipe)
    {
        $periodo = 'data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59"';

        if ($equipe == 'todas') {
            return self::getLigacoes($periodo . ' AND responsavel="' . $agente . '"');
        }

        return self::getLigacoes($periodo . ' AND fila = "' . $equipe . '" AND responsavel="' . $agente . '"');
    }

    public static function getAgentesByFilter($dataInicio, $dataFim, $equipe)
    {
        $periodo = 'data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59"';

        if ($equipe == 'todas') {
            return self::getLigacoes($periodo, null, null, '*', 'responsavel');
        }

        return self::getLigacoes($periodo . ' AND fila = "' . $equipe . '"', null, null, '*', 'responsavel');
    }

    public static function getNotasByEquipe($equipe)
    {
        if ($equipe == 'todas') {
            return self::getLigacoes();
        }

        return self::getLigacoes('fila = "' . $equipe . '"');
    }

    public static function getFilas()
    {
        return self::getLigacoes(null, null, null, 'fila', 'fila');
    }
}