<?php

namespace App\Controller\Apps;

use \App\Controller\Pages\Page;
use \App\Utils\View;
use \App\Utils\Alert;
use \App\Model\Entity\Apps as EntityApp;

class Apps extends Page
{

    /**
     * Método responsável por retornar o conteúdo (view) da home
     * @return string
     */
    public static function getApps($request)
    {
        //Retorna a view da pagina
        $content = View::render('/apps/apps', [
            'itens' => self::getAppsItem(),
            'status' => self::getStatus($request)
        ]);
        return parent::getPage('Apps > ToolsVGL', $content);
    }

    private static function getAppsItem()
    {
        $itens = '';

        $results = EntityApp::getApps(null, 'id DESC');

        while ($obApp = $results->fetchObject(EntityApp::class)) {
            $itens .= View::render('/apps/item', [
                'link' => $obApp->link,
                'img' => $obApp->img,
                'title' => $obApp->titulo,
                'id' => $obApp->id
            ]);
        }


        return $itens;
    }

    public static function getNewApp($request)
    {
        $content = View::render('/apps/form', [
            'title' => 'Novo App'
        ]);

        return parent::getPage('Novo App > ToolsVGL', $content);
    }


    /**
     * Métido responsável por cadastrar um novo usuário
     * @param Request $request
     * @return never
     */
    public static function setNewApp($request)
    {

        $postVars = $request->getPostVars();

        $titulo = $postVars['titulo'] ?? '';
        $img = $postVars['img'] ?? '';
        $link = $postVars['link'] ?? '';

        $obApp = new EntityApp;
        $obApp->titulo = $titulo;
        $obApp->img = $img;
        $obApp->link = $link;

        $obApp->cadastrar();


        $request->getRouter()->redirect('/apps?status=created');
        exit;
    }

    public static function getDeleteApp($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];


        $obApp = EntityApp::getAppById($id);

        if (!$obApp instanceof EntityApp) {
            $request->getRouter()->redirect('apps');
            exit;
        }

        $content = View::render('apps/delete', [
            'titulo' => $obApp->titulo,
            'link' => $obApp->link
        ]);

        //Retorna a página
        return parent::getPage('Excluir App > ToolsVGL', $content);
    }


    /**
     * Método responsável por realizar a remoção do usuário do banco de Ddados
     * @param Request $request
     * @return never
     */
    public static function setDeleteApp($request)
    {
        $queryParams = $request->getQueryParams();
        $id = $queryParams['id'];
        $obApp = EntityApp::getAppById($id);

        if (!$obApp instanceof EntityApp) {
            $request->getRouter()->redirect('/app');
            exit;
        }
        $obApp->excluir();

        $request->getRouter()->redirect('/apps?status=deleted');
        exit;
    }

    private static function getStatus($request)
    {
        $queryParams = $request->getQueryParams();

        if (!isset($queryParams['status']))
            return '';

        switch ($queryParams['status']) {
            case 'created':
                return Alert::getSuccess('App criado com sucesso!');
            case 'deleted':
                return Alert::getSuccess('App excluido com sucesso!');
        }
        return '';
    }
}