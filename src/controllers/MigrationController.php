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
}