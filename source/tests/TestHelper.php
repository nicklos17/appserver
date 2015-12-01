<?php
use Phalcon\DI,
    Phalcon\DI\FactoryDefault;

ini_set('display_errors', 1);
error_reporting(E_ALL);

define('ROOT_PATH', __DIR__);

set_include_path(
    ROOT_PATH . PATH_SEPARATOR . get_include_path()
);

// required for phalcon/incubator
include __DIR__ . "/vendor/autoload.php";
// use the application autoloader to autoload the classes
// autoload the dependencies found in composer
$loader = new Phalcon\Loader();
$loader->registerDirs(array(
    ROOT_PATH
));
$loader->registerNamespaces(array(
    'Phalcon' => __DIR__ . '/incubator_phalcon/Library/Phalcon',
    'Appserver\v1\Controllers' => __DIR__ . '/../apps/api/v0.1/controllers/',
    'Appserver\v2\Controllers' => __DIR__ . '/../apps/api/v0.2/controllers/',
));
$loader->register();