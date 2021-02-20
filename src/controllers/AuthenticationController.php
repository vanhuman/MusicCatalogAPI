<?php

namespace Controllers;

use DateTime;
use Enums\ExceptionType;
use Enums\LoggingType;
use Exception;
use Handlers\LoggingHandler;
use Handlers\SessionsHandler;
use Helpers\ContainerHelper;
use Models\AuthParams;
use Models\Logging;
use Models\McException;
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
    private const REQUEST_METHODS_FOR_PUBLIC = ['GET'];
    private const TIMEOUT_BETWEEN_LOGIN_ATTEMPTS = 3;

    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
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
     * @var LoggingHandler $loggingHandler
     */
    protected $loggingHandler;

    /**
     * @var User $user
     */
    protected $user;

    /**
     * @var Session $session
     */
    protected $session;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->usersHandler = ContainerHelper::get($container, 'usersHandler');
        $this->sessionsHandler = ContainerHelper::get($container, 'sessionsHandler');
        $this->loggingHandler = $container->get('loggingHandler');
        $this->messageController = $container->get('messageController');
    }

    /**
     * @return Response
     */
    public function authenticate(Request $request, Response $response, array $args)
    {
        if ($this->assertTimeBetweenLoginAttemptsTooShort()) {
            return $this->messageController->showError(
                $response,
                new McException(
                    'Time between login attempts is too short. Please try again.',
                    401,
                    ExceptionType::AUTH_EXCEPTION()
                ),
                true
            );
        }
        $body = $request->getParsedBody();
        if (!array_key_exists('username', $body) || !array_key_exists('password', $body) || empty($body['username'])) {
            return $this->messageController->showError(
                $response,
                new McException(
                    'Username and password are mandatory to authenticate.',
                    401,
                    ExceptionType::AUTH_EXCEPTION()
                )
            );
        }
        $authParams = new AuthParams([
            'username' => $body['username'],
            'password' => $body['password'],
            'method' => 'POST',
        ]);
        try {
            $this->login($authParams, true);
        } catch (Exception $e) {
            return $this->messageController->showError($response, $e);
        }
        $sessionWithUser = [
            'session' => (new SessionTemplate($this->session))->getArray(false),
            'user' => (new UserTemplate($this->user))->getArray(false),
        ];
        try {
            $this->loggingHandler->insert([
                'type' => LoggingType::AUTHENTICATION(),
                'user_id' => $this->user->getId(),
                'ip_address' => $_SERVER['REMOTE_ADDR'],
                'data' => 'Logged in',
            ]);
        } catch (Exception $e) {}
        return $response->withJson($sessionWithUser, 200);
    }

    /**
     * @throws Exception
     */
    public function login(AuthParams $authParams, bool $methodOverride = false)
    {
        // first get a session
        if (isset($authParams->token)) {
            $this->session = $this->sessionsHandler->getSessionByToken($authParams->token);
            if (!isset($this->session)) {
                throw new McException(
                    'No valid session for user found. Token is invalid.',
                    401,
                    ExceptionType::AUTH_EXCEPTION()
                );
            }
            $this->user = $this->usersHandler->getUserById($this->session->getUserId());
            $this->container['user_id'] = $this->user->getId();
        } else {
            $this->user = $this->usersHandler->getUserByCredentials($authParams->username);
            if (!isset($this->user)) {
                throw new McException(
                    'User with username ' . $authParams->username . ' not found.',
                    401,
                    ExceptionType::AUTH_EXCEPTION()
                );
            }
            $this->container['user_id'] = $this->user->getId();
            if (!$this->user->passwordMatches($authParams->password)) {
                throw new McException(
                    'Password for user ' . $authParams->username . ' is not valid.',
                    401,
                    ExceptionType::AUTH_EXCEPTION()
                );
            }
            $this->session = $this->sessionsHandler->getSessionByUserId($this->user->getId());
            if (!isset($this->session)) {
                throw new McException(
                    'Session for user ' . $authParams->username . ' not found.',
                    401,
                    ExceptionType::AUTH_EXCEPTION()
                );
            }
        }
        // if we have a session, check for admin rights depending on the request method
        if (!isset($authParams->method) ||
            (
                !in_array($authParams->method, self::REQUEST_METHODS_FOR_PUBLIC)
                && !$methodOverride
                && !$this->user->getAdmin()
            )
        ) {
            throw new McException(
                'You have no rights to perform this action',
                401,
                ExceptionType::AUTH_EXCEPTION()
            );
        }
    }

    public function getUser(): User
    {
        return $this->user;
    }

    private function assertTimeBetweenLoginAttemptsTooShort(): bool
    {
        $loggings = $this->loggingHandler->selectByIP($_SERVER['REMOTE_ADDR']);
        $loggings = array_filter($loggings, function ($logging) {
           return $logging->getType() === LoggingType::AUTHENTICATION();
        });
        if (count($loggings) === 0) {
            return false;
        }
        usort($loggings, function(Logging $log1, Logging $log2) {
            return $log1->getDateCreated() < $log2->getDateCreated() ? 1 : -1;
        });
        $lastLoggingDate = $loggings[0]->getDateCreated();
        $now = new DateTime();
        return ($now->getTimestamp() - $lastLoggingDate->getTimestamp()) < self::TIMEOUT_BETWEEN_LOGIN_ATTEMPTS;
    }
}
