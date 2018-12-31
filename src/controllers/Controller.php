<?php

namespace Controllers;

use Handlers\DatabaseHandler;
use Psr\Container\ContainerInterface;
use \Slim\Http\Response;
use Slim\Http\Request;

class Controller
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var DatabaseHandler $handler
     */
    protected $handler;

    public function delete(Request $request, Response $response, $args)
    {
        if ($request->getUri()->getPath()) {
            // get the base of the URI ('albums', 'artists', etc)
            $table = explode('/', $request->getUri()->getPath())[0];
            // remove the last s since we want singular entity names
            $table = rtrim($table, 's');
        } else {
            return $this->showError($response, 'ERROR: No route path found.', 500);
        }
        $id = $args['id'];
        try {
            $this->handler->delete($table, $id);
        } catch (\Exception $e) {
            return $this->showError($response, $e->getMessage(), $e->getCode());
        }
        $result = ucfirst($table) . ' with id ' . $id . ' deleted.';
        $response = $response->withJson($result, 200);
        return $response;
    }

    /**
     * @param Response $response
     * @param string $errorMessage
     * @return Response
     */
    protected function showError($response, $errorMessage, $status)
    {
        $response = $response->withJson($errorMessage, $status);
        return $response;
    }
}