<?php

namespace Controllers;

use Handlers\GenresHandler;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;
use Templates\GenresTemplate;
use Templates\GenreTemplate;

class GenresController extends Controller
{
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->handler = new GenresHandler($this->container->get('db'));
    }

    protected function newTemplate($genres)
    {
        if (is_array($genres)) {
            return new GenresTemplate($genres);
        } else {
            return new GenreTemplate($genres);
        }
    }

    public function postGenre(Request $request, Response $response, $args)
    {
        $body = $request->getParsedBody();
        try {
            $genre = $this->handler->insertGenre($body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $genreTemplate = new GenreTemplate($genre);
        $response = $response->withJson($genreTemplate->getArray(), 200);
        return $response;
    }

    public function putGenre(Request $request, Response $response, $args)
    {
        $id = $args['id'];
        $body = $request->getParsedBody();
        try {
            $genre = $this->handler->updateGenre($id, $body);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $genreTemplate = new GenreTemplate($genre);
        $response = $response->withJson($genreTemplate->getArray(), 200);
        return $response;
    }
}