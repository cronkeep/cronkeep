<?php

require_once 'vendor/autoload.php';

$app = new \Slim\Slim();

$app->get('/', function () {
    $crontab = new \models\Crontab();
	$crontab->load();
	
	foreach ($crontab as $job) {
		var_dump($job->getExpression());
		var_dump($job->getCommand());
	}
});

$app->run();