<?php

namespace Handlers;

use Helpers\DatabaseConnection;
use Models\User;

class UsersHandler extends DatabaseConnection
{

    /**
     * @param int $userId
     * @return User
     * @throws \Exception
     */
    public function getUserById(int $userId)
    {
        $query = 'SELECT * FROM user WHERE id = ' . $userId;
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        if ($result->rowCount() === 0) {
            throw new \Exception('User with id ' . $userId . ' not found.', 404);
        }
        $userData = $result->fetch();
        $user = $this->createModelFromDatabaseData($userData);
        return $user;
    }

    /**
     * @param string $username
     * @throws \Exception
     * @return User
     */
    public function getUserByCredentials(string $username)
    {
        $query = 'SELECT * FROM user WHERE username = "' . $username . '"';
        try {
            $result = $this->db->query($query);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage(), 500);
        };
        if ($result->rowCount() === 0) {
            throw new \Exception('User with username ' . $username . ' not found.', 404);
        }
        $userData = $result->fetch();
        $user = $this->createModelFromDatabaseData($userData);
        return $user;
    }

    /**
     * @param array $userData
     * @return User
     */
    private function createModelFromDatabaseData(array $userData)
    {
        $newUser = new User([
            'id' => $userData['id'],
            'username' => $userData['username'],
            'password' => $userData['password'],
        ]);
        return $newUser;
    }
}