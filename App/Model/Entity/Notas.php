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
        $periodo = 'data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59"';

        if ($equipe == 'todas') {
            return self::getNotas($periodo);
        }

        if ($equipe == 'ggnet' || $equipe == 'alt') {
            return self::getNotas($periodo . ' AND canal = "' . $equipe . '"');
        }

        return self::getNotas($periodo . ' AND equipe = "' . $equipe . '"');
    }

    public static function getNotasByAgente($agente, $dataInicio, $dataFim, $equipe)
    {
        $periodo = 'data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59"';

        if ($equipe == 'todas') {
            return self::getNotas($periodo . ' AND agente="' . $agente . '"');
        }

        if ($equipe == 'ggnet' || $equipe == 'alt') {
            return self::getNotas($periodo . ' AND canal = "' . $equipe . '" AND agente="' . $agente . '"');
        }

        return self::getNotas($periodo . ' AND equipe = "' . $equipe . '" AND agente="' . $agente . '"');
    }

    public static function getAgentesByFilter($dataInicio, $dataFim, $equipe)
    {
        $periodo = 'data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59"';

        if ($equipe == 'todas') {
            return self::getNotas($periodo, null, null, '*', 'agente');
        }

        if ($equipe == 'ggnet' || $equipe == 'alt') {
            return self::getNotas($periodo . ' AND canal = "' . $equipe . '"', null, null, '*', 'agente');
        }

        return self::getNotas($periodo . ' AND equipe = "' . $equipe . '"', null, null, '*', 'agente');
    }

    public static function getNotasByEquipe($equipe)
    {
        if ($equipe == 'todas') {
            return self::getNotas();
        }

        if ($equipe == 'ggnet' || $equipe == 'alt') {
            return self::getNotas('canal = "' . $equipe . '"');
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