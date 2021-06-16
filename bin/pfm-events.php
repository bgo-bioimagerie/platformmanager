<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once 'Framework/Events.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/Statistics.php';
require_once 'Modules/core/Model/CoreSpace.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Handler for rabbitmq messages receiving pfm events
 */
class Backend {

	private $logger;

	public function __construct() {
		$this->logger = Configuration::getLogger();
	}

	private function spaceCreate($msg) {
		$this->logger->debug('[spaceCreate]', ['space_id' => $msg['space']['id']]);
		$model = new CoreSpace();
		$space = $model->getSpace($msg['space']['id']);
		$spaces = $model->getSpaces('id');
		Statistics::createDB($space['shortname']);
		$stat = ['name' => 'spaces', 'fields' => ['value' => count($spaces)]];
		Statistics::stat(Configuration::get('influxdb_org', 'pfm'), $stat);
	}

	private function spaceDelete($msg) {
		$this->logger->debug('[spaceDelete]', ['space_id' => $msg['space']['id']]);
		$model = new CoreSpace();
		$spaces = $model->getSpaces('id');
		$stat = ['name' => 'spaces', 'fields' => ['value' => count($spaces)]];
		Statistics::stat(Configuration::get('influxdb_org', 'pfm'), $stat);
	}

	private function spaceUserCount($msg){
		$model = new CoreSpace();
		$space = $model->getSpace($msg['space']['id']);
		$users = $model->countUsers($msg['space']['id']);
		$stat = ['name' => 'users', 'fields' => ['value' => $users[0]]];
		Statistics::stat($space['shortname'], $stat);
	}
	private function spaceUserJoin($msg) {
		$this->logger->debug('[spaceUserJoin]', ['space_id' => $msg['space']['id']]);
		$this->spaceUserCount($msg);
	}
	private function spaceUserUnjoin($msg) {
		$this->logger->debug('[spaceUserUnjoin]', ['space_id' => $msg['space']['id']]);
		$this->spaceUserCount($msg);
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
					$this->spaceCreate($data);
					break;
				case Events::ACTION_SPACE_DELETE:
					$this->spaceDelete($data);
					break;
				case Events::ACTION_SPACE_USER_JOIN:
					$this->spaceUserJoin($data);
					break;
				case Events::ACTION_SPACE_USER_UNJOIN:
					$this->spaceUserUnjoin($data);
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
