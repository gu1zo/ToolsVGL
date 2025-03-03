<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Agendados
{
    public $id;

    public $protocolo;

    public $data;

    public $tipo;

    public $observacao;

    public $status;
    public $id_usuario;

    public static function getAgendados($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('agendados'))->select($where, $order, $limit, $fields);
    }

    public function cadastrar()
    {
        $this->id = (new Database('agendados'))->insert([
            'protocolo' => $this->protocolo,
            'data' => $this->data,
            'tipo' => $this->tipo,
            'observacao' => $this->observacao,
            'status' => 'agendado',
            'id_usuario' => $this->id_usuario
        ]);

        return true;
    }

    public function atualizar()
    {
        return (new Database('agendados'))->update('id =' . $this->id, [
            'protocolo' => $this->protocolo,
            'data' => $this->data,
            'tipo' => $this->tipo,
            'observacao' => $this->observacao,
            'status' => $this->status,
            'id_usuario' => $this->id_usuario
        ]);
    }

    public static function getAgendadosById($id)
    {
        return self::getAgendados('id =' . $id)->fetchObject(self::class);
    }
    public static function getAgendadosByTipoAndStatus($tipo, $status)
    {
        return self::getAgendados('tipo ="' . $tipo . '" AND status = "' . $status . '"', 'data ASC');
    }

    public function excluir()
    {
        return (new Database('agendados'))->delete('id =' . $this->id);

    }
}