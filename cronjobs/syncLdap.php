<?php
namespace App\Cronjobs;
require __DIR__ . '/../includes/app.php';

use App\Controller\Ldap\Ldap;
use App\Model\Entity\User as EntityUser;
use DateTime;
use DateTimeZone;

$results = Ldap::getUsers();

foreach ($results as $ldap) {
    $obUsuario = EntityUser::getUserByLogin($ldap->login);
    if (!$obUsuario instanceof EntityUser) {
        $obUsuario = new EntityUser;
        $obUsuario->nome = $ldap->nome;
        $obUsuario->login = $ldap->login;
        $obUsuario->privilegio = 'normal';
        $obUsuario->cadastrar();
    } else {
        $obUsuario->nome = $ldap->nome;
        $obUsuario->login = $ldap->login;

        $obUsuario->atualizar();
    }
}

$data = new DateTime('now', new DateTimeZone('America/Sao_Paulo')); // Definir o fuso horário de Brasília
echo "Usuários sincornizados com o LDAP - " . $data->format('d/m/Y H:i') . "\n"; // Formatar para o padrão brasileiro