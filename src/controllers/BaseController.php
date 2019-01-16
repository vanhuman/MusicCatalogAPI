<?php

namespace Controllers;

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

    public function initController(ContainerInterface $container)
    {
        $this->container = $container;
        $this->messageController = new MessageController();
        $this->authController = new AuthenticationController($this->container);
    }

    /**
     * @throws \Exception
     */
    protected function login(Request $request)
    {
        $token = $request->getParam('token');
        if (!isset($token)) {
            throw new \Exception('Token is required.', 403);
        }
        $this->authController->login(new AuthParams([
            'token' => $token,
        ]));
    }
}