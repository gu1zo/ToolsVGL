<?php
namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Queues
{
    public $id;
    public $nome;
    public function cadastrar()
    {
        (new Database('queues'))->insert([
            'id' => $this->id,
            'nome' => $this->nome,
        ]);
        return true;
    }

    public function atualizar()
    {
        return (new Database('queues'))->update('id =' . $this->id, [
            'nome' => $this->nome,
        ]);
    }

    public static function getQueueById($id)
    {
        return self::getQueues('id = "' . $id . '"')->fetchObject(self::class);
    }

    public static function getQueues($where = null, $order = null, $limit = null, $fields = '*', $group = null)
    {
        return (new Database('queues'))->select($where, $order, $limit, $fields, $group);
    }


}