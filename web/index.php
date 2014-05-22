<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Validator\Constraints\Collection;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

$app = new Silex\Application();

$app->register(new DerAlex\Silex\YamlConfigServiceProvider(__DIR__ . '/../app/config/settings.yml'));
$app->register(new Silex\Provider\SwiftmailerServiceProvider(), array(
    'swiftmailer.options' => array(
        'host'       => $app['config']['swiftmailer']['host'],
        'port'       => $app['config']['swiftmailer']['port'],
        'username'   => $app['config']['swiftmailer']['username'],
        'password'   => $app['config']['swiftmailer']['password'],
        'encryption' => $app['config']['swiftmailer']['encryption'],
        'auth_mode'  => $app['config']['swiftmailer']['auth_mode']
    )
));
$app->register(new Silex\Provider\ValidatorServiceProvider());
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views'
));

//$app['mailer'] = $app->share(function ($app) {
//    return new \Swift_Mailer($app['swiftmailer.transport']);
//});

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

    $errorMessages = array();
    foreach ($errors as $error){
        $errorMessages[] = sprintf(
            '%s: %s',
            preg_replace('/\[|\]/', "", $error->getPropertyPath()),
            $error->getMessage()
        );
    }

    if (count($errorMessages) > 0) {
        return $app->json(array('message' => implode('<br />', $errorMessages)), 404);
    }

    try {
        $message = \Swift_Message::newInstance()
            ->setSubject(sprintf(
                '[ %s ] %s',
                $app['request']->get('name'),
                $app['request']->get('subject')
            ))
            ->setFrom(array($app['request']->get('email')))
            ->setTo(array('thibaud@groovinmove.com'))
            ->setBody($app['request']->get('message'));

        $app['mailer']->send($message);
    }
    catch (\Exception $e) {
        return $app->json(array('message' => 'Sorry, unable to send email'), 500);
    }

    return $app->json(array('message' => 'Thank you for your message'));
});

$app->run();