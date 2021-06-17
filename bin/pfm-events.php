<?php
require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Framework/Events.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/Statistics.php';
require_once 'Modules/core/Model/CoreSpace.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$channel = Events::getChannel();
list($queue_name, ,) = $channel->queue_declare("pfm_events", false, true, false, false);

$channel->queue_bind($queue_name, 'pfm_events');

echo " [*] Waiting for logs. To exit press CTRL+C\n";

$callback = function ($msg) {
	try {
		$backend = new EventHandler();
		$backend->message($msg);
	} catch(Exception $e) {
		Configuration::getLogger()->error('something went wrong', ['error' => $e->getMessage()]);
	}
	$msg->ack();
};


$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
