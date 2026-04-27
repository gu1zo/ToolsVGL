<?php
namespace App\Model\Entity;

use WilliamCosta\DatabaseManager\Database;

class Ligacoes
{
    public $id;
    public $data;
    public $tempo_fila;
    public $tempo_atendimento;
    public $responsavel;
    public $status;
    public $fila;
    public $numero;
    public $uuid;
    public $nota;
    public $id_queue;

    public static function getLigacoes($where = null, $order = null, $limit = null, $fields = '*', $group = null)
    {
        return (new Database('ligacoes'))->select($where, $order, $limit, $fields, $group);
    }


    public function cadastrar()
    {
        $this->id = (new Database('ligacoes'))->insert([
            'data' => $this->data,
            'tempo_fila' => $this->tempo_fila,
            'tempo_atendimento' => $this->tempo_atendimento,
            'responsavel' => $this->responsavel,
            'status' => $this->status,
            'fila' => $this->fila,
            'numero' => $this->numero,
            'uuid' => $this->uuid,
            'id_queue' => $this->id_queue,
            'nota' => $this->nota
        ]);

        return true;
    }

    public function atualizar()
    {
        return (new Database('ligacoes'))->update('id =' . $this->id, [
            'data' => $this->data,
            'tempo_fila' => $this->tempo_fila,
            'tempo_atendimento' => $this->tempo_atendimento,
            'responsavel' => $this->responsavel,
            'status' => $this->status,
            'fila' => $this->fila,
            'numero' => $this->numero,
            'uuid' => $this->uuid,
            'nota' => $this->nota
        ]);
    }
    public function excluir()
    {
        return (new Database('ligacoes'))->delete('id =' . $this->id);

    }

    public static function getLigacoesByUuid($uuid)
    {
        return self::getLigacoes('uuid ="' . $uuid . '"')->fetchObject(self::class);
    }

    public static function getLigacoesById($id)
    {
        return self::getLigacoes('id ="' . $id . '"')->fetchObject(self::class);
    }

    public static function getLigacoesByFilter($dataInicio, $dataFim, $fila)
    {
        $periodo = 'data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59"';

        if ($fila == 'todas') {
            return self::getLigacoes($periodo, null, null, '*');
        }
        return self::getLigacoes($periodo . ' AND fila = "' . $fila . '"', null, null, '*');
    }

    public static function getNotasByFilter($dataInicio, $dataFim, $filas = [])
    {
        $where = 'data BETWEEN "' . $dataInicio . ' 00:00:00" 
              AND "' . $dataFim . ' 23:59:59" 
              AND nota IS NOT NULL';

        if (!empty($filas) && $filas !== 'todas') {
            $ids = implode(',', array_map('intval', $filas));
            $where .= ' AND id_queue IN (' . $ids . ')';
        }

        return self::getLigacoes(
            $where,
            null,
            null,
            'nota, id_queue, responsavel, data, numero, fila'
        );
    }
    /*
    public static function getNotasByFilter($dataInicio, $dataFim, $fila)
    {
        $where = 'data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59" AND nota is not null';
        if ($fila == 'todas') {
            return self::getLigacoes($where, null, null, 'nota, fila, responsavel, data, numero');
        }

        return self::getLigacoes($where . ' AND fila = "' . $fila . '"', null, null, 'nota, fila, responsavel, data, numero');
    }*/

    public static function getNotasByAgente($agente, $dataInicio, $dataFim, $equipe)
    {
        $periodo = 'data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59"';

        if ($equipe == 'todas') {
            return self::getLigacoes($periodo . ' AND responsavel="' . $agente . '"');
        }

        return self::getLigacoes($periodo . ' AND fila = "' . $equipe . '" AND responsavel="' . $agente . '"');
    }

    public static function getAgentesByFilter($dataInicio, $dataFim, $equipe)
    {
        $periodo = 'data BETWEEN "' . $dataInicio . ' 00:00:00" AND "' . $dataFim . ' 23:59:59"';

        if ($equipe == 'todas') {
            return self::getLigacoes($periodo, null, null, '*', 'responsavel');
        }

        return self::getLigacoes($periodo . ' AND fila = "' . $equipe . '"', null, null, '*', 'responsavel');
    }

    public static function getNotasByEquipe($equipe)
    {
        if ($equipe == 'todas') {
            return self::getLigacoes();
        }

        return self::getLigacoes('fila = "' . $equipe . '"');
    }

    public static function getFilas($where = null, $order = null, $limit = null, $fields = 'fila', $group = 'fila')
    {
        return self::getLigacoes($where, $order, $limit, $fields, $group);
    }

