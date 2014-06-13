<?php

use Symfony\Component\Debug\Debug;
use Silex\Provider\WebProfilerServiceProvider;
use Silex\Provider\MonologServiceProvider;

require_once __DIR__ . '/../vendor/autoload.php';

Debug::enable();

$app = require __DIR__ . '/../src/bootstrap.php';

$app->register(new WebProfilerServiceProvider(), array(
    'profiler.cache_dir' => __DIR__ . '/../var/cache/profiler',
));

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../var/logs/app_dev.log',
    'monolog.name'    => 'personal_app',
    'monolog.level'   => 300
));

$app['debug'] = true;

require __DIR__ . '/../src/controllers.php';

$app->run();