<?php

namespace Controllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

use Handlers\AlbumsHandler;
use Models\Album;
use Templates\AlbumTemplate;
use Templates\AlbumsTemplate;

class AlbumsController extends Controller
{
    /**
     * AlbumsController constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->handler = new AlbumsHandler($this->container->get('db'));
        $this->messageController = new MessageController();
    }

    /**
     * For GET requests to the albums endpoint
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function get(Request $request, Response $response, $args)
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
     * @param Request $request
     * @param Response $response
     * @param $args
     * @return Response
     */
    public function getAlbumsSortedOnRelatedTable(Request $request, Response $response, $args)
    {
        $params = $this->collectGetParams($request, $args);
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