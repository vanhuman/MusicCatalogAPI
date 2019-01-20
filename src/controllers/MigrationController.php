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
     * @return Response
     */
    public function migrationPhase1(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $this->migrationHandler->migration_1_First();
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $numAlbumsArtists = $this->migrationHandler->migration_2_Artists();
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $numAlbumsLabels = $this->migrationHandler->migration_3_Labels();
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $this->migrationHandler->migration_4_AfterArtistAndLabel();
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }

        return $response->withJson([
            'number of albums for artists migration' => $numAlbumsArtists,
            'number of albums for labels migration' => $numAlbumsLabels,
        ], 200);
    }

}