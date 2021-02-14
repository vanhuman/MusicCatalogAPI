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
    public function migration(Request $request, Response $response, array $args)
    {
        $migration = array_key_exists('migration', $args) ? $args['migration'] : null;
        try {
            $this->login($request);
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        try {
            $this->migrationHandler->migration($migration);
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        return $response->withJson([
            'Result' => 'OK'
        ], 200);
    }
}
