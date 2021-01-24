<?php

namespace Templates;

use Models\User;

class UserTemplate implements TemplateInterface
{
    /**
     * @var User $user
     */
    protected $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * @return array|null
     */
    public function getArray(bool $includeWrapper = true)
    {
        if (isset($this->user)) {
            $user = [
                'id' => $this->user->getId(),
                'username' => $this->user->getUsername(),
                'admin' => $this->user->getAdmin(),
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