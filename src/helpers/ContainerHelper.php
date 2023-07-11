<?php

namespace Helpers;

use Controllers\AuthenticationController;
use Controllers\MessageController;
use Enums\DependencyType;
use Handlers\LoggingHandler;
use Psr\Container\ContainerInterface;

class ContainerHelper
{
    public static function init(ContainerInterface $container): void
    {
        $container['databaseConnection'] = function ($container) {
            $databaseConnection = new DatabaseConnection();
            return $databaseConnection;
        };
        $container['loggingHandler'] = function ($container) {
            $loggingHandler = new LoggingHandler($container);
            return $loggingHandler;
        };
        $container['authenticationController'] = function ($container) {
            $authenticationController = new AuthenticationController($container);
            return $authenticationController;
        };
        $container['messageController'] = function ($container) {
            $messageController = new MessageController($container);
            return $messageController;
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
