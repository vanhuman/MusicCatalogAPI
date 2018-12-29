<?php

require 'vendor/autoload.php';

use Handlers\ContainerHandler;
use Controllers\AlbumsController;
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

$app->get('/albums/{albumId}', AlbumsController::class . ':getAlbum');
$app->get('/albums', AlbumsController::class . ':getAlbums');

$app->post('/albums', AlbumsController::class . ':postAlbum');
$app->put('/albums/{albumId}', AlbumsController::class . ':putAlbum');
$app->delete('/albums/{albumId}', AlbumsController::class . ':deleteAlbum');

$app->get('/migrationPre', MigrationController::class . ':migrationPre');
$app->get('/migrateArtists', MigrationController::class . ':migrateArtists');
$app->get('/migrateLabels', MigrationController::class . ':migrateLabels');
$app->get('/migrationPost', MigrationController::class . ':migrationPost');

try {
    $app->run();
} catch (\Exception $ex) {
    echo 'Something went terribly wrong. O-o...';
}
