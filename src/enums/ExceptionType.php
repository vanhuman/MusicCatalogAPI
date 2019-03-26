<?php

namespace Enums;

use MabeEnum\Enum;

/**
 * Class ExceptionType
 * @package Enums
 * @method static ExceptionType SYS_EXCEPTION()
 * @method static ExceptionType AUTH_EXCEPTION()
 * @method static ExceptionType DB_EXCEPTION()
 * @method static ExceptionType VALIDATION_EXCEPTION()
 */
class ExceptionType extends Enum
{
    const SYS_EXCEPTION = [1, 'System error'];
    const AUTH_EXCEPTION = [2, 'Autorisation error'];
    const DB_EXCEPTION = [3, 'Database error'];
    const VALIDATION_EXCEPTION = [4, 'Validation error'];
}