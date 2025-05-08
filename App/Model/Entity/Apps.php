<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Apps
{
    public $id;

    public $link;

    public $img;

    public $titulo;

    public function cadastrar()
    {
        $this->id = (new Database('apps'))->insert([
            'link' => $this->link,
            'img' => $this->img,
            'titulo' => $this->titulo,
        ]);

        return true;
    }
    public static function getApps($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('apps'))->select($where, $order, $limit, $fields);
    }

    public function excluir()
    {
        return (new Database('apps'))->delete('id =' . $this->id);

    }
    public static function getAppById($id)
    {
        return self::getApps('id = "' . $id . '"')->fetchObject(self::class);
    }
}