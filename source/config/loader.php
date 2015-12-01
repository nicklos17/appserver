<?php
$loader = new Phalcon\Loader();

$loader->registerNamespaces(array(
    'Appserver\Mdu\Models' => __DIR__ . '/../apps/mdu/models',
    'Appserver\Mdu\Modules' => __DIR__ . '/../apps/mdu/',
    'Appserver\Utils' => __DIR__ . '/../apps/utils',
    'Appserver\Utils\Extensive' => __DIR__ . '/../apps/utils/extensive',
    'Appserver\Utils\MyValidator' => __DIR__ . '/../apps/utils/validator',
    'Appserver\Utils\Tasks' => __DIR__ . '/../apps/utils/tasks',
    'Appserver\Utils\Push' => __DIR__ . '/../apps/utils/push',
));
$loader->register();