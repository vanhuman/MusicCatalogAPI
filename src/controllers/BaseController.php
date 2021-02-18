<?php

namespace Controllers;

use Enums\ExceptionType;
use Enums\LoggingType;
use Handlers\LoggingHandler;
use Models\McException;
use Psr\Container\ContainerInterface;
use Slim\Http\Request;

use Models\AuthParams;

abstract class BaseController
{
    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @var MessageController $messageController
     */
    protected $messageController;

    /**
     * @var AuthenticationController $authController
     */
    protected $authController;

    /**
     * @var LoggingHandler $loggingHandler
     */
    protected $loggingHandler;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->messageController = $container->get('messageController');
        $this->authController = $container->get('authenticationController');
        $this->loggingHandler = $container->get('loggingHandler');
    }

    protected function logQuery($query): void
    {
        $this->loggingHandler->insert([
            'type' => LoggingType::QUERY(),
            'user_id' => $this->authController->getUser() ? $this->authController->getUser()->getId() : null,
            'data' => $query,
        ]);
    }

    /**
     * @throws \Exception
     */
    protected function login(Request $request)
    {
        $token = $request->getParam('token');
        if (!isset($token)) {
            throw new McException('Token is required.', 403, ExceptionType::AUTH_EXCEPTION());
        }
        $this->authController->login(new AuthParams([
            'token' => $token,
            'method' => $request->getMethod(),
        ]));
    }
}
