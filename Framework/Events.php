<?php

require_once 'Framework/Configuration.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class Events {

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
            Configuration::getLogger()->error('CONNECT TO', ["host" => $amqpHost, "port" => $amqpPort, "u" => $amqpUser, "p" => $amqpPassword]);
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