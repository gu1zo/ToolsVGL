<?php
namespace App\Controller\Ligacoes;

use \App\Utils\View;
use DateTime;

class Ligacoes
{
    public static function render($request)
    {
        return View::render('ligacoes/dashboard');
    }
}