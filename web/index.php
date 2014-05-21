<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

$app = new Silex\Application();

$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\SwiftmailerServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/views'
));

$app->get('/', function() use ($app) {
    return $app['twig']->render('index.html', array());
});

$app->post('/mail', function() use ($app) {
    $constraints = new Collection(array(
        'email'     => new Email(),
        'subject'   => new NotBlank(),
        'name'      => new NotBlank(),
        'message'   => new NotBlank()
    ));

    $errors = $app['validator']->validateValue($app['request']->request->all(), $constraints);

    if (count($errors) > 0) {
        return $app->json(array('message' => (string) $errors), 404);
    }

    $message = \Swift_Message::newInstance()
        ->setSubject(sprintf(
            '[thibaudanthoine.com][%s] %s',
            $app['request']->get('name'),
            $app['request']->get('subject')
        ))
        ->setFrom(array($app['request']->get('email')))
        ->setTo(array('thibaud@groovinmove.com'))
        ->setBody($app['request']->get('message'));

    $app['mailer']->send($message);

    return $app->json(array('message' => 'Thank you for your message'));
});

$app->run();