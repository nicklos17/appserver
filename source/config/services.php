<?php

/**
 * Services are globally registered in this file
 */

use Phalcon\Mvc\Router;
use Phalcon\Mvc\Url as UrlResolver;
use Phalcon\DI\FactoryDefault;
use Phalcon\Session\Adapter\Files as SessionAdapter;
/**
 * The FactoryDefault Dependency Injector automatically register the right services providing a full stack framework
 */
$di = new FactoryDefault();
/**
 * Registering a router
 */
$di['router'] = function() use($di)
{
    $router = new Router();

    $router->handle();
    $gateWay = new \Appserver\Utils\GateWay($di);

    if(!$gateWay->check($router->getControllerName(), $router->getActionName()))
    {
        echo json_encode(array('msg' => $gateWay->errMsg()));exit;
    }

    $router->setDefaultModule('api_' . $gateWay->ver);

    return $router;
};

/**
 * The URL component is used to generate all kind of urls in the application
 */
$di['url'] = function ()
{
    $url = new UrlResolver();
    $url->setBaseUri('/');

    return $url;
};
$di['view'] = function()
{
    $view = new Phalcon\Mvc\View();

    return $view;
};
/**
 * Start the session the first time some component request the session service
 */
$di['session'] = function()
{
    $session = new SessionAdapter();
    $session->start();

    return $session;
};

$sysConfig = include __DIR__ . '/../config/sysconfig.php';
$flagmsg = include __DIR__ . '/../config/flagmsg.php';
$dbConfig = include __DIR__ . '/../config/database.php';
$sdkConfig = include __DIR__ . '/../config/sdkConfig.php';
$verConfig = include __DIR__ . '/../config/verConfig.php';
$areaConfig = include __DIR__ . '/../config/areaConfig.php';
$weatherConfig = include __DIR__ . '/../config/weatherConfig.php';

$di['sysconfig'] = function () use ($sysConfig) 
{
    return $sysConfig;
};

$di['flagmsg'] = function () use ($flagmsg) 
{
    return $flagmsg;
};
$di['sdkconfig'] = function() use ($sdkConfig)
{
    return $sdkConfig;
};
$di['verConfig'] = function () use ($verConfig) 
{
    return $verConfig;
};
$di['areaConfig'] = function () use ($areaConfig) 
{
    return $areaConfig;
};
$di['weatherConfig'] = function () use ($weatherConfig) 
{
    return $weatherConfig;
};

$di['db'] = function () use ($dbConfig)
{
    return new Phalcon\Db\Adapter\Pdo\Mysql(array(
        "host" => $dbConfig->database->host,
        "username" => $dbConfig->database->username,
        "password" => $dbConfig->database->password,
        "dbname" => $dbConfig->database->dbname,

        "options" => array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
//            \PDO::ATTR_CASE => \PDO::CASE_LOWER,
            \PDO::ATTR_EMULATE_PREPARES => false,
            \PDO::ATTR_STRINGIFY_FETCHES => true,
        )
    ));
};