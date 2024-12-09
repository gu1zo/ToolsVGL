<?php

namespace App\Model\Entity;

use PDOStatement;
use WilliamCosta\DatabaseManager\Database;
class Testimony
{
    public $id;
    public $nome;
    public $mensagem;
    public $data;

    public function cadastrar()
    {
        $this->data = date('Y-m-d H:i:s');
        $this->id = (new Database('depoimentos'))->insert([
            'nome' => $this->nome,
            'mensagem' => $this->mensagem,
            'data' => $this->data
        ]);

        return true;
    }

    /**
     * Método responsável por retoranr depoimentos
     * @param string $where
     * @param string $order
     * @param string $limit
     * @param string $field
     * @return \PDOStatment
     */
    public static function getTestimonies($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('depoimentos'))->select($where, $order, $limit, $fields);
    }

    /**
     * Método responsável por retornar um depoimento pelo id
     * @param int $id
     * @return Testimony
     */
    public static function getTestimonyById($id)
    {
        return self::getTestimonies('id =' . $id)->fetchObject(self::class);
    }

    public function atualizar()
    {
        return (new Database('depoimentos'))->update('id =' . $this->id, [
            'nome' => $this->nome,
            'mensagem' => $this->mensagem,
            'data' => $this->data
        ]);
    }
    public function excluir($id)
    {
        return (new Database('depoimentos'))->delete('id =' . $this->id);

    }
}