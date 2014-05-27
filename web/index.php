<?php

require_once __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../src/bootstrap.php';
$app['debug'] = true;

require __DIR__ . '/../src/controllers.php';

$app->run();