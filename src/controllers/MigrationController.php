<?php

namespace Controllers;

use Exception;
use Helpers\ContainerHelper;
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
        parent::__construct($container);
        $this->migrationHandler = ContainerHelper::get($container, 'migrationHandler');
    }

    /**
     * @return Response
     */
    public function migrationPhase1(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $this->migrationHandler->migration_1_First();
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $numAlbumsArtists = $this->migrationHandler->migration_2_Artists();
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $numAlbumsLabels = $this->migrationHandler->migration_3_Labels();
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $this->migrationHandler->migration_4_AfterArtistAndLabel();
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }

        return $response->withJson([
            'number of albums for artists migration' => $numAlbumsArtists,
            'number of albums for labels migration' => $numAlbumsLabels,
        ], 200);
    }

    /**
     * @return Response
     */
    public function migrationPhase2(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $this->migrationHandler->migration_5_ImageLocation();
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        return $response->withJson([
            'Result' => 'OK'
        ], 200);
    }

    /**
     * @return Response
     */
    public function migrationAddSalt(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $this->migrationHandler->migration_6_add_salt();
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        return $response->withJson([
            'Result' => 'OK'
        ], 200);
    }

}
