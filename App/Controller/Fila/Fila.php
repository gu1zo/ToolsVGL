<?php
namespace App\Controller\Fila;

use \App\Controller\Pages\Page;
use \App\Utils\View;
use \App\Utils\Alert;
use \App\Session\Login\Login;
use \App\Model\Entity\Fila as EntityFila;
use \App\Model\Entity\User as EntityUser;

class Fila extends Page
{
    public static function getFila($request)
    {
        $content = View::render('fila/table', [
            'id' => Login::getId()
        ]);

        return parent::getPage('Fila SZ.chat > ToolsVGL', $content);
    }

    public static function getFilaGestor($request)
    {
        $content = View::render('fila/table-gestor', [
            'itens' => self::getFiltaItems(),
            'status' => self::getStatus($request)
        ]);

        return parent::getPage('Fila SZ.chat > ToolsVGL', $content);
    }

    private static function getFiltaItems()
    {
        $itens = '';

        //TOTAL DE REGISTROS
        $quantidadetotal = EntityFila::getFila(null, null, null, 'COUNT(*) as qtd')->fetchObject()->qtd;

        if ($quantidadetotal <= 0) {
            $itens = '';
        }

        $results = EntityFila::getFila(null, 'posicao ASC');

        while ($obFila = $results->fetchObject(EntityFila::class)) {
            $obUser = EntityUser::getUserById($obFila->id_usuario);
            $motivo = $obFila->motivo == null ? '' : $obFila->motivo;
            $data_pausa = $obFila->data_pausa == null ? '' : $obFila->data_pausa;
            $itens .= View::render('/fila/item', [
                'id_usuario' => $obFila->id_usuario,
                'usuario' => $obUser->nome,
                'posicao' => $obFila->posicao,
                'entrada' => $obFila->data_entrada,
                'motivo_pausa' => $motivo,
                'hora_pausa' => $data_pausa
            ]);
        }
        return $itens;
    }

    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'no-permission':
                return Alert::getError('Você não tem permissão');
                break;
            case 'deleted':
                return Alert::getSuccess('Usuário excluido com sucesso!');
                break;
        }
        return '';
    }

    public static function deleteFila($request)
    {
        $queryParams = $request->getQueryParams();
        $id_usuario = $queryParams['id'];

        $obFila = EntityFila::getFilaById($id_usuario);
        if ($obFila instanceof EntityFila) {

            $results = EntityFila::getFila('posicao > "' . $obFila->posicao . '"');

            while ($row = $results->fetchObject(EntityFila::class)) {
                $row->posicao = $row->posicao - 1;
                $row->atualizar();
            }

            $obFila->excluir();
        }
        $request->getRouter()->redirect('/fila/gestao?status=deleted');
        exit;
    }
}