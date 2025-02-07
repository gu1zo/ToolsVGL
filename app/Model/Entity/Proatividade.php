<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Proatividade
{
    public $id;
    public $protocolo;
    public $data;
    public $regional;
    public $host;
    public $id_usuario_criador;
    public $observacao;

    public function cadastrar()
    {
        $this->id = (new Database('proatividade'))->insert([
            'protocolo' => $this->protocolo,
            'data' => $this->data,
            'regional' => $this->regional,
            'host' => $this->host,
            'observacao' => $this->observacao,
            'id_usuario_criador' => $this->id_usuario_criador
        ]);

        return true;
    }

    public function atualizar()
    {
        return (new Database('proatividade'))->update('id =' . $this->id, [
            'protocolo' => $this->protocolo,
            'data' => $this->data,
            'regional' => $this->regional,
            'host' => $this->host,
            'observacao' => $this->observacao,
            'id_usuario_criador' => $this->id_usuario_criador
        ]);
    }

    public function excluir($id)
    {
        return (new Database('proatividade'))->delete('id =' . $this->id);

    }
    public static function getProatividadeByProtocol($protocolo)
    {
        return (new Database('proatividade'))->select('protocolo = "' . $protocolo . '"')->fetchObject(self::class);
    }

    public static function getProatividadeById($id)
    {
        return self::getProatividade('id =' . $id)->fetchObject(self::class);
    }

    public static function getProatividade($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('proatividade'))->select($where, $order, $limit, $fields);
    }

}