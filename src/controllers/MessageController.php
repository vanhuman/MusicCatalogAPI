<?php

namespace Controllers;

use Slim\Http\Response;

class MessageController
{
    /**
     * Generic error messaging.
     * @param Response $response
     * @param \Exception $e
     * @return Response
     */
    public function showError(Response $response, \Exception $e)
    {
        $reference = explode('/', $e->getFile());
        $reference = explode('.', end($reference));
        $reference = current($reference);
        $returnedError = [
            'message' => $e->getMessage(),
            'type' => 'ERROR',
            'reference' => $reference,
            'status' => $e->getCode(),
        ];
        return $response->withJson($returnedError, $e->getCode());
    }

    /**
     * @param Response $response
     * @param string $message
     * @return Response
     */
    public function showMessage(Response $response, string $message)
    {
        $returnedMessage = [
            'message' => $message,
            'type' => 'INFORMATION',
        ];
        return $response->withJson($returnedMessage, 200);
    }
}