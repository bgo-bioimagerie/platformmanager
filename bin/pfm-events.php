<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Framework/Events.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreConfig.php';
require_once 'Framework/Statistics.php';
require_once 'Modules/core/Model/CoreSpace.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

pcntl_async_signals(true);

pcntl_signal(SIGTERM, function ($signo) {
    Configuration::getLogger()->error("SIGTERM!!!!!!");
    //var_dump("SIGTERM"); die;
});

// to defined timezone: date_default_timezone_set(Configuration::get('timezone', 'UTC'));

if (Configuration::get('sentry_dsn', '')) {
    \Sentry\init(['dsn' => Configuration::get('sentry_dsn')]);
}

while (true) {

    try {

        $channel = Events::getChannel();
        list($queue_name, ,) = $channel->queue_declare("pfm_events", false, true, false, false);

        $channel->queue_bind($queue_name, 'pfm_events');

        echo " [*] Waiting for logs. To exit press CTRL+C\n";

        $callback = function ($msg) {
            try {
                $backend = new EventHandler();
                $backend->message($msg);
                // params use a cache to avoid reloading all params
                // we are in a loop, reload params between messages
                CoreConfig::clearParams();
            } catch (Exception $e) {
                Configuration::getLogger()->error('something went wrong', ['error' => $e->getMessage()]);
                if (Configuration::get('sentry_dsn', '')) {
                    \Sentry\captureException($e);
                }
            }
            Model::resetDatabase();
            $msg->ack();
        };


        $channel->basic_consume($queue_name, '', false, false, false, false, $callback);

        while ($channel->is_open()) {
            $channel->wait();
        }

    } catch (Throwable $e) {
        Configuration::getLogger()->error('Something went wrong', ['error' => $e->getMessage()]);
        if (Configuration::get('sentry_dsn', '')) {
            \Sentry\captureException($e);
        }
    }

    Events::close();
    Configuration::getLogger()->debug('sleep for 1 minute');
    sleep(1 * 60);

}
