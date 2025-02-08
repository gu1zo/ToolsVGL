<?php

namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;
class Joins
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
    public $ponto_acesso_codigo;
    public $ponto_acesso_nome;
    public $total_vezes_afetado;
    public $tempo_total_afetado;
    public $motivo_conclusao;
    public $usuario_nome;
    public $evento_id;
    public $data;
    public $comentario;

    public static function getEventosByDateAndMonth($dataInicio, $dataAtual)
    {
        return (new Database('eventos'))->select('dataInicio BETWEEN "' . $dataInicio . '" AND "' . $dataAtual . '"', 'dataInicio', null, 'DATE_FORMAT(dataInicio, "%b %Y") AS mes, tipo, COUNT(*) as total', 'mes, tipo');
    }

    public static function getEventosByDate($dataInicio, $dataAtual)
    {
        return (new Database('eventos'))->select('dataInicio BETWEEN "' . $dataInicio . '" AND "' . $dataAtual . '"', 'dataInicio', null, 'DATE_FORMAT(dataInicio, "%b %Y") AS mes, dataInicio, dataFim');
    }
    public static function getClientesEventosByDate($dataInicio, $dataAtual)
    {
        return (new Database('eventos'))->select('dataInicio BETWEEN "' . $dataInicio . '" AND "' . $dataAtual . '"', 'dataInicio', null, 'DATE_FORMAT(dataInicio, "%b %Y") AS mes, clientes');
    }
    public static function getEventosForcaMaiorByDate($dataInicio, $dataAtual)
    {
        return (new Database('eventos e JOIN evento_conclusao ec ON e.id = ec.evento_id'))->select(' e.dataInicio BETWEEN "' . $dataInicio . '" AND "' . $dataAtual . '"', 'e.dataInicio;', null, 'DATE_FORMAT(dataInicio, "%b %Y") AS mes, ec.forca_maior');
    }
    public static function getTop10CaixasByDate($dataInicio, $dataAtual)
    {
        return (new Database('pontos_acesso_afetados paa JOIN eventos e ON paa.evento_id = e.id JOIN pontos_acesso pa ON paa.ponto_acesso_codigo = pa.codigo'))->select(' e.dataInicio BETWEEN "' . $dataInicio . '" AND "' . $dataAtual . '"', 'total_vezes_afetado DESC', 10, 'pa.codigo AS ponto_acesso_codigo, pa.nome AS ponto_acesso_nome, COUNT(paa.ponto_acesso_codigo) AS total_vezes_afetado, SEC_TO_TIME(SUM(TIMESTAMPDIFF(SECOND, e.dataInicio, e.dataFim))) AS tempo_total_afetado', 'paa.ponto_acesso_codigo, pa.nome');
    }
    public static function getTop10MotivosByDate($dataInicio, $dataAtual)
    {
        return (new Database('evento_conclusao ec JOIN eventos e ON ec.evento_id = e.id'))->select(' e.dataInicio BETWEEN "' . $dataInicio . '" AND "' . $dataAtual . '"', 'total DESC', 10, 'ec.motivo AS motivo_conclusao, COUNT(ec.motivo) AS total', 'ec.motivo');
    }

    public static function getLastInfoById($evento_id)
    {
        return (new Database('comentarios c JOIN usuarios u ON c.id_usuario_criador = u.id'))->select('c.evento_id = "' . $evento_id . '"', 'c.data DESC', 1, ' c.evento_id, c.id_usuario_criador, c.data, c.comentario, u.nome AS usuario_nome')->fetchObject(self::class);
    }
    public static function getEventoByStatus($status)
    {
        return (new Database('eventos e JOIN usuarios u ON e.id_usuario_criador = u.id'))->select('e.status = "' . $status . '"', null, null, ' e.*, u.nome AS usuario_nome');
    }


}