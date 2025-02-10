<?php

namespace App\Utils;

class StringVerify
{
    public static function verificaTermoProibido($nome, $termos)
    {
        foreach ($termos as $termo) {
            if (strpos($nome, $termo) !== false) {
                return true;
            }
        }
        return false;
    }
}