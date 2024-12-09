<?php
require __DIR__ . '/includes/app.php';

use \App\http\Router;

$obRouter = new Router(URL);


include __DIR__ . '/routes/pages.php';
include __DIR__ . '/routes/admin.php';
//IMPRIME O RESPONSE DA PÁGINA
$obRouter->run()->sendResponse();

?>