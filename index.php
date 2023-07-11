<?php

/* @var array $settings */

require 'vendor/autoload.php';
include 'settings.php';

use Slim\App;
use Helpers\ContainerHelper;
use Helpers\Routes;

$env = getenv('DEV_ENVIRONMENT') === 'development' ? 'development' : 'production';
$settings = $settings[$env];
$config['showDebug'] = $settings['showdebug'];
$config['showParams'] = $settings['showparams'];
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new App(['settings' => $config]);
$container = $app->getContainer();

// Generate salt and hashed password
//$salt = \Helpers\SecurityUtility::generateToken();
//$hash = \Helpers\SecurityUtility::hash('wachtwoord', $salt);
//std()->show([$salt, $hash]);

try {
    ContainerHelper::init($container);
    Routes::init($app);
    $app->run();
} catch (Exception $ex) {
    echo 'Something went terribly wrong. O-o...';
}
