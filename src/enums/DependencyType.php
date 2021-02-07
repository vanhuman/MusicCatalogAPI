<?php

namespace Enums;

use MabeEnum\Enum;

/**
 * Class DependencyType
 * @package Enums
 * @method static DependencyType HANDLERS()
 * @method static DependencyType CONTROLLERS()
 * @method static DependencyType HELPERS()
 */
class DependencyType extends Enum
{
    const HANDLERS = 'Handlers';
    const CONTROLLERS = 'Controllers';
    const HELPERS = 'Helpers';
}
