<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class PontoAcesso
{

    public $id;
    public $codigo;
    public $nome;
    public $latitude;
    public $longitude;

    public function cadastrar()
    {
        $this->id = (new Database('pontos_acesso'))->insert([
            'codigo' => $this->codigo,
            'nome' => $this->nome,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ]);

        return true;
    }


    public static function getPontosAcesso($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('pontos_acesso'))->select($where, $order, $limit, $fields);
    }


    public static function getPontoByCode($codigo)
    {
        return (new Database('pontos_acesso'))->select('codigo = "' . $codigo . '"')->fetchObject(self::class);
    }
    public static function getPontoByName($nome)
    {
        return (new Database('pontos_acesso'))->select('nome = "' . $nome . '"')->fetchObject(self::class);
    }
    public static function getCodeByName($nome)
    {
        return (new Database('pontos_acesso'))->select('nome = "' . $nome . '"', null, null, 'codigo')->fetchObject(self::class);
    }
    public static function getPontoByCodeAndName($codigo, $nome)
    {
        return (new Database('pontos_acesso'))->select('codigo = "' . $codigo . '" AND nome = "' . $nome . '"')->fetchObject(self::class);
    }

    public static function getCodeAndNameById($id)
    {
        return (new Database('pontos_acesso pa JOIN pontos_acesso_afetados paa ON pa.codigo = paa.ponto_acesso_codigo'))->select('paa.evento_id = "' . $id . '"', null, null, 'pa.nome, pa.codigo');
    }
}