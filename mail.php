<?php

require_once  '../lib/rmail/Rmail.php';

foreach(array('email', 'name', 'subject', 'message') as $post) {
	if (!isset($_POST[$post])) {
    	echo json_encode(array('status' => 0));
    	exit;
 	}

 	${$post} = filter_var($_POST[$post], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
}

$mail = new Rmail();
$mail->setFrom($name . ' <' . $email . '>');
$mail->setSubject($subject);
$mail->setPriority('high');
$mail->setText($message);
$result = $mail->send(array('thibaud@groovinmove.com'));

echo json_encode(array('status' => 1));
exit;