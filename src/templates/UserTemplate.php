<?php

namespace Templates;

use Models\User;

class UserTemplate implements TemplateInterface
{
    /**
     * @var User $user
     */
    protected $user;

    /**
     * UserTemplate constructor.
     * @param User $user
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * @param bool $includeWrapper
     * @return array|null
     */
    public function getArray($includeWrapper = true)
    {
        if (isset($this->user)) {
            $user = [
                'id' => $this->user->getId(),
                'username' => $this->user->getUsername(),
            ];
        } else {
            $user = null;
        }
        if ($includeWrapper) {
            $user = [
                'user' => $user
            ];
        }
        return $user;
    }
}