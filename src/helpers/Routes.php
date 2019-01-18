<?php

namespace Helpers;

use Controllers\AuthenticationController;
use Slim\App;
use Controllers\AlbumsController;
use Controllers\ArtistsController;
use Controllers\LabelsController;
use Controllers\GenresController;
use Controllers\FormatsController;
use Controllers\MigrationController;

class Routes
{
    /**
     * @param App $app
     */
    public static function init(App $app)
    {
        $routes = [
            '/albums' => AlbumsController::class,
            '/artists' => ArtistsController::class,
            '/labels' => LabelsController::class,
            '/formats' => FormatsController::class,
            '/genres' => GenresController::class,
        ];
        foreach ($routes as $route => $controller) {
            $app->get($route . '/{id}', $controller . ':getById');
            $app->get($route, $controller . ':get');
            $app->post($route, $controller . ':post');
            $app->put($route . '/{id}', $controller . ':put');
            $app->delete($route . '/{id}', $controller . ':delete');
        }

        /* authentication */
        $app->post('/authenticate', AuthenticationController::class . ':authenticate');

        /* migration routes */
        $app->get('/migration_phase1', MigrationController::class . ':migrationPhase1');
    }
}