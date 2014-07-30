<?php

require_once 'vendor/autoload.php';
use \models\Crontab;
use \models\SystemUser;

$app = new \Slim\Slim(array(
	'templates.path' => 'application/views',
	'debug' => true
));
\Slim\Route::setDefaultConditions(array(
    'hash' => '[a-z0-9]{8}'
));

// Initialize layout when not in an AJAX context
if (!$app->request->isXhr()) {
	$app->view('\library\Extra\Layout');
}

// Routes
$app->get('/', function() use ($app) {
	$crontab = new Crontab();
	$systemUser = new SystemUser();
	
	$app->render('index.phtml', array(
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
		$crontab = new Crontab();
		$job = $crontab->findByHash($hash);
		
		if ($job) {
			$crontab->run($job);
			$pid = $crontab->getLastPid();
			
			$app->render(200, array(
				'error' => false,
				'pid' => $pid,
				'msg' => sprintf('Process with PID %d started.', $pid)
			));
		} else {
			$app->render(404, array(
				'error' => true,
				'msg' => 'Cron job no longer exists'
			));
		}
	});
	
	/**
	 * Pauses schedule by commenting the job in crontab, so it no longer runs when
	 * is is supposed to.
	 */
	$app->get('/pause/:hash', $setupJsonResponse, function($hash) use ($app) {
		$crontab = new Crontab();
		$job = $crontab->findByHash($hash);
		
		if ($job) {
			$crontab->pause($job)->save();
			
			$app->render(200, array(
				'error' => false,
				'msg' => 'Job schedule has been paused.',
				'hash' => $job->getHash()
			));
		} else {
			$app->render(404, array(
				'error' => true,
				'msg' => 'Cron job no longer exists'
			));
		}
	});
	
	/**
	 * Resumes schedule by un-commenting the job in crontab.
	 */
	$app->get('/resume/:hash', $setupJsonResponse, function($hash) use ($app) {
		$crontab = new Crontab();
		$job = $crontab->findByHash($hash);
		
		if ($job) {
			$crontab->resume($job)->save();
			
			$app->render(200, array(
				'error' => false,
				'msg' => 'Job schedule has been resumed.',
				'hash' => $job->getHash()
			));
		} else {
			$app->render(404, array(
				'error' => true,
				'msg' => 'Cron job no longer exists.'
			));
		}
	});
});

$app->run();