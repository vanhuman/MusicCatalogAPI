<?php

namespace Models;

class AuthParams
{
    /**
     * @var string
     */
    public $username;

    /**
     * @var string
     */
    public $password;

    /**
     * @var string
     */
    public $token;

    /**
     * @var string
     */
    public $method;

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