<?php

require_once 'Framework/Configuration.php';
require_once 'Framework/Model.php';
require_once 'Framework/Statistics.php';
require_once 'Framework/Grafana.php';

require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreSpaceUser.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/core/Model/CoreHistory.php';
require_once 'Modules/core/Model/CoreUser.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

class EventModel extends Model {

    public function runRequest($sql, $args=array()) {
        return parent::runRequest($sql, $args);
    }   

}

class EventHandler {

    private $logger;

    public function __construct() {
        $this->logger = Configuration::getLogger();
    }


    private function spaceCount($msg) {
        $model = new CoreSpace();
        $nbSpaces = $model->countSpaces();
        $stat = ['name' => 'spaces', 'fields' => ['value' => $nbSpaces]];
        $statHandler = new Statistics();
        $statHandler->record(Configuration::get('influxdb_org', 'pfm'), $stat);
    }

    public function spaceCreate($msg) {
        $this->logger->debug('[spaceCreate]', ['space_id' => $msg['space']['id']]);
        $model = new CoreSpace();
        $space = $model->getSpace($msg['space']['id']);
        $statHandler = new Statistics();
        $statHandler->createDB($space['shortname']);
        $this->spaceCount($msg);
        // create org
        $g = new Grafana();
        $g->createOrg($space['shortname']);
        // add pfm super admin
        $u = new CoreUser();
        $user = $u->getUserByLogin(Configuration::get('admin_user'));
        $g->addUser($space['shortname'], $user['login'], $user['apikey']);
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
        $statHandler = new Statistics();
        $statHandler->record($space['shortname'], $stat);
    }

    private function isSpaceOwner($id_space, $id_user) {
        $sum = new CoreSpaceUser();
        $link = $sum->getUserSpaceInfo2($id_space, $id_user);
        if($link['status'] >= CoreSpace::$MANAGER) {
            return true;
        }
        return false;
    }

    public function spaceUserRoleUpdate($msg) {
        $this->logger->debug('[spaceUserRoleUpdate][TODO]', ['space_id' => $msg['space']['id'], 'user' => $msg['user']['id'], 'role' => $msg['role']]);
        $role = $msg["role"];
        $u = new CoreUser();
        $user = $u->getInfo($msg['user']['id']);
        $login = $user['login'];
        $m = new CoreHistory();
        $m->add($msg['space']['id'], $msg['_user'] ?? null, "User $login role update [role=$role]");

        // If owner, add to or remove from grafana 
        $g = new Grafana();
        $s = new CoreSpace();
        $space = $s->getSpace($msg['space']['id']);
        if($role >= CoreSpace::$MANAGER) {
            $g->addUser($space['shortname'], $user['login'], $user['apikey']);
        } else {
            $g->delUser($space['shortname'], $user['login']);
        }
    }

    public function userApiKey($msg) {
        $this->logger->debug('[userApiKey]', ['user' => $msg['user']]);
        // TODO do nothing if not a space manager/admin
        $gm = new Grafana();
        $u = new CoreUser();
        $id_user = $msg['user']['id'];
        $user = $u->userAllInfo($id_user);
        $gm->updateUserPassword($user['login'], $user['apikey']);
    }

    public function spaceUserJoin($msg) {
        $this->logger->debug('[spaceUserJoin]', ['space_id' => $msg['space']['id']]);
        $this->spaceUserCount($msg);
        $u = new CoreUser();
        $user = $u->getInfo($msg['user']['id']);
        $login = $user['login'];
        $m = new CoreHistory();
        $m->add($msg['space']['id'], $msg['_user'] ?? null, "User $login joined space");

        // If owner, add to grafana
        $g = new Grafana();
        $s = new CoreSpace();
        $space = $s->getSpace($msg['space']['id']);
        if($this->isSpaceOwner($msg['space']['id'], $msg['user']['id'])) {
            $g->addUser($space['shortname'], $user['login'], $user['apikey']);
        }
    }

