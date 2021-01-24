<?php

/* @var array $settings */

require 'vendor/autoload.php';
include 'settings.php';

use Slim\App;

use Helpers\ContainerHelper;
use Helpers\Routes;

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;
$config['db']['host'] = 'localhost';

$env = getenv('DEV_ENVIRONMENT') === 'development' ? 'development' : 'production';
$settings = $settings[$env];

$config['db']['user'] = $settings['dbuser'];
$config['db']['pass'] = $settings['dbpassword'];
$config['db']['dbname'] = $settings['dbname'];
$config['showDebug'] = $settings['showdebug'];
$config['showParams'] = $settings['showparams'];

$app = new App(['settings' => $config]);
$container = $app->getContainer();

try {
    ContainerHelper::init($container);
    Routes::init($app);
    $app->run();
} catch (\Exception $ex) {
    echo 'Something went terribly wrong. O-o...';
}
