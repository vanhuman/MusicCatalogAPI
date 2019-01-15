<?php

namespace Templates;

use Models\Session;

class SessionTemplate
{
    /**
     * @var Session $session
     */
    protected $session;

    /**
     * SessionTemplate constructor.
     * @param Session $session
     */
    public function __construct($session)
    {
        $this->session = $session;
    }

    /**
     * @param bool $includeWrapper
     * @return array|null
     */
    public function getArray($includeWrapper = true)
    {
        if (isset($this->session)) {
            $session = [
                'id' => $this->session->getId(),
                'user_id' => $this->session->getUserId(),
                'token' => $this->session->getToken(),
                'time_out' => $this->session->getTimeOut(),
            ];
        } else {
            $session = null;
        }
        if ($includeWrapper) {
            $session = [
                'session' => $session
            ];
        }
        return $session;
    }
}