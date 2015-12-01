<?php
 $loader = new Phalcon\Loader();
 $loader->registerNamespaces(array(
   'Qiniu' => __DIR__,
 ));
 $loader->register();

require_once 'functions.php';