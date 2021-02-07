<?php

namespace Helpers;

use Controllers\AuthenticationController;
use Controllers\MessageController;
use Enums\DependencyType;
use Psr\Container\ContainerInterface;

class ContainerHelper
{
    public static function init(ContainerInterface $container): void
    {
        $container['messageController'] = function ($container) {
            $messageController = new MessageController();
            return $messageController;
        };
        $container['authenticationController'] = function ($container) {
            $authenticationController = new AuthenticationController($container);
            return $authenticationController;
        };
        $container['databaseConnection'] = function ($container) {
            $databaseConnection = new DatabaseConnection();
            return $databaseConnection;
        };
    }

    /**
     * @param ContainerInterface $container
     * @param string $dependency
     * @param DependencyType|null $type
     * @return mixed
     */
    public static function get(ContainerInterface $container, string $dependency, DependencyType $type = null)
    {
        if (empty($type)) {
            $type = DependencyType::HANDLERS();
        }
        if (!$container->has($dependency)) {
            $container[$dependency] = function ($container) use ($dependency, $type) {
                $class = '\\' . $type->getValue() . '\\' . ucfirst($dependency);
                return new $class($container);
            };
        }
        return $container->get($dependency);
    }
}
