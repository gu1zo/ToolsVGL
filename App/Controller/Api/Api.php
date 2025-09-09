<?php

namespace App\Controller\Api;

use App\http\Request;
use App\Model\Entity\Notas as EntityNotas;
use App\Model\Entity\NotasCordialidade as EntityNotasCordialidade;
use DateTime;
use Exception;

class Api
{
    public static function setNota($request)
    {
        $postVars = $request->getPostVars();

        try {
            $protocolo = $postVars['protocolo'] ?? throw new Exception('Protocolo não definido');
            $nota = $postVars['nota'] ?? throw new Exception('Nota não definida');
            $equipe = $postVars['equipe'] ?? throw new Exception('Equipe não definida');
            $mensagem = $postVars['mensagem'] ?? throw new Exception('Mensagem não definida');
            $agente = $postVars['agente'] ?? throw new Exception('Agente não definido');
            $canal = $postVars['canal'] ?? throw new Exception('Canal não definido');

            $obNotas = EntityNotas::getNotasByProtocolo($protocolo);

            if ($obNotas instanceof EntityNotas) {
                throw new Exception('Protocolo já cadastrado');
            }

            $obNotas = new EntityNotas;
            $obNotas->protocolo = $protocolo;
            $obNotas->nota = $nota;
            $obNotas->equipe = $equipe;
            $obNotas->mensagem = $mensagem;
            $obNotas->agente = $agente;
            $obNotas->canal = $canal;

            $data = new DateTime('America/Sao_Paulo');
            $obNotas->data = $data->format('Y-m-d H:i');

            if (!$obNotas->cadastrar()) {
                throw new Exception('Erro ao cadastrar');
            }
            $data = [
                'error' => false,
                'message' => 'Cadastrado com sucesso'
            ];

        } catch (Exception $e) {
            $data = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public static function setNotaCordialidade($request)
    {
        $postVars = $request->getPostVars();

        try {
            $protocolo = $postVars['protocolo'] ?? throw new Exception('Protocolo não definido');
            $nota = $postVars['nota'] ?? throw new Exception('Nota não definida');
            $equipe = $postVars['equipe'] ?? throw new Exception('Equipe não definida');
            $agente = $postVars['agente'] ?? throw new Exception('Agente não definido');
            $canal = $postVars['canal'] ?? throw new Exception('Canal não definido');

            $obNotas = EntityNotasCordialidade::getNotasByProtocolo($protocolo);

            if ($obNotas instanceof EntityNotasCordialidade) {
                throw new Exception('Protocolo já cadastrado');
            }

            $obNotas = new EntityNotasCordialidade;
            $obNotas->protocolo = $protocolo;
            $obNotas->nota = $nota;
            $obNotas->equipe = $equipe;
            $obNotas->agente = $agente;
            $obNotas->canal = $canal;

            $data = new DateTime('America/Sao_Paulo');
            $obNotas->data = $data->format('Y-m-d H:i');

            if (!$obNotas->cadastrar()) {
                throw new Exception('Erro ao cadastrar');
            }
            $data = [
                'error' => false,
                'message' => 'Cadastrado com sucesso'
            ];

        } catch (Exception $e) {
            $data = [
                'error' => true,
                'message' => $e->getMessage()
            ];
        }
        return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}