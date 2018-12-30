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
    protected $container;
    protected $artistsHandler;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->artistsHandler = new ArtistsHandler($this->container->get('db'));
    }

    public function getArtist(Request $request, Response $response, $args)
    {
        $artistId = $args['artistId'];
        try {
            $artist = $this->artistsHandler->getArtist($artistId);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $artistTemplate = new ArtistTemplate($artist);
        $response = $response->withJson($artistTemplate->getArray(), 200);
        return $response;
    }

    public function getArtists(Request $request, Response $response, $args)
    {
        $sortBy = $request->getParam('sortBy');
        $sortDirection = $request->getParam('sortDirection');
        try {
            $artists = $this->artistsHandler->getArtists($sortBy, $sortDirection);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $artistsTemplate = new ArtistsTemplate($artists);
        $response = $response->withJson($artistsTemplate->getArray(), 200);
        return $response;
    }

    public function postArtist(Request $request, Response $response, $args)
    {
        $body = $request->getParsedBody();
        try {
            $artist = $this->artistsHandler->insertArtist($body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $artistTemplate = new ArtistTemplate($artist);
        $response = $response->withJson($artistTemplate->getArray(), 200);
        return $response;
    }

    public function putArtist(Request $request, Response $response, $args)
    {
        $artistId = $args['artistId'];
        $body = $request->getParsedBody();
        try {
            $artist = $this->artistsHandler->updateArtist($artistId, $body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $artistTemplate = new ArtistTemplate($artist);
        $response = $response->withJson($artistTemplate->getArray(), 200);
        return $response;
    }

    public function deleteArtist(Request $request, Response $response, $args)
    {
        $artistId = $args['artistId'];
        try {
            $result = $this->artistsHandler->deleteRecord('artist', $artistId);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $result = 'Artist with id ' . $artistId . ' deleted.';
        $response = $response->withJson($result, 200);
        return $response;
    }

}