<?php

namespace Helpers;

use Controllers\AuthenticationController;
use Controllers\CleanupController;
use Controllers\HelpController;
use Slim\App;
use Controllers\AlbumsController;
use Controllers\ArtistsController;
use Controllers\LabelsController;
use Controllers\GenresController;
use Controllers\FormatsController;
use Controllers\MigrationController;

class Routes
{
    public static function init(App $app): void
    {
        $routes = [
            '/albums' => AlbumsController::class,
            '/artists' => ArtistsController::class,
            '/labels' => LabelsController::class,
            '/formats' => FormatsController::class,
            '/genres' => GenresController::class,
        ];
        foreach ($routes as $route => $controller) {
            $app->get($route . '/help', HelpController::class . ':getHelp');
            $app->get($route . '/{id}', $controller . ':getById');
            $app->get($route, $controller . ':get');
            $app->post($route, $controller . ':post');
            $app->put($route . '/{id}', $controller . ':put');
            if ($route !== '/albums') {
                $app->delete($route . '/remove_orphans', $controller . ':removeOrphans');
            }
            $app->delete($route . '/{id}', $controller . ':delete');
        }

        /* authentication */
        $app->post('/authenticate', AuthenticationController::class . ':authenticate');

        /* migration */
        $app->post('/migration/{migration}', MigrationController::class . ':migration');

        /* images cleanup route */
        $app->get('/cleanup_images', CleanupController::class . ':cleanupImages');
    }
}
