<?php
require __DIR__ . '/includes/app.php';

use \App\http\Router;

$obRouter = new Router(URL);


include __DIR__ . '/routes/pages.php';
include __DIR__ . '/routes/usuario.php';
include __DIR__ . '/routes/agendados.php';
include __DIR__ . '/routes/ajax.php';
include __DIR__ . '/routes/perdidas.php';
include __DIR__ . '/routes/fila.php';
include __DIR__ . '/routes/apps.php';


//IMPRIME O RESPONSE DA PÁGINA
$obRouter->run()->sendResponse();

?>