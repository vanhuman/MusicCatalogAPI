<?php

namespace Controllers;

use Exception;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;
use Slim\Http\Response;

use Handlers\DatabaseHandler;
use Templates\HelpTemplate;

class HelpController extends RestController
{
    /**
     * @var DatabaseHandler $handler
     */
    protected $handler;

    public function __construct(ContainerInterface $container)
    {
        $this->initController($container);
    }

    protected function newTemplate($models) {
        return $models;
    }

    public function getHelp(Request $request, Response $response, array $args)
    {
        try {
            $this->login($request);
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        if ($request->getUri()->getPath()) {
            // get the base of the URI ('albums', 'artists', etc)
            $table = explode('/', $request->getUri()->getPath())[0];
        } else {
            return $this->messageController->showError($response,
                new Exception(
                    'Unable to determine the table to get help for.',
                    500
                )
            );
        }
        $classname = '\Handlers\\'.ucfirst($table).'Handler';
        $property = 'FIELDS';
        $fields = $classname::$$property;
        $helpTemplate = new HelpTemplate($table, $fields);
        return $response->withJson($helpTemplate->getArray(), 200);
    }
}