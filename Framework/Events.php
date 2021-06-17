<?php

require_once 'Framework/Configuration.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class EventHandler {

    private $logger;

    public function __construct() {
        $this->logger = Configuration::getLogger();
    }


    private function spaceCount($msg) {
        $model = new CoreSpace();
        $nbSpaces = $model->countSpaces();
        $stat = ['name' => 'spaces', 'fields' => ['value' => $nbSpaces]];
        Statistics::stat(Configuration::get('influxdb_org', 'pfm'), $stat);
    }

    public function spaceCreate($msg) {
        $this->logger->debug('[spaceCreate]', ['space_id' => $msg['space']['id']]);
        $model = new CoreSpace();
        $space = $model->getSpace($msg['space']['id']);
        Statistics::createDB($space['shortname']);
        $this->spaceCount($msg);
    }

    public function spaceDelete($msg) {
        $this->logger->debug('[spaceDelete]', ['space_id' => $msg['space']['id']]);
        $this->spaceCount($msg);
    }

    public function spaceUserCount($msg){
        $model = new CoreSpace();
        $space = $model->getSpace($msg['space']['id']);
        $nbUsers = $model->countUsers($msg['space']['id']);
        $stat = ['name' => 'users', 'fields' => ['value' => $nbUsers]];
        Statistics::stat($space['shortname'], $stat);
    }

    public function spaceUserJoin($msg) {
        $this->logger->debug('[spaceUserJoin]', ['space_id' => $msg['space']['id']]);
        $this->spaceUserCount($msg);
    }

    public function spaceUserUnjoin($msg) {
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


class Events {

    public const ACTION_SPACE_CREATE = 0;
    public const ACTION_SPACE_DELETE = 1;
    public const ACTION_SPACE_USER_JOIN = 2;
    public const ACTION_SPACE_USER_UNJOIN = 3;

    private static $connection;
    private static $channel;

    /**
     * Initialize client
     */
    public static function getChannel() {
        if(self::$channel === null) {
            $amqpHost = Configuration::get('amqp_host', '');
            if ($amqpHost == '') {
                return null;
            }
            $amqpPort = Configuration::get('amqp_port', 5672);
            $amqpUser = Configuration::get('amqp_user');
            $amqpPassword = Configuration::get('amqp_password');
            self::$connection = new AMQPStreamConnection($amqpHost, intval($amqpPort), $amqpUser, $amqpPassword);
            self::$channel = self::$connection->channel();
            self::$channel->exchange_declare('pfm_events', 'fanout', false, false, false);
        }
        return self::$channel;
    }

    /**
     * Close connection
     */
    public static function Close() {
        if(self::$channel != null) {
            self::$channel->close();
            self::$connection->close();
        }
    }
        

    /**
     * Sends a message to rabbitmq
     * @param array $message message to send
     */
    public static function send(array $message) {
        try {
            $channel = self::getChannel();
            if($channel === null) {
                return;
            }
            Configuration::getLogger()->debug('[event] send', ['message' => $message]);
            $amqpMsg = new AMQPMessage(json_encode($message));
            $channel->basic_publish($amqpMsg, 'pfm_events', '');
        } catch (Exception $e) {
            Configuration::getLogger()->error('[event] error', ['message' => $e->getMessage()]);
            return;
        }
    }
}

?>