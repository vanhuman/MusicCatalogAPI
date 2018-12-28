<?php

namespace Controllers;

use Psr\Container\ContainerInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use Handlers\MigrationHandler;

class MigrationController
{
    protected $container;
    protected $albumsHandler;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->migrationHandler = new MigrationHandler($this->container->get('db'));
    }

    public function migrationPre(Request $request, Response $response, $args)
    {
        try {
            $this->migrationHandler->migrationPre();
        } catch (\Exception $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
        return $response->withJson('Done', 200);
    }

    public function migrateArtists(Request $request, Response $response, $args)
    {
        $numRecs = $this->migrationHandler->migrateArtists();
        if ($numRecs instanceof \Exception) {
            return $response->withJson($numRecs, 500);
        }
        return $response->withJson(['number of records' => $numRecs], 200);
    }

    public function migrateLabels(Request $request, Response $response, $args)
    {
        $numRecs = $this->migrationHandler->migrateLabels();
        if ($numRecs instanceof \Exception) {
            return $response->withJson($numRecs, 500);
        }
        return $response->withJson(['number of records' => $numRecs], 200);
    }

    public function migrationPost(Request $request, Response $response, $args)
    {
        try {
            $this->migrationHandler->migrationPost();
        } catch (\Exception $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
        return $response->withJson('Done', 200);
    }

}