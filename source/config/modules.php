<?php

/**
 * Register application modules
 */
$application->registerModules(array(
    'api_0.1' => array(
        'className' => 'Appserver\v1\Module',
        'path' => __DIR__ . '/../apps/api/v0.1/Module.php'
    ),
    'api_0.2' => array(
        'className' => 'Appserver\v2\Module',
        'path' => __DIR__ . '/../apps/api/v0.2/Module.php'
    ),
    'api_0.3' => array(
        'className' => 'Appserver\v3\Module',
        'path' => __DIR__ . '/../apps/api/v0.3/Module.php'
    )
));