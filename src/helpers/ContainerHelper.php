<?php

namespace Helpers;

use Psr\Container\ContainerInterface;

class ContainerHelper
{
    /**
     * Initialize the container by adding a PDO object.
     * @param ContainerInterface $container
     */
    public static function init($container)
    {
        $container['db'] = function ($c) {
            $db = $c['settings']['db'];
            $pdo = new \PDO('mysql:host=' . $db['host'] . ';dbname=' . $db['dbname'],
                $db['user'], $db['pass']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            return $pdo;
        };
    }
}