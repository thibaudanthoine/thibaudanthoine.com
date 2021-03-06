<?php

use Silex\Provider\MonologServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../src/bootstrap.php';

$app['twig.options'] = array(
    'cache' => __DIR__.'/../var/cache/twig'
);

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../var/logs/app.log',
    'monolog.name'    => 'personal_app',
    'monolog.level'   => 300
));

require __DIR__ . '/../src/controllers.php';

$app->run();