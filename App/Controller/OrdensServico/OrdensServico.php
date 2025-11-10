<?php
namespace App\Controller\OrdensServico;

use \App\Utils\View;
use \App\Utils\Alert;
use \App\Controller\Pages\Page;
use \App\Model\Entity\Tecnicos as EntityTecnicos;
use \App\Model\Entity\OrdensServico as EntityOrdensServico;
use \App\Model\Entity\EquipamentosOrdensServico as EntityEquipamentosOrdensServico;
use \App\Model\Entity\ImagensOrdensServico as EntityImagensOrdensServico;
use \App\Model\Entity\Avaliacoes as EntityAvaliacao;
use App\Model\Rest\APIInt6;
use DateTime;

class OrdensServico extends Page
{
    public static function getOs($request)
    {
        $content = View::render('/ordem-servico/form', [
            'status' => self::getStatus($request)
        ]);

        return parent::getPage('Notas > ToolsVGL', $content);
    }
    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'nenhuma':
                return Alert::getError('Nenhuma Ordem de Serviço encontrada com os filtros!');
            case 'no-permission':
                return Alert::getError('Você não tem permissão');
            case 'already':
                return Alert::getError('OS já avaliada!');
            case 'evaluated':
                return Alert::getSuccess('OS avaliada com sucesso!');
        }
        return '';
    }

    public static function getOsTable($request)
    {
        $queryParams = $request->getQueryParams();
        $uri = http_build_query($queryParams);
        $content = View::render('/ordem-servico/table', [
            'status' => self::getStatus($request),
            'itens' => self::getTableItens($request),
            'URI' => $uri
        ]);

        return parent::getPage('Notas > ToolsVGL', $content);
    }


    public static function getTableItens($request)
    {
        $queryParams = $request->getQueryParams();
        $dataInicio = $queryParams['data_inicial'];
        $dataFim = $queryParams['data_final'];
        $tecnico = $queryParams['tecnico'];
        $resultados = EntityOrdensServico::getOsByFilter($dataInicio, $dataFim, $tecnico);
        $uri = str_replace("/table", "/delete", $_SERVER['REQUEST_URI']);

        $itens = '';
        while ($obOrdens = $resultados->fetchObject(EntityOrdensServico::class)) {
            $data = (new DateTime($obOrdens->data))->format('d/m/Y H:i');


            $imagens = EntityImagensOrdensServico::getTotalImagesByIdOs($obOrdens->id) > 0 ? '✔️' : '❌';
            $equipamentos = EntityEquipamentosOrdensServico::getTotalEquipimanetosByIdOs($obOrdens->id) > 0 ? '✔️' : '❌';

            $confirmacao = $obOrdens->confirmacao == 0 ? '❌' : '✔️';


            $itens .= View::render('/ordem-servico/item', [
                'id' => $obOrdens->id,
                'numero' => $obOrdens->numero,
                'data' => $data,
                'nome_tecnico' => $obOrdens->nome_tecnico,
                'cliente' => $obOrdens->cliente,
                'tipo' => $obOrdens->tipo,
                'imagens' => $imagens,
                'equipamentos' => $equipamentos,
                'confirmacao' => $confirmacao,
                'URI' => $uri
            ]);
        }
        return $itens;
    }
    public static function getOsDetails($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];

        $obOs = EntityOrdensServico::getOrdemServicoById($id);
        if (!$obOs instanceof EntityOrdensServico) {
            $request->getRouter()->redirect('/os/table');
            exit;
        }

        $data = new DateTime($obOs->data);

        $content = View::render('ordem-servico/detail/details', [
            'status' => self::getStatus($request),
            'id' => $obOs->numero,
            'idOrdem' => $obOs->id,
            'data' => $data->format('d/m/Y H:i'),
            'tecnico' => $obOs->nome_tecnico,
            'id_tecnico' => $obOs->id_tecnico,
            'tipo_os' => $obOs->tipo,
            'cliente' => $obOs->cliente,
            'obs' => $obOs->obs,
            'materiais' => self::getMateriaisById($id),
            'imagens' => self::getImagensById($id),
            'pppoe' => $obOs->pppoe,
            'tipo_fechamento' => $obOs->tipo_fechamento,
            'solicitado' => $obOs->solicitado,
            'plano' => $obOs->plano,
            'tempo' => $obOs->tempo,
            'onu' => '<div class="text-muted">Carregando dados da ONU...</div>',
            'roteador' => '<div class="text-muted">Carregando dados do roteador...</div>',
        ]);

        return parent::getPage('Detalhe OS > ToolsVGL', $content);
    }


    private static function getMateriaisById($id)
    {
        $itens = '';
        $resultados = EntityEquipamentosOrdensServico::getEquipamentosOrdemServicoByIdOs($id);

        while ($obMateriais = $resultados->fetchObject(EntityEquipamentosOrdensServico::class)) {
            $itens .= View::render('ordem-servico/detail/item', [
                'item' => $obMateriais->item,
                'qtd' => $obMateriais->qtd,
            ]);
        }

        return $itens;
    }

    private static function getImagensById($id)
    {
        $itens = '';
        $resultados = EntityImagensOrdensServico::getImagemOrdemServicoByIdOs($id);

        while ($obImagens = $resultados->fetchObject(EntityImagensOrdensServico::class)) {
            $descricao = $obImagens->descricao == '' ? 'Sem descrição' : $obImagens->descricao;

            $itens .= View::render('ordem-servico/detail/imagem', [
                'url' => $obImagens->url,
                'descricao' => $descricao,
            ]);
        }

        return $itens;
    }

    public static function getRoteador($pppoe)
    {
        $obRoteador = APIInt6::getRouterStatus($pppoe);

        return View::render('ordem-servico/detail/router', [
            'modelo' => $obRoteador['modelo'],
            'firmware' => $obRoteador['firmware'],
            'wifi24' => $obRoteador['wifi24'],
            'wifi5' => $obRoteador['wifi5'],
            'dns' => $obRoteador['dns'],
        ]);
    }

    public static function getOnu($pppoe)
    {
        $obONU = APIInt6::getOnu($pppoe);

        return View::render('ordem-servico/detail/onu', [
            'modelo' => $obONU['modelo'],
            'firmware' => $obONU['firmware'],
            'sinal' => $obONU['sinal'],
            'ponlink' => $obONU['ponlink']
        ]);
    }

    public static function setAvaliacao($request)
    {
        $postVars = $request->getPostVars();
        $idOs = $postVars['id'];
        $obOrdem = EntityOrdensServico::getOrdemServicoById($idOs);
        if (!$obOrdem instanceof EntityOrdensServico) {
            $request->getRouter()->redirect('/os/table');
            exit;
        }

        $obAvaliacao = EntityAvaliacao::getAvaliacoesByIdOS($idOs);

        if ($obAvaliacao instanceof EntityAvaliacao) {
            $request->getRouter()->redirect('/os/?id=' . $idOs . '&status=already');
            exit;
        }
        $obAvaliacao = new EntityAvaliacao();
        $obAvaliacao->idOs = $idOs;
        $obAvaliacao->idTecnico = $postVars['idTecnico'];
        $obAvaliacao->nota = $postVars['nota'];
        $obAvaliacao->descricao = $postVars['obs'];
        $obAvaliacao->cadastrar();

        $obOrdem->confirmacao = 1;
        $obOrdem->atualizar();


        $request->getRouter()->redirect('/os/?id=' . $idOs . '&status=evaluated');
        exit;

    }

}