    public static function getCardsData($dataInicio, $dataFim, $filas = [])
    {
        $where = 'data BETWEEN "' . $dataInicio . ' 00:00:00" 
              AND "' . $dataFim . ' 23:59:59" 
              AND nota IS NOT NULL';

        if (!empty($filas) && $filas !== 'todas') {
            $ids = implode(',', array_map('intval', $filas));
            $where .= ' AND id_queue IN (' . $ids . ')';
        }
        $where .= ' AND id_queue > 0';

        $fields = '
        COUNT(*) as total,
        SUM(CASE WHEN nota <= 2 THEN 1 ELSE 0 END) as detratores,
        SUM(CASE WHEN nota = 3 THEN 1 ELSE 0 END) as neutros,
        SUM(CASE WHEN nota >= 4 THEN 1 ELSE 0 END) as promotores,
        AVG(nota) as media
    ';

        return self::getLigacoes($where, null, 1, $fields)->fetchObject();
    }

    public static function getNotasDataTable($params)
    {
        $columns = [
            'id',
            'numero',
            'data',
            'nota',
            'responsavel',
            'id_queue'
        ];

        $start = (int) ($params['start'] ?? 0);
        $length = (int) ($params['length'] ?? 10);
        $search = $params['search']['value'] ?? '';

        $dataInicio = $params['data_inicial'] ?? date('Y-m-d');
        $dataFim = $params['data_final'] ?? date('Y-m-d');

        $filas = $params['filaLigacoes'] ?? [];

        if (!is_array($filas)) {
            $filas = [$filas];
        }

        // 🔥 sanitiza IDs válidos
        $ids = array_filter(
            array_map('intval', $filas),
            fn($v) => $v > 0
        );

        // 🔥 WHERE BASE (usado no TOTAL)
        $whereBase = 'data BETWEEN "' . $dataInicio . ' 00:00:00"
        AND "' . $dataFim . ' 23:59:59"
        AND nota IS NOT NULL
        AND id_queue > 0';

        // 🔥 WHERE COMPLETO (usado no filtered + dados)
        $where = $whereBase;

        // filtro por filas
        if (!empty($ids)) {
            $where .= ' AND id_queue IN (' . implode(',', $ids) . ')';
        }

        // busca
        if (!empty($search)) {
            $search = addslashes($search);
            $where .= ' AND (
            numero LIKE "%' . $search . '%" OR
            responsavel LIKE "%' . $search . '%"
        )';
        }

        // 📊 ordenação segura
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = strtoupper($params['order'][0]['dir'] ?? 'DESC');
        $orderDir = $orderDir === 'ASC' ? 'ASC' : 'DESC';

        $orderColumn = $columns[$orderColumnIndex] ?? 'data';
        $order = $orderColumn . ' ' . $orderDir;

        // 🔢 TOTAL (sem busca, sem filtro de fila)
        $total = (new Database('ligacoes'))->select(
            $whereBase,
            null,
            null,
            'COUNT(*) as total'
        )->fetchObject()->total;

        // 🔢 TOTAL FILTRADO (com tudo aplicado)
        $filtered = (new Database('ligacoes'))->select(
            $where,
            null,
            null,
            'COUNT(*) as total'
        )->fetchObject()->total;

        // 📦 DADOS PAGINADOS
        $results = (new Database('ligacoes'))->select(
            $where,
            $order,
            $start . ',' . $length,
            'id, numero, data, nota, responsavel, fila, id_queue'
        );

        $data = [];

        while ($row = $results->fetchObject(self::class)) {
            $data[] = [
                'id' => $row->id,
                'numero' => $row->numero,
                'data' => (new \DateTime($row->data))->format('d/m/Y H:i'),
                'nota' => $row->nota,
                'agente' => $row->responsavel,
                'fila' => $row->fila,
                'id_fila' => $row->id_queue
            ];
        }

