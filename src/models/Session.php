<?php

namespace Models;

use Helpers\SecurityUtility;

class Session extends BaseModel
{

    /**
     * @var int $userId
     */
    private $userId;

    /**
     * @var string $token
     */
    private $token;

    /**
     * @var int $timeOut
     */
    private $timeOut;

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->userId;
    }

    /**
     * @param int $user_id
     */
    public function setUserId(int $userId): void
    {
        $this->userId = $userId;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    /**
     * @return int
     */
    public function getTimeOut(): int
    {
        return $this->timeOut;
    }

    /**
     * @param int $timeOut
     */
    public function setTimeOut(int $timeOut): void
    {
        $this->timeOut = $timeOut;
    }

    /**
     * @throws \Exception
     */
    public function generateToken(): void
    {
        $this->token = SecurityUtility::generateToken();
    }

    public function generateTimeOut(): void
    {
        $this->timeOut = SecurityUtility::generateTimeOut();
    }

    /**
     * Determines whether a client session has expired based on the timeout
     * @return boolean
     */
    public function isExpired()
    {
        return time() > $this->timeOut;
    }
}