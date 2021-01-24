<?php

namespace Models;

use Enums\ExceptionType;

class McException extends \Exception
{
    /**
     * @var ExceptionType
     */
    public $exceptionType;

    public function __construct(string $message = "", int $code = 0, ExceptionType $exceptionType = null)
    {
        parent::__construct($message, $code);
        $this->exceptionType = $exceptionType;
    }
}