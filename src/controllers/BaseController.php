<?php

namespace Controllers;

use Enums\ExceptionType;
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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->messageController = $container->get('messageController');
        $this->authController = $container->get('authenticationController');
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
