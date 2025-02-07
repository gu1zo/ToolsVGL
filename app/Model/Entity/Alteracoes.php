<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Alteracoes
{
    public $id;
    public $evento_id;
    public $alteracao;
    public $data;
    public $id_usuario_criador;

    public function cadastrar()
    {
        $this->id = (new Database('alteracoes'))->insert([
            'evento_id' => $this->evento_id,
            'alteracao' => $this->alteracao,
            'data' => $this->data,
            'id_usuario_criador' => $this->id_usuario_criador,
        ]);

        return true;
    }

    public static function getAlteracoes($where = null, $order = null, $limit = null, $fields = '*', $group = null)
    {
        return (new Database('alteracoes'))->select($where, $order, $limit, $fields, $group);
    }
    public static function getAlteracoesByEventoId($id)
    {
        return self::getAlteracoes('evento_id = "' . $id . '"', 'data ASC');
    }
    public static function getComentarioById($id)
    {
        return self::getAlteracoes('id = "' . $id . '"')->fetchObject(self::class);
    }

    public function excluir()
    {
        return (new Database('alteracoes'))->delete('id =' . $this->id);

    }
}