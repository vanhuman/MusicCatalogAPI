<?php

namespace Enums;

use MabeEnum\Enum;

/**
 * Class LoggingType
 * @package Enums
 * @method static LoggingType AUTHENTICATION()
 * @method static LoggingType QUERY()
 * @method static LoggingType ERROR()
 */

class LoggingType extends Enum
{
    const AUTHENTICATION = 'Authentication';
    const QUERY = 'Query';
    const ERROR = 'Error';
}
