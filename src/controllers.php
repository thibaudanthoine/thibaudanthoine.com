<?php

use Thibaud\Form\ContactType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Layout
 */
$app->before(function() use ($app) {
    $app['twig']->addGlobal('layout', $app['twig']->loadTemplate('layout.html.twig'));
});

/**
 * Index
 */
$app->get('/', function() use ($app) {
    $form = $app['form.factory']->create(new ContactType());
    return $app['twig']->render('index.html.twig', array('form' => $form->createView()));
});

/**
 * Ajax contact post
 */
$app->post('/contact', function(Request $request) use ($app) {
    $form = $app['form.factory']->create(new ContactType());
    $form->handleRequest($request);

    if (!$form->isValid()) {
        $fieldsErrors = array(
            'name' => $form->get('name')->getErrors(),
            'email' => $form->get('email')->getErrors(),
            'subject' => $form->get('subject')->getErrors(),
            'message' => $form->get('message')->getErrors()
        );

        $errorMessages = array();
        foreach ($fieldsErrors as $errors) {
            foreach ($errors as $error) {
                array_push($errorMessages, $error->getMessage());
            }
        }

        return $app->json(array('message' => implode('<br>', $errorMessages)), 404);
    }

    try {
        $data = $form->getData();
        $message = \Swift_Message::newInstance()
            ->setSubject(sprintf(
                '[ %s ] %s',
                $data['name'],
                $data['subject']
            ))
            ->setFrom(array($data['email']))
            ->setTo(array('thibaud@groovinmove.com'))
            ->setBody($data['message']);

        $app['mailer']->send($message);
    }
    catch (\Exception $e) {
        return $app->json(array('message' => 'Sorry, unable to send email'), 500);
    }

    return $app->json(array('message' => 'Thank you for your message'));
})->bind('contact');