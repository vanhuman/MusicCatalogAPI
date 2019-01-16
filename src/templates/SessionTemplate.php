<?php

namespace Templates;

use Models\Session;

class SessionTemplate
{
    /**
     * @var Session $session
     */
    protected $session;

    public function __construct(Session $session)
    {
        $this->session = $session;
    }

    /**
     * @return array|null
     */
    public function getArray(bool $includeWrapper = true)
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