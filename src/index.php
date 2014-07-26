<?php

require_once 'vendor/autoload.php';

$app = new \Slim\Slim(array(
	'view' => new \library\Extra\Layout(),
	'templates.path' => 'application/views',
	'debug' => true
));
\Slim\Route::setDefaultConditions(array(
    'hash' => '[a-z0-9]{8}'
));

// Routes
$app->get('/', function() use ($app) {
	$crontab = new \models\Crontab();
	$systemUser = new \models\SystemUser();
	
	$app->render('list.phtml', array(
		'crontab' => $crontab,
		'systemUser' => $systemUser
	));
});

/**
 * Groups cron job related routes.
 */
$app->group('/job', function() use ($app) {
	/**
	 * Should be used as a route middleware to setup allow for the response
	 * to be JSON in the route callable.
	 * 
	 * @return void
	 */
	$setupJsonResponse = function() {
		$app = \Slim\Slim::getInstance();
		
        $app->view(new \JsonApiView());
        $app->add(new \JsonApiMiddleware());
	};
	
	/**
	 * Runs a cron job in background.
	 * 
	 * @see http://symfony.com/doc/current/components/process.html
	 */
	$app->get('/run/:hash', $setupJsonResponse, function($hash) use ($app) {
		$crontab = new \models\Crontab();
		$job = $crontab->findByHash($hash);
		
		if ($job) {
			$process = new \Symfony\Component\Process\Process($job->getCommand());
			try {
				$process->start();
			} catch (Exception $e) {
				$app->render(200, array(
					'error' => true,
					'message' => sprintf('Failed to start job: %s', $e->getMessage())
				));
			}
			
			$app->render(200, array(
				'error' => false,
				'pid' => $process->getPid()
			));
		} else {
			$app->render(200, array(
				'error' => true,
				'message' => 'Cron job no longer exists'
			));
		}
	});
});

$app->run();