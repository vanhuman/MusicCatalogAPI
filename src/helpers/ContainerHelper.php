<?php

namespace Helpers;

use PDO;
use Psr\Container\ContainerInterface;

class ContainerHelper
{
    public static function init(ContainerInterface $container): void
    {
        $container['db'] = function ($c) {
            $db = $c['settings']['db'];
            $options = [
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ];
            $pdo = new PDO(
                'mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'],
                $db['user'],
                $db['pass'],
                $options
            );
            return $pdo;
        };
    }
}
