<?php

namespace App\Model\Entity\Sippulse;

use WilliamCosta\DatabaseManager\DatabaseSIP;

class cdrs_full
{
    public $id;
    public $uuid;
    public $start_stamp;
    public $queue_waiting_duration;
    public $queue_call_duration;
    public $queue_user_name;
    public $queue_status;
    public $queue_name;
    public $queue_id;
    public $caller_id;
    public $digit;

    public static function getLigacoes(
        $where = null,
        $order = null,
        $limit = null,
        $fields = 'id, uuid, start_stamp, queue_waiting_duration, queue_call_duration, queue_user_name, queue_status, queue_name, queue_id, caller_id',
        $group = null
    ) {
        return (new DatabaseSIP('cdrs_full'))->select($where, $order, $limit, $fields, $group);
    }

    public static function getResumoByFilter($dataInicio, $dataFim, $filas)
    {
        $periodo = 'start_stamp BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59"';

        $where = $periodo;

        if (!in_array('todas', $filas)) {
            $filasSql = array_map(fn($f) => '"' . addslashes($f) . '"', $filas);
            $where .= ' AND queue_name IN (' . implode(',', $filasSql) . ')';
        }

        return self::getLigacoes(
            $where,
            null,
            null,
            '
        COUNT(*) as total,
        SUM(CASE WHEN queue_status = "answered" THEN 1 ELSE 0 END) as atendidas,
        SUM(CASE WHEN queue_status != "answered" THEN 1 ELSE 0 END) as perdidas,
        SUM(queue_waiting_duration) as totalTempoFila,
        SUM(CASE WHEN queue_status = "answered" THEN queue_call_duration ELSE 0 END) as totalTempoAtendimento,
        SUM(CASE WHEN queue_waiting_duration <= 45 THEN 1 ELSE 0 END) as dentroMeta
        '
        )->fetchObject();
    }

    public static function getFilas()
    {
        return self::getLigacoes(
            null,
            null,
            null,
            'queue_name, queue_id',
            'queue_id, queue_name'
        );
    }

    public static function getLigacoesByFilter($dataInicio, $dataFim, $fila)
    {
        $dataInicio = $dataInicio . ' 00:00:00';
        $dataFim = $dataFim . ' 23:59:59';

        $where = 'cdr.start_stamp BETWEEN "' . $dataInicio . '" AND "' . $dataFim . '" 
          AND cdr.queue_name = "' . $fila . '"';

        $fields = '
        cdr.id,
        cdr.uuid,
        cdr.start_stamp,
        cdr.queue_waiting_duration,
        cdr.queue_call_duration,
        cdr.queue_user_name,
        cdr.queue_status,
        cdr.queue_name,
        cdr.queue_id,
        cdr.caller_id,
        COALESCE(ivr_uuid.digit, ivr_transfer.digit) AS digit
    ';

        $join = '
        LEFT JOIN ivr_log ivr_uuid
            ON ivr_uuid.uuid = cdr.uuid
            AND ivr_uuid.id_ivr = 15

        LEFT JOIN ivr_log ivr_transfer
            ON ivr_transfer.uuid = cdr.transfer_dst_uuid_out
            AND ivr_transfer.id_ivr = 15
    ';

        return (new DatabaseSIP('cdrs_full cdr'))
            ->select($where, null, null, $fields, null, $join);
    }
}