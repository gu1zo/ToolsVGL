<?php

namespace App\Utils;

use \App\Utils\View;

class Alert
{
    /**
     * Método responsável por retornar o alrta de sucesso
     * @param string $message
     * @return string
     */
    public static function getSuccess($message)
    {
        return View::render('/alert/status', [
            'tipo' => 'success',
            'mensagem' => $message

        ]);
    }

    public static function getError($message)
    {
        return View::render('/alert/status', [
            'tipo' => 'danger',
            'mensagem' => $message

        ]);
    }
}