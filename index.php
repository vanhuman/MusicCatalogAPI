<?php

require 'vendor/autoload.php';

use Slim\App;

use Helpers\ContainerHelper;
use Helpers\Routes;

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host'] = 'localhost';

$config['db']['user'] = 'user';
$config['db']['pass'] = 'password';
$config['db']['dbname'] = 'music_catalog';
$config['showDebug'] = true;
$config['showParams'] = true;

//$config['db']['user'] = 'deb55474_musiccatalog';
//$config['db']['pass'] = 'ankerput';
//$config['db']['dbname'] = 'deb55474_musiccatalog';
//$config['showDebug'] = false;
//$config['showParams'] = false;

$app = new App(['settings' => $config]);
$container = $app->getContainer();

try {
    ContainerHelper::init($container);
    Routes::init($app);
    $app->run();
} catch (\Exception $ex) {
    echo 'Something went terribly wrong. O-o...';
}
