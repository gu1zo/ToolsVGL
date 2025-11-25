<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class EquipamentosOrdensServico
{
    public $id;
    public $idOs;
    public $item;
    public $qtd;

    public $id_os;

    public function cadastrar()
    {
        $this->id = (new Database('equipamentos_ordens_servico'))->insert([
            'id_os' => $this->idOs,
            'item' => $this->item,
            'qtd' => $this->qtd,
        ]);

        return true;
    }

    public static function getEquipamentosOrdensServico($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('equipamentos_ordens_servico'))->select($where, $order, $limit, $fields);
    }

    public static function getEquipamentosOrdemServicoByIdOs($idOs)
    {
        return self::getEquipamentosOrdensServico('id_os = "' . $idOs . '"');

    }

    public static function getTotalEquipimanetosByIdOs($idOs)
    {
        return self::getEquipamentosOrdensServico('id_os = "' . $idOs . '"', null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

    }
}