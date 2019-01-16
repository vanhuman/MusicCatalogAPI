<?php

namespace Handlers;

use Helpers\DatabaseConnection;
use Models\User;

class UsersHandler extends DatabaseConnection
{

    /**
     * @return User | null
     * @throws \Exception
     */
    public function getUserById(int $userId)
    {
        $query = 'SELECT * FROM user WHERE id = ' . $userId;
        $result = $this->db->query($query);
        if ($result->rowCount() === 0) {
            return null;
        }
        $userData = $result->fetch();
        return $this->createModelFromDatabaseData($userData);
    }

    /**
     * @throws \Exception
     * @return User | null
     */
    public function getUserByCredentials(string $username)
    {
        $query = 'SELECT * FROM user WHERE username = "' . $username . '"';
        $result = $this->db->query($query);
        if ($result->rowCount() === 0) {
            return null;
        }
        $userData = $result->fetch();
        return $this->createModelFromDatabaseData($userData);
    }

    /**
     * @return User
     */
    private function createModelFromDatabaseData(array $userData)
    {
        return new User([
            'id' => $userData['id'],
            'username' => $userData['username'],
            'password' => $userData['password'],
        ]);
    }
}