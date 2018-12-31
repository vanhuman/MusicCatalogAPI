<?php

namespace Controllers;

use Handlers\ArtistsHandler;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Templates\ArtistsTemplate;
use Templates\ArtistTemplate;

class ArtistsController extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->handler = new ArtistsHandler($this->container->get('db'));
    }

    protected function newTemplate($artists)
    {
        if (is_array($artists)) {
            return new ArtistsTemplate($artists);
        } else {
            return new ArtistTemplate($artists);
        }
    }

    public function postArtist(Request $request, Response $response, $args)
    {
        $body = $request->getParsedBody();
        try {
            $artist = $this->handler->insertArtist($body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $artistTemplate = new ArtistTemplate($artist);
        $response = $response->withJson($artistTemplate->getArray(), 200);
        return $response;
    }

    public function putArtist(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $body = $request->getParsedBody();
        try {
            $artist = $this->handler->updateArtist($id, $body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $artistTemplate = new ArtistTemplate($artist);
        $response = $response->withJson($artistTemplate->getArray(), 200);
        return $response;
    }
}