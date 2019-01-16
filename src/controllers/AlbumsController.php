<?php

namespace Controllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

use Handlers\AlbumsHandler;
use Models\Album;
use Templates\AlbumTemplate;
use Templates\AlbumsTemplate;

class AlbumsController extends RestController
{
    public function __construct(ContainerInterface $container)
    {
        $this->initController($container);
        $this->handler = new AlbumsHandler($this->container->get('db'));
    }

    /**
     * For GET requests to the albums endpoint
     * @return Response
     */
    public function get(Request $request, Response $response, array $args)
    {
        $id = array_key_exists('id', $args) ? $args['id'] : null;
        $sortBy = $request->getParam('sortby');
        if (!isset($id) && (in_array($sortBy, $this->handler::RELATED_SORT_FIELDS))) {
            return $this->getAlbumsSortedOnRelatedTable($request, $response, $args);
        } else {
            return parent::get($request, $response, $args);
        }
    }

    /**
     * For GET requests to the albums endpoint that use sorting on related tables.
     * @return Response
     */
    public function getAlbumsSortedOnRelatedTable(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        $params = $this->collectGetParams($request);
        try {
            $result = $this->handler->getAlbumsSortedOnRelatedTable($params);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        $template = new AlbumsTemplate($result['body']);
        $templateArray = $template->getArray();
        $returnObject = $this->getReturnObject($params, $result, $templateArray);
        return $response->withJson($returnObject, 200);
    }

    /**
     * @param Album | Album[] $albums
     * @return AlbumsTemplate | AlbumTemplate
     */
    protected function newTemplate($albums)
    {
        if (is_array($albums)) {
            return new AlbumsTemplate($albums);
        } else {
            return new AlbumTemplate($albums);
        }
    }
}