<?php

namespace App\Utils;
use DateTime;
class DateManipulation
{

    public static function gethourDiff($hora1, $horaFinal)
    {
        date_default_timezone_set('America/Sao_Paulo');
        $horaInicial = new DateTime($hora1);

        $diferenca = $horaInicial->diff($horaFinal);

        return $diferenca->h . " horas " . $diferenca->i . " min";
    }
}