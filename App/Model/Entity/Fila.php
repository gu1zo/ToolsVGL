<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Fila
{
    public $id_usuario;

    public $posicao;

    public $data_entrada;
    public $motivo;
    public $data_pausa;
    public $pausa;

    public static function getFila($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('fila'))->select($where, $order, $limit, $fields);
    }

    public function cadastrar()
    {
        (new Database('fila'))->insert([
            'id_usuario' => $this->id_usuario,
            'posicao' => $this->posicao,
            'data_entrada' => $this->data_entrada,
            'motivo' => null,
            'data_pausa' => null,
            'pausa' => 0
        ]);

        return true;
    }

    public function atualizar()
    {
        return (new Database('fila'))->update('id_usuario =' . $this->id_usuario, [
            'id_usuario' => $this->id_usuario,
            'posicao' => $this->posicao,
            'data_entrada' => $this->data_entrada,
            'motivo' => $this->motivo,
            'data_pausa' => $this->data_pausa,
            'pausa' => $this->pausa
        ]);
    }

    public function excluir()
    {
        return (new Database('fila'))->delete('id_usuario =' . $this->id_usuario);

    }

    public static function getFilaById($id)
    {
        return self::getFila('id_usuario ="' . $id . '"', 'posicao ASC')->fetchObject(self::class);
    }
    public static function getTotalUsuarios()
    {
        $result = (new Database('fila'))->select('posicao IS NOT NULL', null, null, 'COUNT(*) as total')->fetch();
        return (int) $result['total'];
    }

}