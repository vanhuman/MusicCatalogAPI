<?php

namespace Controllers;

use Handlers\DatabaseHandler;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\Request;
use Templates\AlbumsTemplate;
use Templates\TemplateInterface;

abstract class Controller
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var DatabaseHandler $handler
     */
    protected $handler;

    /**
     * @var int $pageSize
     */
    protected $pageSize;

    abstract protected function newTemplate($models);

    public function get(Request $request, Response $response, $args)
    {
        $params = $this->collectParams($request, $args);
        try {
            $records = $this->handler->get($params);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        /* @var TemplateInterface $template */
        $template = $this->newTemplate($records);
        $returnObject = $this->buildReturnObject($params, $template);
        $response = $response->withJson($returnObject, 200);
        return $response;
    }

    public function delete(Request $request, Response $response, $args)
    {
        if ($request->getUri()->getPath()) {
            // get the base of the URI ('albums', 'artists', etc)
            $table = explode('/', $request->getUri()->getPath())[0];
            // remove the last s since we want singular entity names
            $table = rtrim($table, 's');
        } else {
            return $this->showError($response, 'ERROR: No route path found.', 500);
        }
        $id = $args['id'];
        try {
            $this->handler->delete($table, $id);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $result = ucfirst($table) . ' with id ' . $id . ' deleted.';
        $response = $response->withJson($result, 200);
        return $response;
    }

    protected function collectParams(Request $request, $args)
    {
        $page = $request->getParam('page');
        if (!isset($page) || !is_numeric($page)) {
            $page = 1;
        }
        $page = (int)$page;
        return [
            'id' => array_key_exists('id', $args) ? $args['id'] : null,
            'page' => $page,
            'page_size' => $this->pageSize,
            'sortby' => $request->getParam('sortby'),
            'sortdirection' => $request->getParam('sortdirection'),
            'filter' => [
                'artist_id' => $request->getParam('artist_id'),
                'label_id' => $request->getParam('label_id'),
                'genre_id' => $request->getParam('genre_id'),
                'format_id' => $request->getParam('format_id'),
            ]
        ];
    }

    /**
     * @param array $params
     * @param TemplateInterface $template
     * @return array
     */
    protected function buildReturnObject($params, $template)
    {
        $templateArray = $template->getArray();
        /*
         * in case of the albumsController, current($templateArray) is one album or an array with albums
         * and current(current($templateArray)) is a specific album id or the first album object, which is an array
        */
        if (sizeof(current($templateArray)) === 0) {
            // we were asking for a list of albums but there are none
            $numberOfRecords = 0;
        } else if (is_array(current(current($templateArray)))) {
            // we were asking for a list of albums and there is at least one
            $numberOfRecords = sizeof(current($templateArray));
        } else {
            // we were asking for one specific album
            $numberOfRecords = 1;
        }
        $returnArray['pagination'] = [
            'page' => (int)$params['page'],
            'number_of_records' => $numberOfRecords,
        ];
        $returnArray['parameters'] = [];
        if (isset($params['sortby'])) {
            $returnArray['parameters']['sortby'] = $params['sortby'];
        }
        if (isset($params['sortdirection'])) {
            $returnArray['parameters']['sortdirection'] = $params['sortdirection'];
        }
        foreach ($params['filter'] as $key => $value) {
            if (isset($params['filter'][$key])) {
               $returnArray['parameters'][$key] = $value;
            }
        }
        $returnArray = array_merge($returnArray, $template->getArray());
        return $returnArray;
    }

    protected function setPageSize()
    {
        if ($this->container->has('settings') && $this->container->get('settings')->has('pageSize')) {
            $this->pageSize = $this->container['settings']['pageSize'];
        } else {
            $this->pageSize = 50;
        }
    }

    /**
     * @param Response $response
     * @param string $errorMessage
     * @return Response
     */
    protected function showError($response, $errorMessage, $status)
    {
        $response = $response->withJson($errorMessage, $status);
        return $response;
    }
}