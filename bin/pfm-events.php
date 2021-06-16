<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Framework/Events.php';
require_once 'Framework/Configuration.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Handler for rabbitmq messages receiving pfm events
 */
class Backend {

	private $logger;

	public function __construct() {
		$this->logger = Configuration::getLogger();
	}

	/**
	 * Handle message from rabbitmq
	 * 
	 * @param PhpAmqpLib\Message\AMQPMessage $msg message (content in $msg->body in text format)
	 */
	public function message($msg) {
		$this->logger->debug('[message]', ['message' => $msg]);
		try {
			$data = json_decode($msg->body, true);
			$action = $data['action'] ?? -1;  // if action not in message, set to -1 and fail
			if(!is_int($action)) {
				$this->logger->error('[message] invalid action', ['action' => $action]);
				$action = -1;
			}
			switch ($action) {
				case Events::ACTION_SPACE_CREATE:
					$this->logger->debug('[message] nothing to to', ['action' => $action]);
					break;
				case Events::ACTION_SPACE_DELETE:
					$this->logger->debug('[message] nothing to to', ['action' => $action]);
					break;
				default:
					$this->logger->error('[message] unknown message', ['action' => $action]);
					break;
			}
		} catch(Exception $e) {
			$this->logger->error('[message] error', ['message' => $e->getMessage()]);
		}
	}
}


$channel = Events::getChannel();
list($queue_name, ,) = $channel->queue_declare("pfm_events", false, true, false, false);

$channel->queue_bind($queue_name, 'pfm_events');

echo " [*] Waiting for logs. To exit press CTRL+C\n";

$callback = function ($msg) {
	echo ' [x] Received ', $msg->body, "\n";
	$backend = new Backend();
	$backend->message($msg);
	$msg->ack();
};


$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();
