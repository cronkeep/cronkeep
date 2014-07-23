<?php

require_once 'vendor/autoload.php';

$app = new \Slim\Slim(array(
	'view' => new \library\Extra\Layout(),
	'templates.path' => 'application/views'
));

$app->get('/', function () use ($app) {
    $crontab = new \models\Crontab();
	$crontab->load();
	
	$app->render('list.phtml', array(
		'crontab' => $crontab
	));
});

$app->run();