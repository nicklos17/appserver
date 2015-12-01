<?php

use Phalcon\DI\FactoryDefault\CLI as CliDI,
    Phalcon\CLI\Console as ConsoleApp;

define('CLI_PATH', realpath(dirname(__FILE__)));

$di = new CliDi();
$loader = new Phalcon\Loader();

$loader->registerDirs(array(CLI_PATH . '/tasks'));

$loader->registerNamespaces(array(
    'Appserver\Mdu\Modules' => __DIR__ . '/../mdu/',
    'Appserver\Mdu\Models' => __DIR__ . '/../mdu/models',
    'Appserver\Mdu\Modules' => __DIR__ . '/../mdu/',
    'Appserver\Utils' => __DIR__ . '/../utils',
    'Appserver\Utils\Extensive' => __DIR__ . '/../utils/extensive',
    'Appserver\Utils\MyValidator' => __DIR__ . '/../utils/validator',
    'Appserver\Utils\Tasks' => __DIR__ . '/../utils/tasks',
    'Appserver\Utils\Push' => __DIR__ . '/../utils/push',

));
$loader->register();

// Load the configuration file (if any)
if(is_readable(dirname(dirname(dirname(__FILE__)))) . '/config/sysconfig.php')
{
     $config = include dirname(dirname(dirname(__FILE__))) . '/config/sysconfig.php';
    $di['sysconfig'] = function () use ($config) 
    {
        return $config;
    };
}

$dbConfig = include __DIR__ . '/../../config/database.php';
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


//create a console application
$console = new ConsoleApp();
$console->setDI($di);

/**
* Process the console arguments
*/
$arguments = array();
foreach($argv as $k => $arg)
{
    if($k == 1)
        $arguments['task'] = $arg;
    elseif($k == 2)
        $arguments['action'] = $arg;
    elseif($k >= 3)
       $arguments['params'][] = $arg;
}

define('CURRENT_TASK', (isset($argv[1]) ? $argv[1] : null));
define('CURRENT_ACTION', (isset($argv[2]) ? $argv[2] : null));

$di->setShared('console', $console);

try{
    $console->handle($arguments);
}
catch(\Phalcon\Exception $e)
{
    echo date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']) . ':' . $e->getMessage();
    exit;
}