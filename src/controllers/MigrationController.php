<?php

namespace Controllers;

use Psr\Container\ContainerInterface;
use \Slim\Http\Request;
use \Slim\Http\Response;
use Handlers\MigrationHandler;

class MigrationController extends BaseController
{
    /**
     * @var MigrationHandler $migrationHandler
     */
    protected $migrationHandler;

    public function __construct(ContainerInterface $container)
    {
        $this->initController($container);
        $this->migrationHandler = new MigrationHandler($this->container->get('db'));
    }

    /**
     * Migration actions 1.
     * Actions prior to migrating artists and labels.
     * @return Response
     */
    public function migrationPre(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $this->migrationHandler->migrationPre();
        } catch (\Exception $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
        return $response->withJson('Done', 200);
    }

    /**
     * Migration actions 2.
     * Migrating artists.
     * @return Response
     */
    public function migrateArtists(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        $numRecs = $this->migrationHandler->migrateArtists();
        if ($numRecs instanceof \Exception) {
            return $response->withJson($numRecs, 500);
        }
        return $response->withJson(['number of records' => $numRecs], 200);
    }

    /**
     * Migration actions 3.
     * Migrating labels.
     * @return Response
     */
    public function migrateLabels(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        $numRecs = $this->migrationHandler->migrateLabels();
        if ($numRecs instanceof \Exception) {
            return $response->withJson($numRecs, 500);
        }
        return $response->withJson(['number of records' => $numRecs], 200);
    }

    /**
     * Migration actions 4.
     * Closing actions, to rename artists and label fields in albums.
     * @return Response
     */
    public function migrationPost(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $this->migrationHandler->migrationPost();
        } catch (\Exception $e) {
            return $response->withJson($e->getMessage(), $e->getCode());
        }
        return $response->withJson('Done', 200);
    }
}