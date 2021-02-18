<?php

namespace Controllers;

use Enums\ExceptionType;
use Enums\LoggingType;
use Exception;
use Handlers\LoggingHandler;
use Models\McException;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;

class MessageController
{

    /**
     * @var LoggingHandler $loggingHandler
     */
    protected $loggingHandler;

    public function __construct(ContainerInterface $container)
    {
        $this->loggingHandler = $container->get('loggingHandler');
    }

    /**
     * @return Response
     */
    public function showError(Response $response, Exception $exception)
    {
        $code = $exception->getCode();
        try {
            $response->withStatus($code);
        } catch (Exception $e) {
            $code = 500;
        }
        $exception_type = ExceptionType::SYS_EXCEPTION;
        if ($exception instanceof McException) {
            $exception_type = [
                'id' => $exception->exceptionType->getValue()[0],
                'description' => $exception->exceptionType->getValue()[1],
            ];
        }
        $reference = explode('/', $exception->getFile());
        $reference = explode('.', end($reference));
        $reference = current($reference);
        $returnedError = [
            'message' => $exception->getMessage(),
            'reference' => $reference,
            'error_code' => $exception->getCode(),
            'error_type' => $exception_type,
        ];
        $this->loggingHandler->insert([
            'type' => $exception_type === ExceptionType::AUTH_EXCEPTION ? LoggingType::AUTHENTICATION : LoggingType::ERROR,
            'data' => $exception->getMessage(),
        ]);

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
