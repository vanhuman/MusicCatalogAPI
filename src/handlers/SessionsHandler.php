<?php

namespace Handlers;

use Models\Session;
use PDO;
use Psr\Container\ContainerInterface;

class SessionsHandler
{

    /* @var PDO $db */
    protected $db;

    public function __construct(ContainerInterface $container)
    {
        $this->db = $container->get('databaseConnection')->getDatabase();
    }

    /**
     * @throws \Exception
     * @return Session | null
     */
    public function getSessionByToken(string $token)
    {
        $statement = $this->db->prepare('SELECT * FROM session WHERE `token` = :token');
        $statement->execute([':token' => $token]);
        if ($statement->rowCount() !== 0) {
            $session = $this->createModelFromDatabaseData($statement->fetch());
            if (!$session->isExpired()) {
                $this->updateSessionTimeout($session);
                return $session;
            } else {
                $statement = $this->db->prepare('DELETE FROM session WHERE `token` = :token');
                $statement->execute([':token' => $token]);
            }
        }
        return null;
    }

    /**
     * Look for existing valid session for this user, otherwise create one
     * @throws \Exception
     * @return Session | null
     */
    public function getSessionByUserId(int $userId)
    {
        // check if session for user exists and is still valid
        $query = 'SELECT * FROM session WHERE user_id = ' . $userId;
        $result = $this->db->query($query);
        if ($result->rowCount() !== 0) {
            $session = $this->createModelFromDatabaseData($result->fetch());
            if (!$session->isExpired()) {
                $this->updateSessionTimeout($session);
                return $session;
            } else {
                $query = 'DELETE FROM session WHERE id = ' . $session->getId();
                $this->db->query($query);
            }
        }
        // otherwise create session
        $session = $this->createSession($userId);
        return $session;
    }

    /**
     * @return Session
     */
    private function createModelFromDatabaseData(array $sessionData)
    {
        return new Session([
            'userId' => $sessionData['user_id'],
            'token' => $sessionData['token'],
            'id' => $sessionData['id'],
            'timeOut' => $sessionData['time_out'],
        ]);
    }

    /**
     * Regenerate timeout on session
     * @throws \Exception
     */
    private function updateSessionTimeout(Session $session)
    {
        $session->generateTimeOut();
        $query = 'UPDATE session SET time_out = ' . $session->getTimeOut() . ' WHERE id = ' . $session->getId();
        $this->db->query($query);
    }

    /**
     * @return Session | null
     * @throws \Exception
     */
    private function createSession(int $userId)
    {
        $session = new Session([
            'userId' => $userId,
        ]);
        $session->generateToken();
        $session->generateTimeOut();

        $query = 'INSERT INTO session (user_id, token, time_out)';
        $query .= ' VALUES (' . $userId . ', "' . $session->getToken() . '", ' . $session->getTimeOut() . ')';

        $this->db->query($query);
        $id = $this->db->lastInsertId();
        $session->setId($id);
        return $session;
    }
}
