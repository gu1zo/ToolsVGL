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
include __DIR__ . '/routes/api.php';
include __DIR__ . '/routes/notas.php';
//include __DIR__ . '/routes/notas-cordialidade.php';
include __DIR__ . '/routes/emails.php';
include __DIR__ . '/routes/massivas.php';
include __DIR__ . '/routes/ordem-servico.php';


//IMPRIME O RESPONSE DA PÁGINA
$obRouter->run()->sendResponse();

?>