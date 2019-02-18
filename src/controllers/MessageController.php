<?php

namespace Controllers;

use Slim\Http\Response;

class MessageController
{
    /**
     * @return Response
     */
    public function showError(Response $response, \Exception $exception)
    {
        $code = $exception->getCode();
        try {
            $response->withStatus($code);
        } catch (\Exception $e) {
            $code = 500;
        }
        $reference = explode('/', $exception->getFile());
        $reference = explode('.', end($reference));
        $reference = current($reference);
        $returnedError = [
            'message' => $exception->getMessage(),
            'reference' => $reference,
            'status' => $exception->getCode(),
        ];
        return $response->withJson($returnedError, $code);
    }

    /**
     * @return Response
     */
    public function showMessage(Response $response, string $message)
    {
        $returnedMessage = [
            'message' => $message,
            'status' => 200,
        ];
        return $response->withJson($returnedMessage, 200);
    }
}