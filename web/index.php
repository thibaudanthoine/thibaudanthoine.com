<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Silex\Provider\FormServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\SwiftmailerServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use DerAlex\Silex\YamlConfigServiceProvider;

use Symfony\Component\Validator\Constraints as Assert;

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new YamlConfigServiceProvider(__DIR__ . '/../app/config/settings.yml'));
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
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new TranslationServiceProvider(), array(
    'translator.messages' => array(),
));
$app->register(new TwigServiceProvider(), array(
    'twig.path' => __DIR__ . '/../views'
));

//$app['mailer'] = $app->share(function ($app) {
//    return new \Swift_Mailer($app['swiftmailer.transport']);
//});

$app->before(function() use ($app) {
    $app['twig']->addGlobal('layout', $app['twig']->loadTemplate('layout.html.twig'));
});

$app->get('/', function() use ($app) {
    $form = $app['form.factory']->createBuilder('form')
        ->add('name', 'text', array(
            'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 4)))
        ))
        ->add('email', 'email', array(
            'constraints' => array(new Assert\NotBlank(), new Assert\Email())
        ))
        ->add('subject', 'text', array(
            'constraints' => array(new Assert\NotBlank())
        ))
        ->add('message', 'textarea', array(
            'constraints' => array(new Assert\NotBlank())
        ))
        ->getForm();
    return $app['twig']->render('index.html.twig', array('form' => $form->createView()));
});

$app->post('/contact', function() use ($app) {

    $form = $app['form.factory']->createBuilder('form')
        ->add('name', 'text', array(
            'constraints' => array(new Assert\NotBlank(), new Assert\Length(array('min' => 4)))
        ))
        ->add('email', 'email', array(
            'constraints' => array(new Assert\NotBlank(), new Assert\Email())
        ))
        ->add('subject', 'text', array(
            'constraints' => array(new Assert\NotBlank())
        ))
        ->add('message', 'textarea', array(
            'constraints' => array(new Assert\NotBlank())
        ))
        ->getForm();

    /*
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
            preg_replace('/\[|\]/', '', $error->getPropertyPath()),
            $error->getMessage()
        );
    }

    if (count($errorMessages) > 0) {
        return $app->json(array('message' => implode('<br />', $errorMessages)), 404);
    }
    */

    $form->handleRequest($request);

    if ($form->isValid()) {
        $data = $form->getData();

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
    }

    return $app->json(array('message' => 'Thank you for your message'));
})
->bind('contact');

$app->run();