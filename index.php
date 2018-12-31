<?php

require 'vendor/autoload.php';

use Handlers\ContainerHandler;
use Controllers\AlbumsController;
use Controllers\ArtistsController;
use Controllers\LabelsController;
use Controllers\GenresController;
use Controllers\FormatsController;
use Controllers\MigrationController;

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host'] = 'localhost';
$config['db']['user'] = 'user';
$config['db']['pass'] = 'password';
$config['db']['dbname'] = 'media_manager';

$app = new \Slim\App(['settings' => $config]);

$container = $app->getContainer();

ContainerHandler::init($container);

foreach ([
             '/albums' => AlbumsController::class,
             '/artists' => ArtistsController::class,
             '/labels' => LabelsController::class,
             '/formats' => FormatsController::class,
             '/genres' => GenresController::class
         ] as $route => $controller) {
    $app->get($route . '/{id}', $controller . ':get');
    $app->get($route, $controller . ':get');
    $app->post($route, $controller . ':postAlbum');
    $app->put($route . '/{id}', $controller . ':putAlbum');
    $app->delete($route . '/{id}', $controller . ':delete');
}

/* migration routes */
$app->get('/migrationPre', MigrationController::class . ':migrationPre');
$app->get('/migrateArtists', MigrationController::class . ':migrateArtists');
$app->get('/migrateLabels', MigrationController::class . ':migrateLabels');
$app->get('/migrationPost', MigrationController::class . ':migrationPost');

try {
    $app->run();
} catch (\Exception $ex) {
    echo 'Something went terribly wrong. O-o...';
}
