<?php

use Silex\Provider\FormServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\MonologServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;
use Thibaud\Silex\Provider\InstagramMinimalistServiceProvider;
use Thibaud\Twig\Extension\InstagramMinimalistExtension;

$app = new Silex\Application();

$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new YamlConfigServiceProvider(__DIR__ . '/../resources/config/settings.yml'));
$app->register(new SwiftmailerServiceProvider(), array(
    'swiftmailer.options' => array(
        'host'       => $app['config']['swiftmailer']['host'],
        'port'       => $app['config']['swiftmailer']['port'],
        'username'   => $app['config']['swiftmailer']['username'],
        'password'   => $app['config']['swiftmailer']['password'],
        'encryption' => $app['config']['swiftmailer']['encryption'],
        'auth_mode'  => $app['config']['swiftmailer']['auth_mode']
    )
));

$app->register(new TranslationServiceProvider(), array(
    'translator.messages' => array()
));

$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../resources/views'
));

$app->register(new MonologServiceProvider(), array(
    'monolog.logfile' => __DIR__ . '/../resources/log/app.log',
    'monolog.name'    => 'personal_app',
    'monolog.level'   => 300
));

$app->register(new InstagramMinimalistServiceProvider(), array(
    'instagram.user_id' => $app['config']['instagram']['user_id'],
    'instagram.access_token' => $app['config']['instagram']['access_token'],
    'instagram.api.base_url' => $app['config']['instagram']['api_base_url']
));

$app['twig'] = $app->share(
    $app->extend(
        'twig',
        function ($twig, $app) {
            $twig->addExtension(new InstagramMinimalistExtension($app));
            $twig->addExtension(new Twig_Extensions_Extension_Text($app));
            return $twig;
        }
    )
);

/*$app['mailer'] = $app->share(function ($app) {
    return new \Swift_Mailer($app['swiftmailer.transport']);
});*/

return $app;