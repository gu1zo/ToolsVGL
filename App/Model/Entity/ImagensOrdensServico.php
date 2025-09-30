<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class ImagensOrdensServicoOrdensServico
{
    public $id;
    public $idOs;
    public $url;
    public $descricao;

    public function cadastrar()
    {
        $this->id = (new Database('imagens-ordens-servico'))->insert([
            'id_os' => $this->idOs,
            'url' => $this->url,
            'descricao' => $this->descricao,
        ]);

        return true;
    }

    public static function getImagensOrdensServico($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('imagens-ordens-servico'))->select($where, $order, $limit, $fields);
    }

    public static function getImagemOrdemServicoByIdOs($idOs)
    {
        return self::getImagensOrdensServico('id_os = "' . $idOs . '"')->fetchObject(self::class);

    }
}