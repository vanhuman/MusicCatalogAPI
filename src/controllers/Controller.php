<?php

namespace Controllers;

use \Slim\Http\Response;

class Controller
{
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