        return [
            'draw' => (int) ($params['draw'] ?? 1),
            'recordsTotal' => (int) $total,
            'recordsFiltered' => (int) $filtered,
            'data' => $data
        ];
    }
    public static function getResumoByFilter($dataInicio, $dataFim, $filas = [])
    {
        // 📅 período
        $where = 'data BETWEEN "' . $dataInicio . ' 00:00:00" 
          AND "' . $dataFim . ' 23:59:59"';

        // 🔒 garante array
        if (!is_array($filas)) {
            $filas = [$filas];
        }

        // 🔥 sanitiza IDs válidos
        $ids = array_filter(
            array_map('intval', $filas),
            fn($v) => $v > 0
        );

        // 🎯 filtro por filas
        if (!empty($ids) && !in_array('todas', $filas)) {
            $where .= ' AND id_queue IN (' . implode(',', $ids) . ')';
        }

        // 🧠 agregações
        $fields = '
        COUNT(*) as total,

        SUM(CASE 
            WHEN status = "answered" THEN 1 
            ELSE 0 
        END) as atendidas,

        SUM(CASE 
            WHEN status = "abandoned" THEN 1 
            ELSE 0 
        END) as perdidas,

        SUM(COALESCE(tempo_fila, 0)) as totalTempoFila,

        SUM(CASE 
            WHEN status = "answered" THEN COALESCE(tempo_atendimento, 0)
            ELSE 0 
        END) as totalTempoAtendimento,

        SUM(CASE 
            WHEN tempo_fila <= 45 THEN 1 
            ELSE 0 
        END) as dentroMeta
    ';

        return self::getLigacoes(
            $where,
            null,
            1,
            $fields
        )->fetchObject();
    }

    public static function getLigacoesDataTable($params)
    {
        $columns = [
            'id',
            'data',
            'tempo_fila',
            'tempo_atendimento',
            'responsavel',
            'status',
            'numero',
            'fila'
        ];

        $start = (int) ($params['start'] ?? 0);
        $length = (int) ($params['length'] ?? 10);
        $search = $params['search']['value'] ?? '';

        $dataInicio = $params['data_inicial'] ?? date('Y-m-d');
        $dataFim = $params['data_final'] ?? date('Y-m-d');

        $filas = $params['filaLigacoes'] ?? [];

        if (!is_array($filas)) {
            $filas = [$filas];
        }

        // 🔒 sanitiza IDs válidos
        $ids = array_filter(
            array_map('intval', $filas),
            fn($v) => $v > 0
        );

        // 📅 WHERE base
        $whereBase = 'data BETWEEN "' . $dataInicio . ' 00:00:00"
        AND "' . $dataFim . ' 23:59:59"
        AND id_queue > 0';

        $where = $whereBase;

        // 🎯 filtro por filas
        if (!empty($ids)) {
            $where .= ' AND id_queue IN (' . implode(',', $ids) . ')';
        }

        // 🔍 busca
        if (!empty($search)) {
            $search = addslashes($search);
            $where .= ' AND (
            numero LIKE "%' . $search . '%" OR
            responsavel LIKE "%' . $search . '%" OR
            fila LIKE "%' . $search . '%"
        )';
        }

        // 📊 ordenação
        $orderColumnIndex = $params['order'][0]['column'] ?? 0;
        $orderDir = strtoupper($params['order'][0]['dir'] ?? 'DESC');
        $orderDir = $orderDir === 'ASC' ? 'ASC' : 'DESC';

        $orderColumn = $columns[$orderColumnIndex] ?? 'data';
        $order = $orderColumn . ' ' . $orderDir;

        // 🔢 TOTAL
        $total = (new Database('ligacoes'))->select(
            $whereBase,
            null,
            null,
            'COUNT(*) as total'
        )->fetchObject()->total;

        // 🔢 TOTAL FILTRADO
        $filtered = (new Database('ligacoes'))->select(
            $where,
            null,
            null,
            'COUNT(*) as total'
        )->fetchObject()->total;

        // 📦 DADOS
        $results = (new Database('ligacoes'))->select(
            $where,
            $order,
            $start . ',' . $length,
            'id, data, tempo_fila, tempo_atendimento, responsavel, status, numero, fila'
        );

        $data = [];

        while ($row = $results->fetchObject(self::class)) {
            $data[] = [
                'id' => $row->id,

                'data' => (new \DateTime($row->data))->format('d/m/Y H:i'),

                'tempo_espera' => gmdate("H:i:s", (int) $row->tempo_fila),

                'tempo_atendimento' => gmdate("H:i:s", (int) $row->tempo_atendimento),

                'agente' => $row->responsavel,

                'status' => $row->status === 'answered'
                    ? '<span class="badge bg-success">Atendida</span>'
                    : '<span class="badge bg-danger">Abandonada</span>',

                'numero' => $row->numero,

                'fila' => $row->fila
            ];
        }

        return [
            'draw' => (int) ($params['draw'] ?? 1),
            'recordsTotal' => (int) $total,
            'recordsFiltered' => (int) $filtered,
            'data' => $data
        ];
    }
}