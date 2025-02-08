<?php
require __DIR__ . '/includes/app.php';

use \App\http\Router;

$obRouter = new Router(URL);


include __DIR__ . '/routes/pages.php';
include __DIR__ . '/routes/api.php';
include __DIR__ . '/routes/eventos.php';
include __DIR__ . '/routes/usuario.php';
include __DIR__ . '/routes/ajax.php';
include __DIR__ . '/routes/graficos.php';

//IMPRIME O RESPONSE DA PÁGINA
$obRouter->run()->sendResponse();

?>