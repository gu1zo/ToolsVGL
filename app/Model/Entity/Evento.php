<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Evento
{
    public $id;
    public $tipo;
    public $protocolo;
    public $dataInicio;
    public $dataFim;
    public $regional;
    public $observacao;
    public $email;
    public $clientes;
    public $id_usuario_criador;
    public $status = 'em execucao';

    public $mes;

    public $total;

    public $forca_maior;


    public function cadastrar()
    {
        $this->id = (new Database(table: 'eventos'))->insert([
            'tipo' => $this->tipo,
            'protocolo' => $this->protocolo,
            'dataInicio' => $this->dataInicio,
            'regional' => $this->regional,
            'observacao' => $this->observacao,
            'status' => $this->status,
            'email' => $this->email,
            'clientes' => $this->clientes,
            'id_usuario_criador' => $this->id_usuario_criador
        ]);
        return true;
    }
    public function atualizar()
    {
        (new Database('eventos'))->update('id =' . $this->id, [
            'tipo' => $this->tipo,
            'protocolo' => $this->protocolo,
            'dataInicio' => $this->dataInicio,
            'regional' => $this->regional,
            'observacao' => $this->observacao,
            'status' => $this->status,
            'email' => $this->email,
            'clientes' => $this->clientes,
            'dataFim' => $this->dataFim
        ]);
        return true;
    }
    public static function getEventoByProtocol($protocolo)
    {
        return (new Database('eventos'))->select('protocolo = "' . $protocolo . '"')->fetchObject(self::class);
    }


    public static function getEventoByStatus($status)
    {
        if (isset($status)) {
            return (new Database('eventos'))->select('status = "' . $status . '"');
        }
        return (new Database('eventos'))->select();
    }

    public static function getEventoById($id)
    {
        return self::getEvento('id =' . $id)->fetchObject(self::class);
    }
    public static function getEvento($where = null, $order = null, $limit = null, $fields = '*')
    {
        return (new Database('eventos'))->select($where, $order, $limit, $fields);
    }

    public static function getQtdEventoByStatus($status)
    {
        return (new Database('eventos'))->select('status = "' . $status . '"', null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;
    }

    public static function getTotalClientesAfetados()
    {
        $total = 0;

        $results = self::getEventoByStatus('em execucao');
        while ($obEvento = $results->fetchObject(self::class)) {

            $clientes = json_decode($obEvento->clientes, true);

            foreach ($clientes as $k) {
                $total++;
            }
        }
        return $total;
    }
    public static function getEventosByDateAndMonth($dataInicio, $dataAtual)
    {
        return (new Database('eventos'))->select('dataInicio BETWEEN "' . $dataInicio . '" AND "' . $dataAtual . '"', 'dataInicio');
    }



}