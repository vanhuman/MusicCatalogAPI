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

    protected function newTemplate($albums)
    {
        if (is_array($albums)) {
            return new AlbumsTemplate($albums);
        } else {
            return new AlbumTemplate($albums);
        }
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
}