<?php

namespace Controllers;

use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Handlers\AlbumsHandler;
use Templates\AlbumsTemplate;
use Templates\AlbumTemplate;

class AlbumsController extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->handler = new AlbumsHandler($this->container->get('db'));
    }

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

    public function getAlbumsSortedOnRelatedTable(Request $request, Response $response, $args)
    {
        $sortBy = $request->getParam('sortby');
        $sortDirection = $request->getParam('sortdirection');
        try {
            $records = $this->handler->getAlbumsSortedOnRelatedTable($sortBy, $sortDirection);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $template = new AlbumsTemplate($records);
        $response = $response->withJson($template->getArray(), 200);
        return $response;
    }

    public function postAlbum(Request $request, Response $response, $args)
    {
        $body = $request->getParsedBody();
        try {
            $album = $this->handler->insertAlbum($body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        std()->show($album);
        $albumTemplate = new AlbumTemplate($album);
        $response = $response->withJson($albumTemplate->getArray(), 200);
        return $response;
    }

    public function putAlbum(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $body = $request->getParsedBody();
        try {
            $album = $this->handler->updateAlbum($id, $body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $albumTemplate = new AlbumTemplate($album);
        $response = $response->withJson($albumTemplate->getArray(), 200);
        return $response;
    }

    protected function newTemplate($albums)
    {
        if (is_array($albums)) {
            return new AlbumsTemplate($albums);
        } else {
            return new AlbumTemplate($albums);
        }
    }
}