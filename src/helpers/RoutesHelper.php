<?php

namespace Helpers;

use Slim\App;
use Controllers\AlbumsController;
use Controllers\ArtistsController;
use Controllers\LabelsController;
use Controllers\GenresController;
use Controllers\FormatsController;
use Controllers\MigrationController;

class RoutesHelper
{
    /**
     * @param App $app
     */
    public static function init($app)
    {
        /* album routes */
        $app->get('/albums/{id}', AlbumsController::class . ':get');
        $app->get('/albums', AlbumsController::class . ':get');
        $app->post('/albums', AlbumsController::class . ':postAlbum');
        $app->put('/albums/{id}', AlbumsController::class . ':putAlbum');
        $app->delete('/albums/{id}', AlbumsController::class . ':delete');

        /* artist routes */
        $app->get('/artists/{id}', ArtistsController::class . ':get');
        $app->get('/artists', ArtistsController::class . ':get');
        $app->post('/artists', ArtistsController::class . ':postArtist');
        $app->put('/artists/{id}', ArtistsController::class . ':putArtist');
        $app->delete('/artists/{id}', ArtistsController::class . ':delete');

        /* label routes */
        $app->get('/labels/{id}', LabelsController::class . ':get');
        $app->get('/labels', LabelsController::class . ':get');
        $app->post('/labels', LabelsController::class . ':postLabel');
        $app->put('/labels/{id}', LabelsController::class . ':putLabel');
        $app->delete('/labels/{id}', LabelsController::class . ':delete');

        /* format routes */
        $app->get('/formats/{id}', FormatsController::class . ':get');
        $app->get('/formats', FormatsController::class . ':get');
        $app->post('/formats', FormatsController::class . ':postFormat');
        $app->put('/formats/{id}', FormatsController::class . ':putFormat');
        $app->delete('/formats/{id}', FormatsController::class . ':delete');

        /* genre routes */
        $app->get('/genres/{id}', GenresController::class . ':get');
        $app->get('/genres', GenresController::class . ':get');
        $app->post('/genres', GenresController::class . ':postGenre');
        $app->put('/genres/{id}', GenresController::class . ':putGenre');
        $app->delete('/genres/{id}', GenresController::class . ':delete');

        /* migration routes */
        $app->get('/migrationPre', MigrationController::class . ':migrationPre');
        $app->get('/migrateArtists', MigrationController::class . ':migrateArtists');
        $app->get('/migrateLabels', MigrationController::class . ':migrateLabels');
        $app->get('/migrationPost', MigrationController::class . ':migrationPost');
    }
}