<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Framework/Events.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/Statistics.php';
require_once 'Modules/core/Model/CoreCron.php';


if(Configuration::get('sentry_dsn', '')) {
    \Sentry\init(['dsn' => Configuration::get('sentry_dsn')]);
}

try {
	$m = new CoreCron();
	$m->add(CoreCron::DAILY, 'booking_statistics');
} catch(Throwable $e) {
	Configuration::getLogger()->error('[init] Something went wrong', ['error' => $e->getMessage()]);
	if(Configuration::get('sentry_dsn', '')) {
		\Sentry\captureException($e);
	}
	exit(1);
}

while(true) {

	try {
		$dailyJob = $m->run('test_day');
		if($dailyJob){
			Configuration::getLogger()->debug('[daily][test] should execute something');
		}
		$bookingStatistics = $m->run('booking_statistics');
		if($bookingStatistics){
			Configuration::getLogger()->debug('[monthly][bookingStatistics] run');
			Events::send([
				"action" => Events::ACTION_BOOKING_STATISTICS,
				"space" => ["id" => 0],
			]);
		}

	} catch(Throwable $e) {
		Configuration::getLogger()->error('Something went wrong', ['error' => $e->getMessage()]);
		if(Configuration::get('sentry_dsn', '')) {
			\Sentry\captureException($e);
		}
	}
	Configuration::getLogger()->debug('sleep for 5 minute');
	sleep(5 * 60);

}