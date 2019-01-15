<?php

namespace Controllers;

use Handlers\SessionsHandler;
use Models\AuthParams;
use Models\Session;
use Models\User;
use Psr\Container\ContainerInterface;
use Slim\Http\Response;
use Slim\Http\Request;

use Handlers\UsersHandler;
use Templates\SessionTemplate;
use Templates\UserTemplate;

class AuthenticationController
{

    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * This generic handler is overridden for each separate controller subclass.
     * @var UsersHandler $usersHandler
     */
    protected $usersHandler;

    /**
     * @var SessionsHandler $sessionsHandler
     */
    protected $sessionsHandler;

    /**
     * @var MessageController $messageController
     */
    protected $messageController;

    /**
     * @var User $user
     */
    protected $user;

    /**
     * @var Session $session
     */
    protected $session;

    public function __construct($container)
    {
        $this->container = $container;
        $this->usersHandler = new UsersHandler($this->container->get('db'));
        $this->sessionsHandler = new SessionsHandler($this->container->get('db'));
        $this->messageController = new MessageController();
    }

    public function authenticate(Request $request, Response $response, $args)
    {
        $body = $request->getParsedBody();
        if (!array_key_exists('username', $body) || !array_key_exists('password', $body)) {
            return $this->messageController->showError(
                $response,
                new \Exception(
                    'Username and password are mandatory to authenticate',
                    404
                )
            );
        }
        $authParams = new AuthParams([
            'username' => $body['username'],
            'password' => $body['password'],
        ]);
        try {
            $this->login($authParams);
        } catch (\Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        $sessionWithUser = [
            'session' => (new SessionTemplate($this->session))->getArray(false),
            'user' => (new UserTemplate($this->user))->getArray(false),
        ];
        return $response->withJson($sessionWithUser, 200);
    }

    /**
     * @param AuthParams $authParams
     * @throws \Exception
     */
    public function login(AuthParams $authParams)
    {
        if (isset($authParams->token)) {
            $this->session = $this->sessionsHandler->getSessionByToken($authParams->token);
            $this->user = $this->usersHandler->getUserById($this->session->getUserId());
        } else {
            $this->user = $this->usersHandler->getUserByCredentials($authParams->username);
            if (!isset($this->user)) {
                throw new \Exception('User with username ' . $authParams->username . ' not found.', 404);
            }
            $this->session = $this->sessionsHandler->getSessionByUserId($this->user->getId());
            if (!isset($this->session)) {
                throw new \Exception('Session for user with username ' . $authParams->username . ' not found.', 404);
            }
        }
    }
}