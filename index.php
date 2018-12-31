<?php

require 'vendor/autoload.php';

use Handlers\ContainerHandler;
use Controllers\AlbumsController;
use Controllers\ArtistsController;
use Controllers\LabelsController;
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

/* album routes */
$app->get('/albums/{id}', AlbumsController::class . ':getAlbum');
$app->get('/albums', AlbumsController::class . ':getAlbums');
$app->post('/albums', AlbumsController::class . ':postAlbum');
$app->put('/albums/{id}', AlbumsController::class . ':putAlbum');
$app->delete('/albums/{id}', AlbumsController::class . ':delete');

/* artist routes */
$app->get('/artists/{id}', ArtistsController::class . ':getArtist');
$app->get('/artists', ArtistsController::class . ':getArtists');
$app->post('/artists', ArtistsController::class . ':postArtist');
$app->put('/artists/{id}', ArtistsController::class . ':putArtist');
$app->delete('/artists/{id}', ArtistsController::class . ':delete');

/* label routes */
$app->get('/labels/{id}', LabelsController::class . ':getLabel');
$app->get('/labels', LabelsController::class . ':getLabels');
$app->post('/labels', LabelsController::class . ':postLabel');
$app->put('/labels/{id}', LabelsController::class . ':putLabel');
$app->delete('/labels/{id}', LabelsController::class . ':delete');

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
