<?php

namespace Appserver\v1;
use Phalcon\Loader;
use Phalcon\Mvc\ModuleDefinitionInterface;
use Phalcon\Db\Adapter\Pdo\Mysql;

class Module implements ModuleDefinitionInterface
{

    /**
     * Registers the module auto-loader
     */
    public function registerAutoloaders(\Phalcon\DiInterface $di = NULL)
    {

        $loader = new Loader();

        $loader->registerNamespaces(array(
            'Appserver\v1\Controllers' => __DIR__ . '/controllers/',
        ));

        $loader->register();
    }

    /**
     * Registers the module-only services
     *
     * @param Phalcon\DI $di
     */
    public function registerServices(\Phalcon\DiInterface $di = NULL)
    {

        /**
         * Read configuration
         */

        /**
         * Database connection is created based in the parameters defined in the configuration file
         */

        $di->set('dispatcher', function()
        {
            $dispatcher = new \Phalcon\Mvc\Dispatcher();
            $dispatcher->setDefaultNamespace("Appserver\\v1\\Controllers");
            return $dispatcher;
        });

        /**
         * Setting up the view component
         */

        $di->set('cookies', function()
        {
            $cookies = new \Phalcon\Http\Response\Cookies();
            $cookies->useEncryption(false);
            return $cookies;
        });
    }

}
