<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Notas
{
    public $id;
    public $nota;
    public $equipe;
    public $mensagem;
    public $canal;
    public $data;
    public $protocolo;
    public $agente;

    public static function getNotas($where = null, $order = null, $limit = null, $fields = '*', $group = null)
    {
        return (new Database('notas'))->select($where, $order, $limit, $fields, $group);
    }

    public function cadastrar()
    {
        $this->id = (new Database('notas'))->insert([
            'nota' => $this->nota,
            'equipe' => $this->equipe,
            'mensagem' => $this->mensagem,
            'canal' => $this->canal,
            'data' => $this->data,
            'protocolo' => $this->protocolo,
            'agente' => $this->agente
        ]);

        return true;
    }

    public static function getNotasByCanal($canal)
    {
        return self::getNotas('canal = "' . $canal . '"');
    }

    public static function getNotasByFilter($dataInicio, $dataFim, $equipe)
    {
        if ($equipe == 'todas') {
            return self::getNotas('data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59"');

        }
        return self::getNotas('data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59" AND equipe = "' . $equipe . '"');
    }

    public static function getNotasByAgente($agente, $dataInicio, $dataFim, $equipe)
    {
        if ($equipe == 'todas') {
            return self::getNotas('data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59" AND agente=' . $agente . '"');

        }
        return self::getNotas('data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59" AND equipe = "' . $equipe . '" AND agente="' . $agente . '"');
    }
    public static function getAgentesByFilter($dataInicio, $dataFim, $equipe)
    {
        if ($equipe == 'todas') {
            return self::getNotas('data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59"', null, null, '*', 'agente');

        }
        return self::getNotas('data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59" AND equipe = "' . $equipe . '"', null, null, '*', 'agente');
    }

    public static function getNotasByEquipe($equipe)
    {
        if ($equipe == 'todas') {
            return self::getNotas()->fetchObject(self::class);

        }
        return self::getNotas('equipe = "' . $equipe . '"');
    }



    public static function getNotasByProtocolo($protocolo)
    {
        return self::getNotas('protocolo = "' . $protocolo . '"')->fetchObject(self::class);
    }

    public static function getNotaById($id)
    {
        return self::getNotas('id = "' . $id . '"')->fetchObject(self::class);
    }
    public static function getEquipes()
    {
        return self::getNotas(null, null, null, 'equipe', 'equipe');
    }

    public function atualizar()
    {
        return (new Database('notas'))->update('id =' . $this->id, [
            'nota' => $this->nota,
            'equipe' => $this->equipe,
            'mensagem' => $this->mensagem,
            'canal' => $this->canal,
            'data' => $this->data,
            'protocolo' => $this->protocolo,
            'agente' => $this->agente
        ]);
    }

    public function excluir()
    {
        return (new Database('notas'))->delete('id =' . $this->id);

    }
}