    public function spaceUserUnjoin($msg) {
        $this->logger->debug('[spaceUserUnjoin]', ['space_id' => $msg['space']['id']]);
        $this->spaceUserCount($msg);
        $u = new CoreUser();
        $user = $u->getInfo($msg['user']['id']);
        $login = $user['login'];
        $m = new CoreHistory();
        $m->add( $msg['space']['id'], $msg['_user'] ?? null, "User $login left space");

        // If owner, remove from grafana
        $g = new Grafana();
        $s = new CoreSpace();
        $space = $s->getSpace($msg['space']['id']);
        if($this->isSpaceOwner($msg['space']['id'], $msg['user']['id'])) {
            $g->delUser($space['shortname'], $user['login']);
        }
    }

    private function _calEntryStat($space, $entry, $value){
        $id_space = $space['id'];
        $timestamp = $entry['start_time'];
        $r = new ResourceInfo();
        $resource = $r-> get($id_space, $entry['resource_id']);
        $u = new CoreUser();
        $id_user = $entry['recipient_id'] ?? $entry['booked_by_id'];
        $user = $u->userAllInfo($id_user);
        $client = ['name' => 'unknown'];
        if($entry['responsible_id']) {
            $c = new ClClient();
            $is_client = $c->get($entry['responsible_id'], $id_space);
            if($is_client) {
                $client = $is_client;
            }
        }
        $stat = ['name' => 'calentry', 'fields' => ['value' => $value], 'tags' =>['resource' => $resource['name'], 'user' => $user['login'], 'client' => $client['name']], 'time' => $timestamp];
        $statHandler = new Statistics();
        $statHandler->record($space['shortname'], $stat);
    }

    public function calentryImport() {
        $em = new EventModel();
        $sql = "SELECT * FROM `bk_calendar_entry`;";
        $resdb = $em->runRequest($sql);
        while($res = $resdb->fetch()) {
            $this->calentryEdit(["action" => Events::ACTION_CAL_ENTRY_EDIT, "bk_calendar_entry_old" => null, "bk_calendar_entry" => ["id" => intval($res['id']), "id_space" => $res['id_space']]]);
        }
    }

    public function calentryEdit($msg) {
        $this->logger->debug('[calentryEdit]', ['calentry_id' => $msg['bk_calendar_entry']['id']]);
        $id_space = $msg['bk_calendar_entry']['id_space'];

        $model = new CoreSpace();
        $space = $model->getSpace($id_space);
        
        if($msg['bk_calendar_entry_old']) {
            $old = $msg['bk_calendar_entry_old'];
            $this->_calEntryStat($space, $old, 0);
        }

        $m = new BkCalendarEntry();
        $entry = $m->getEntry($id_space, $msg['bk_calendar_entry']['id']);
        if(!$entry) {
            Configuration::getLogger()->debug('[calentryEdit] id not found', ['id' => $msg['bk_calendar_entry']['id'], 'id_space' => $id_space]);
            return;
        }

        $this->_calEntryStat($space, $entry, 1);
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
                case Events::ACTION_SPACE_USER_ROLEUPDATE:
                    $this->spaceUserRoleUpdate($data);
                    break;
                case Events::ACTION_USER_APIKEY:
                    $this->userApikey($data);
                case Events::ACTION_CAL_ENTRY_EDIT:
                    $this->calentryEdit($data);
                    break;
                default:
                    $this->logger->error('[message] unknown message', ['action' => $action]);
                    break;
            }
        } catch(Throwable $e) {
            $this->logger->error('[message] error', ['message' => $e->getMessage()]);
        }
    }
}


class Events {

    public const ACTION_SPACE_CREATE = 0;
    public const ACTION_SPACE_DELETE = 1;
    public const ACTION_SPACE_USER_JOIN = 2;
    public const ACTION_SPACE_USER_UNJOIN = 3;
    public const ACTION_SPACE_USER_ROLEUPDATE = 4;
    public const ACTION_USER_APIKEY = 5;

    public const ACTION_CAL_ENTRY_EDIT = 100;

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
            self::$channel = null;
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
            $message['_user'] = $_SESSION['login'] ?? 'unknown';
            $amqpMsg = new AMQPMessage(json_encode($message));
            $channel->basic_publish($amqpMsg, 'pfm_events', '');
        } catch (Exception $e) {
            Configuration::getLogger()->error('[event] error', ['message' => $e->getMessage()]);
            return;
        }
    }
}

?>