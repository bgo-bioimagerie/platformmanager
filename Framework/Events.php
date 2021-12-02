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
require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/helpdesk/Model/Helpdesk.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/quote/Model/Quote.php';
require_once 'Modules/services/Model/SeService.php';


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

    public function ticketCount($msg) {
        $hm = new Helpdesk();
        $model = new CoreSpace();
        $space = $model->getSpace($msg['space']['id']);
        $statHandler = new Statistics();
        $timestamp = time();
        $counts = $hm->count($msg['space']['id']);
        foreach ($counts as $count) {
            $tag = "";
            switch ($count['status']) {
                case Helpdesk::$STATUS_NEW:
                    $tag = "new";
                    break;
                case Helpdesk::$STATUS_OPEN:
                    $tag = "open";
                    break;
                case Helpdesk::$STATUS_REMINDER:
                    $tag = "reminder";
                    break;
                case Helpdesk::$STATUS_CLOSED:
                    $tag = "closed";
                    break;
                case Helpdesk::$STATUS_SPAM:
                    break;
                default:
                    $tag = "unknown";
                    break;
            }
            if($tag) {
                $stat = ['name' => 'tickets', 'fields' => ['value' => intval($count['total'])], 'tags' =>['status' =>$tag], 'time' => $timestamp];
                $statHandler->record($space['shortname'], $stat);
            }
        }
    }

    public function spaceCount() {
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
        $this->spaceCount();
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
        $this->spaceCount();
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
        $this->logger->debug('[spaceUserRoleUpdate]', ['space_id' => $msg['space']['id'], 'user' => $msg['user']['id'], 'role' => $msg['role']]);
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
            $plan = new CorePlan($space['plan'], $space['plan_expire']);
            if($plan->hasFlag(CorePlan::FLAGS_GRAFANA)) {
                $g->addUser($space['shortname'], $user['login'], $user['apikey']);
            } else {
                Configuration::getLogger()->debug('[flags][disabled] ', ['space' => $space['name'] , 'flags' => [CorePlan::FLAGS_GRAFANA]]);
            }
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


    public function spaceResourceEdit($action, $msg) {
        $this->logger->debug('[spaceResourceEdit]', ['space_id' => $msg['space']['id']]);
        $model = new CoreSpace();
        $space = $model->getSpace($msg['space']['id']);
        $modelResource = new ResourceInfo();
        $nbResources = $modelResource->admCount('re_info', $msg['space']['id']);
        
        $stat = ['name' => 'resources', 'fields' => ['value' => $nbResources['total']]];
        $statHandler = new Statistics();
        $statHandler->record($space['shortname'], $stat);
        if (array_key_exists('resource', $msg)) {
            $cname = $msg['resource']['id'];
            if (array_key_exists('name', $msg['resource'])) {
                $cname = $msg['resource']['name'] . "[$cname]";
            }
            $hmsg = "Resource $cname deleted";
            if ($action == Events::ACTION_RESOURCE_EDIT) {
                $cname = $modelResource->getName($msg['space']['id'], $msg['resource']['id']);
                $hmsg = "Resource $cname edited";
            }
            $m = new CoreHistory();
            $m->add($msg['space']['id'], $msg['_user'] ?? null, $hmsg);
        }
    }

    public function spaceServiceEdit($action, $msg) {
        $this->logger->debug('[spaceServiceEdit]', ['space_id' => $msg['space']['id']]);
        $model = new CoreSpace();
        $space = $model->getSpace($msg['space']['id']);
        $modelService = new SeService();
        $nbServices = $modelService->admCount('se_services', $msg['space']['id']);
        
        $stat = ['name' => 'services', 'fields' => ['value' => $nbServices['total']]];
        $statHandler = new Statistics();
        $statHandler->record($space['shortname'], $stat);
        if (array_key_exists('service', $msg)) {
            $cname = $msg['service']['id'];
            if (array_key_exists('name', $msg['service'])) {
                $cname = $msg['service']['name'] . "[$cname]";
            }
            $hmsg = "Service $cname deleted";
            if ($action == Events::ACTION_SERVICE_EDIT) {
                $cname = $modelService->getName($msg['space']['id'], $msg['service']['id']);
                $hmsg = "Service $cname edited";
            }
            $m = new CoreHistory();
            $m->add($msg['space']['id'], $msg['_user'] ?? null, $hmsg);
        }
    }

    public function spaceQuoteEdit($action, $msg) {
        $this->logger->debug('[spaceQuoteEdit]', ['space_id' => $msg['space']['id']]);
        $model = new CoreSpace();
        $space = $model->getSpace($msg['space']['id']);
        $modelQuote = new Quote();
        $nbQuotes = $modelQuote->admCount('qo_quotes', $msg['space']['id']);
        
        $stat = ['name' => 'quotes', 'fields' => ['value' => $nbQuotes['total']]];
        $statHandler = new Statistics();
        $statHandler->record($space['shortname'], $stat);
        
        if (array_key_exists('quote', $msg)) {
            $cname = $msg['quote']['id'];
            $hmsg = "Quote $cname deleted";
            if ($action == Events::ACTION_QUOTE_EDIT) {
                $quote = $modelQuote->getAllInfo($msg['space']['id'], $msg['quote']['id']);
                $user = "unknown";
                if($quote) {
                    $user = $quote["recipient"];
                }
                $hmsg = "Quote $cname [$user] edited";
                $stat = ['name' => 'quote', 'fields' => ['value' => 1], 'tags' => ['client' => 'unknown'], 'time' => $quote['created_at']];
                $statHandler->record($space['shortname'], $stat);
            }
            $m = new CoreHistory();
            $m->add($msg['space']['id'], $msg['_user'] ?? null, $hmsg);
        }
    }

    public function spaceCustomerEdit($action, $msg) {
        $this->logger->debug('[spaceCustomerEdit]', ['space_id' => $msg['space']['id']]);
        $model = new CoreSpace();
        $space = $model->getSpace($msg['space']['id']);
        $modelClient = new ClClient();
        $nbCustomers = $modelClient->count($msg['space']['id']);
        $stat = ['name' => 'customers', 'fields' => ['value' => $nbCustomers]];
        $statHandler = new Statistics();
        $statHandler->record($space['shortname'], $stat);
        if (array_key_exists('client', $msg)) {
            $cname = $msg['client']['id'];
            if (array_key_exists('name', $msg['client'])) {
                $cname = $msg['client']['name'] . "[$cname]";
            }
            $hmsg = "Client $cname deleted";
            if ($action == Events::ACTION_CUSTOMER_EDIT) {
                $cname = $modelClient->getName($msg['space']['id'], $msg['client']['id']);
                $hmsg = "Client $cname edited";
            }
            $m = new CoreHistory();
            $m->add($msg['space']['id'], $msg['_user'] ?? null, $hmsg);
        }
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

            $plan = new CorePlan($space['plan'], $space['plan_expire']);
            if($plan->hasFlag(CorePlan::FLAGS_GRAFANA)) {
                $g->addUser($space['shortname'], $user['login'], $user['apikey']);
            } else {
                Configuration::getLogger()->debug('[flags][disabled] ', ['space' => $space['name'] , 'flags' => [CorePlan::FLAGS_GRAFANA]]);
            }
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

    private function _calEntryRemoveStat($space, $entry){
        $id_space = $space['id'];
        $timestamp = $entry['start_time'];
        $r = new ResourceInfo();
        $resource = $r-> get($id_space, $entry['resource_id']);
        //$u = new CoreUser();
        //$id_user = $entry['recipient_id'] ?? $entry['booked_by_id'];
        // $user = $u->userAllInfo($id_user);
        $client = ['name' => 'unknown'];
        if($entry['responsible_id']) {
            $c = new ClClient();
            $is_client = $c->get($id_space, $entry['responsible_id']);
            if($is_client) {
                $client = $is_client;
            }
        }
        $value = time() - $timestamp;
        $stat = ['name' => 'calentry_cancel', 'fields' => ['value' => $value], 'tags' =>['resource' => $resource['name'], 'client' => $client['name']], 'time' => $timestamp];
        $statHandler = new Statistics();
        $statHandler->record($space['shortname'], $stat);
    } 

    private function _calEntryStat($space, $entry, $value){
        $id_space = $space['id'];
        $timestamp = $entry['start_time'];
        $r = new ResourceInfo();
        $resource = $r-> get($id_space, $entry['resource_id']);
        //$u = new CoreUser();
        //$id_user = $entry['recipient_id'] ?? $entry['booked_by_id'];
        //$user = $u->userAllInfo($id_user);
        $client = ['name' => 'unknown'];
        if($entry['responsible_id']) {
            $c = new ClClient();
            $is_client = $c->get($id_space, $entry['responsible_id']);
            if($is_client) {
                $client = $is_client;
            }
        }
        $stat = ['name' => 'calentry', 'fields' => ['value' => $value], 'tags' =>['resource' => $resource['name'], 'client' => $client['name']], 'time' => $timestamp];
        $statHandler = new Statistics();
        $statHandler->record($space['shortname'], $stat);
    }

    public function customerImport() {
        $cp = new CoreSpace();
        $spaces = $cp->getSpaces('id');
        foreach ($spaces as $space) {
            $this->spaceCustomerEdit(Events::ACTION_CUSTOMER_EDIT, ['space' => ['id' => $space['id']]]);
        }
    }

    public function resourceImport() {
        $cp = new CoreSpace();
        $spaces = $cp->getSpaces('id');
        foreach ($spaces as $space) {
            $this->spaceResourceEdit(Events::ACTION_CUSTOMER_EDIT, ['space' => ['id' => $space['id']]]);
        }
    }

    public function quoteImport() {
        $cp = new CoreSpace();
        $spaces = $cp->getSpaces('id');
        foreach ($spaces as $space) {
            $this->spaceQuoteEdit(Events::ACTION_CUSTOMER_EDIT, ['space' => ['id' => $space['id']]]);
        }
    }

    public function serviceImport() {
        $cp = new CoreSpace();
        $spaces = $cp->getSpaces('id');
        foreach ($spaces as $space) {
            $this->spaceServiceEdit(Events::ACTION_CUSTOMER_EDIT, ['space' => ['id' => $space['id']]]);
        }
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
        if(!$space) {
            return;
        }
        
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

    public function calentryRemove($msg) {
        $this->logger->debug('[calentryRemove]', ['calentry_id' => $msg['bk_calendar_entry']['id']]);
        $id_space = $msg['bk_calendar_entry']['id_space'];

        $model = new CoreSpace();
        $space = $model->getSpace($id_space);
        if(!$space) {
            return;
        }

        $m = new BkCalendarEntry();
        $entry = $m->getEntry($id_space, $msg['bk_calendar_entry']['id']);
        if(!$entry) {
            Configuration::getLogger()->debug('[calentryEdit] id not found', ['id' => $msg['bk_calendar_entry']['id'], 'id_space' => $id_space]);
            return;
        }
        $this->_calEntryStat($space, $entry, 0);
        $this->_calEntryRemoveStat($space, $entry);

    }

    public function invoiceImport() {
        $em = new EventModel();
        $sql = "SELECT * FROM `in_invoice`;";
        $resdb = $em->runRequest($sql);
        $i = 0;
        while($res = $resdb->fetch()) {
            $dt = DateTime::createFromFormat("Y-m-d H:i:s", $res["date_generated"]." 00:00:00");
            $timestamp = $dt->getTimestamp() + $i;
            $i++;
            $this->invoiceEdit(["action" => Events::ACTION_INVOICE_EDIT, "invoice" => ["id" => intval($res['id']), "created_at" => $timestamp], "id_space" => $res['id_space']]);
        }
    }


    public function invoiceEdit($msg) {
        $this->logger->debug('[invoiceEdit]', ['id_invoice' => $msg['invoice']['id']]);
        $im = new InInvoice();
        $invoice = $im->admGetBy('in_invoice', 'id', $msg['invoice']['id']);
        $id_space = $invoice['id_space'];
        $model = new CoreSpace();
        $space = $model->getSpace($id_space);
        if(!$space) {
            return;
        }
        $client = ['name' => 'unknown'];
        if($invoice['id_responsible']) {
            $c = new ClClient();
            $is_client = $c->get($id_space, $invoice['id_responsible']);
            if($is_client) {
                $client = $is_client;
            }
        }
        $total = floatval($invoice['total_ht']) - floatval($invoice['discount']);
        $timestamp = isset($msg['invoice']['created_at']) ? $msg['invoice']['created_at'] : $invoice['created_at'];
        $stat = ['name' => 'invoice', 'fields' => ['value' => $total], 'tags' =>['module' => $invoice['module'], 'client' => $client['name']], 'time' => $timestamp];
        $this->logger->debug('[invoiceEdit]', ['stat' => $stat]);
        $statHandler = new Statistics();
        $statHandler->record($space['shortname'], $stat);
    }

    public function invoiceDelete($msg) {
        $this->logger->debug('[invoiceDelete]', ['id_invoice' => $msg['invoice']['id']]);
        $im = new InInvoice();
        $invoice = $im->admGetBy('in_invoice', 'id', $msg['invoice']['id']);
        $id_space = $invoice['id_space'];
        $model = new CoreSpace();
        $space = $model->getSpace($id_space);
        $client = ['name' => 'unknown'];
        if($invoice['responsible_id']) {
            $c = new ClClient();
            $is_client = $c->get($id_space, $invoice['responsible_id']);
            if($is_client) {
                $client = $is_client;
            }
        }
        $timestamp = isset($msg['invoice']['created_at']) ? $msg['invoice']['created_at'] : $invoice['created_at'];
        $stat = ['name' => 'invoice', 'fields' => ['value' => 0], 'tags' =>['module' => $invoice['module'], 'client' => $client['name']], 'time' => $timestamp];
        $statHandler = new Statistics();
        $statHandler->record($space['shortname'], $stat);
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
                    break;
                case Events::ACTION_CAL_ENTRY_EDIT:
                    $this->calentryEdit($data);
                    break;
                case Events::ACTION_CAL_ENTRY_REMOVE:
                    $this->calentryRemove($data);
                case Events::ACTION_INVOICE_EDIT:
                    $this->invoiceEdit($data);
                    break;
                case Events::ACTION_INVOICE_DELETE:
                    $this->invoiceDelete($data);
                    break;
                case Events::ACTION_CUSTOMER_EDIT:
                case Events::ACTION_CUSTOMER_DELETE:
                    $this->spaceCustomerEdit($action, $data);
                    break;
                case Events::ACTION_HELPDESK_TICKET:
                    $this->ticketCount($data);
                    break;
                case Events::ACTION_RESOURCE_EDIT:
                case Events::ACTION_RESOURCE_DELETE:
                    $this->spaceResourceEdit($action, $data);
                    break;
                case Events::ACTION_QUOTE_EDIT:
                case Events::ACTION_QUOTE_DELETE:
                    $this->spaceQuoteEdit($action, $data);
                    break;
                case Events::ACTION_SERVICE_EDIT:
                case Events::ACTION_SERVICE_DELETE:
                    $this->spaceServiceEdit($action, $data);
                    break;
                default:
                    $this->logger->error('[message] unknown message', ['action' => $data]);
                    break;
            }
        } catch(Throwable $e) {
            $this->logger->error('[message] error', ['message' => $e->getMessage(), 'line' => $e->getLine(), 'stack' => $e->getTraceAsString()]);
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
    public const ACTION_CAL_ENTRY_REMOVE = 101;
    public const ACTION_HELPDESK_TICKET = 200;

    public const ACTION_INVOICE_EDIT = 300;
    public const ACTION_INVOICE_DELETE = 301;

    public const ACTION_CUSTOMER_EDIT = 400;
    public const ACTION_CUSTOMER_DELETE = 401;

    public const ACTION_RESOURCE_EDIT = 500;
    public const ACTION_RESOURCE_DELETE = 501;

    public const ACTION_QUOTE_EDIT = 600;
    public const ACTION_QUOTE_DELETE = 601;

    public const ACTION_SERVICE_EDIT = 700;
    public const ACTION_SERVICE_DELETE = 701;
    public const ACTION_SERVICE_PROJECT_EDIT = 710;
    public const ACTION_SERVICE_PROJECT_DELETE = 711;

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