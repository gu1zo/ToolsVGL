<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class FilaEmails
{

    public $id;
    public $titulo;
    public $email;
    public $status;
    public $body;

    public static function getFilaEmails($where = null, $order = null, $limit = null, $fields = '*', $group = null)
    {
        return (new Database('fila_emails'))->select($where, $order, $limit, $fields, $group);
    }

    public function cadastrar()
    {
        (new Database('fila_emails'))->insert([
            'id' => $this->id,
            'titulo' => $this->titulo,
            'email' => $this->email,
            'body' => $this->body

        ]);

        return true;
    }

    public function atualizar()
    {
        return (new Database('fila_emails'))->update('id =' . $this->id, [
            'id' => $this->id,
            'titulo' => $this->titulo,
            'email' => $this->email,
            'status' => $this->status,
            'body' => $this->body
        ]);
    }

    public function excluir()
    {
        return (new Database('fila_emails'))->delete('id =' . $this->id);

    }
}