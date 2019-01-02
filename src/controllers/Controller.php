<?php

namespace Controllers;

use Handlers\DatabaseHandler;
use Psr\Container\ContainerInterface;
use \Slim\Http\Response;
use Slim\Http\Request;
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
        $response = $response->withJson($template->getArray(), 200);
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
        return [
            'id' => array_key_exists('id', $args) ? $args['id'] : null,
            'sortBy' => $request->getParam('sortby'),
            'sortDirection' => $request->getParam('sortdirection'),
            'filter' => [
                'artist_id' => $request->getParam('artist_id'),
                'label_id' => $request->getParam('label_id'),
                'genre_id' => $request->getParam('genre_id'),
                'format_id' => $request->getParam('format_id'),
            ]
        ];
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