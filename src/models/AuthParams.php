<?php

namespace Models;

class AuthParams
{
    /**
     * @var string $username
     */
    public $username;

    /**
     * @var string $password
     */
    public $password;

    /**
     * @var string $token
     */
    public $token;

    /**
     * AuthParams constructor.
     * @param array $args
     */
    public function __construct(array $args)
    {
        foreach ($args as $property => $value) {
            $this->{$property} = $value;
        }
    }
}