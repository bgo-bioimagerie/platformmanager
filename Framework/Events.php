<?php

require_once 'Framework/Configuration.php';
require_once 'Framework/Model.php';
require_once 'Framework/Statistics.php';
require_once 'Framework/Grafana.php';
require_once 'Framework/Email.php';

require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreSpaceUser.php';
require_once 'Modules/core/Model/CoreUserSettings.php';
require_once 'Modules/core/Model/CoreFiles.php';

require_once 'Modules/core/Model/CoreVirtual.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/booking/Model/BkStats.php';
require_once 'Modules/booking/Model/BkStatsUser.php';

require_once 'Modules/core/Model/CoreHistory.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/helpdesk/Model/Helpdesk.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/quote/Model/Quote.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeStats.php';

require_once 'Modules/statistics/Model/GlobalStats.php';

require_once 'Modules/invoices/Model/GlobalInvoice.php';

require_once 'Modules/booking/Model/BookingInvoice.php';
require_once 'Modules/services/Model/ServicesInvoice.php';

require_once 'Modules/rating/Model/Rating.php';
require_once 'Modules/rating/Model/RatingTranslator.php';



use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Sentry\Event;

class EventModel extends Model
{
    public function runRequest($sql, $args=array())
    {
        return parent::runRequest($sql, $args);
    }
}

/**
 * Private test class to simulate messages
 */
class FakeMsg
{
    public string $body = "";
}

/**
 * Handler to manage messages from RabbitMQ
 */
class EventHandler
{
    private $logger;

    public function __construct()
    {
        $this->logger = Configuration::getLogger();
    }

    private function prometheus($reqStart, $reqEnd, $action, $ok=true)
    {
        if (!Configuration::get('redis_host')) {
            return;
        }
        Configuration::getLogger()->debug('[prometheus] stat', ['action' => $action]);
        try {
            \Prometheus\Storage\Redis::setDefaultOptions(
                [
                    'host' => Configuration::get('redis_host'),
                    'port' => intval(Configuration::get('redis_port', 6379)),
                    'password' => null,
                    'timeout' => 0.1, // in seconds
                    'read_timeout' => '10', // in seconds
                    'persistent_connections' => false
                ]
            );
            $event = "event_$action";
            $registry = \Prometheus\CollectorRegistry::getDefault();
            $counter = $registry->getOrRegisterCounter('pfmevent', 'request_nb', 'quantity', ['action', 'status']);
            $counter->incBy(1, [$event, $ok ? 'success' : 'error']);
            $gauge = $registry->getOrRegisterHistogram('pfmevent', 'request_time', 'time', ['action'], [10, 20, 50, 100, 1000]);
            $gauge->observe(($reqEnd - $reqStart)*1000, [$event]);
        } catch (Exception $e) {
            Configuration::getLogger()->error('[prometheus] error', ['error' => $e]);
        }
    }

    public function ticketCount($msg)
    {
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
            if ($tag) {
                $stat = ['name' => 'tickets', 'fields' => ['value' => intval($count['total'])], 'tags' =>['status' =>$tag], 'time' => $timestamp];
                $statHandler->record($space['shortname'], $stat);
            }
        }
    }

    public function spaceCount()
    {
        $model = new CoreSpace();
        $nbSpaces = $model->countSpaces();
        $stat = ['name' => 'spaces', 'fields' => ['value' => $nbSpaces]];
        $statHandler = new Statistics();
        $statHandler->record(Configuration::get('influxdb_org', 'pfm'), $stat);
    }

    public function spaceCreate($msg)
    {
        $this->logger->debug('[spaceCreate]', ['space_id' => $msg['space']['id']]);
        $model = new CoreSpace();
        $space = $model->getSpace($msg['space']['id']);
        $statHandler = new Statistics();
        $statHandler->createDB($space['shortname']);
        $this->spaceCount();
        // create mysql db and views for space
        $model->createDbAndViews($space);
        // create org
        $g = new Grafana();
        $g->createOrg($space);
        // add pfm super admin
        $u = new CoreUser();
        $user = $u->getUserByLogin(Configuration::get('admin_user'));
        $g->addUser($space['shortname'], $user['login'], $user['apikey']);
    }

    public function spaceDelete($msg)
    {
        $this->logger->debug('[spaceDelete]', ['space_id' => $msg['space']['id']]);
        $this->spaceCount();
    }

    public function spaceUserCount($msg)
    {
        $model = new CoreSpace();
        $space = $model->getSpace($msg['space']['id']);
        $nbUsers = $model->countUsers($msg['space']['id']);
        $stat = ['name' => 'users', 'fields' => ['value' => $nbUsers]];
        $statHandler = new Statistics();
        $statHandler->record($space['shortname'], $stat);
    }

    private function isSpaceOwner($id_space, $id_user)
    {
        $sum = new CoreSpaceUser();
        $link = $sum->getUserSpaceInfo2($id_space, $id_user);
        if ($link && $link['status'] >= CoreSpace::$MANAGER) {
            return true;
        }
        return false;
    }

    public function spaceUserRoleUpdate($msg)
    {
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
        if ($role >= CoreSpace::$MANAGER) {
            $plan = new CorePlan($space['plan'], $space['plan_expire']);
            if ($plan->hasFlag(CorePlan::FLAGS_GRAFANA)) {
                $g->addUser($space['shortname'], $user['login'], $user['apikey']);
            } else {
                Configuration::getLogger()->debug('[flags][disabled] ', ['space' => $space['name'] , 'flags' => [CorePlan::FLAGS_GRAFANA]]);
            }
        } else {
            $g->delUser($space['shortname'], $user['login']);
        }
    }

    public function userApiKey($msg)
    {
        $this->logger->debug('[userApiKey]', ['user' => $msg['user']]);
        $gm = new Grafana();
        $u = new CoreUser();
        $id_user = $msg['user']['id'];
        $user = $u->userAllInfo($id_user);
        $gm->updateUserPassword($user['login'], $user['apikey']);
    }


    public function spaceResourceEdit($action, $msg)
    {
        $this->logger->debug('[spaceResourceEdit]', ['space_id' => $msg['space']['id']]);
        $model = new CoreSpace();
        $space = $model->getSpace($msg['space']['id']);
        $modelResource = new ResourceInfo();
        $nbResources = $modelResource->admCount($msg['space']['id']);

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

    public function spaceServiceEdit($action, $msg)
    {
        $this->logger->debug('[spaceServiceEdit]', ['space_id' => $msg['space']['id']]);
        $model = new CoreSpace();
        $space = $model->getSpace($msg['space']['id']);
        $modelService = new SeService();
        $nbServices = $modelService->admCount($msg['space']['id']);

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

    public function spaceQuoteEdit($action, $msg)
    {
        $this->logger->debug('[spaceQuoteEdit]', ['space_id' => $msg['space']['id']]);
        $modelQuote = new Quote();

        if (array_key_exists('quote', $msg)) {
            $cname = $msg['quote']['id'];
            $hmsg = "Quote $cname deleted";
            if ($action == Events::ACTION_QUOTE_EDIT) {
                $quote = $modelQuote->getAllInfo($msg['space']['id'], $msg['quote']['id']);
                $user = "unknown";
                if ($quote) {
                    $user = $quote["recipient"];
                }
                $hmsg = "Quote $cname [$user] edited";
            }
            $m = new CoreHistory();
            $m->add($msg['space']['id'], $msg['_user'] ?? null, $hmsg);
        }
    }

    public function spaceCustomerEdit($action, $msg)
    {
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

    public function spaceUserJoin($msg)
    {
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
        if ($this->isSpaceOwner($msg['space']['id'], $msg['user']['id'])) {
            $plan = new CorePlan($space['plan'], $space['plan_expire']);
            if ($plan->hasFlag(CorePlan::FLAGS_GRAFANA)) {
                $g->addUser($space['shortname'], $user['login'], $user['apikey']);
            } else {
                Configuration::getLogger()->debug('[flags][disabled] ', ['space' => $space['name'] , 'flags' => [CorePlan::FLAGS_GRAFANA]]);
            }
        }
    }

    public function spaceUserUnjoin($msg)
    {
        $this->logger->debug('[spaceUserUnjoin]', ['space_id' => $msg['space']['id']]);
        $this->spaceUserCount($msg);
        $u = new CoreUser();
        $user = $u->getInfo($msg['user']['id']);
        $login = $user['login'];
        $m = new CoreHistory();
        $m->add($msg['space']['id'], $msg['_user'] ?? null, "User $login left space");

        // If owner, remove from grafana
        $g = new Grafana();
        $s = new CoreSpace();
        $space = $s->getSpace($msg['space']['id']);
        // User is already removed, check if role set in message
        // if user is at least manager, remove from grafana
        if (isset($msg['role']) && $msg['role'] >= CoreSpace::$MANAGER) {
            $g->delUser($space['shortname'], $user['login']);
        }

        $cus = new CoreUserSettings();
        $user_lang = $cus->getUserSetting($user['id'], "language") ?? 'en';
        // Send mail to user
        $email = new Email();
        $from = $email->getFromEmail($space['shortname']);
        $fromName = "Platform-Manager";
        $subject = CoreTranslator::MailSubjectPrefix($space['name'], $user_lang) . " " . CoreTranslator::spaceUserUnjoin($user_lang);
        $email->sendEmail($from, $fromName, $user['email'], $subject, CoreTranslator::spaceUserUnjoinTxt($space['name'], $user_lang));
    }

    public function spacePlanEdit($msg)
    {
        // If owner, add to grafana
        $g = new Grafana();
        $s = new CoreSpace();
        $space = $s->getSpace($msg['space']['id']);


        $plan = new CorePlan($msg['plan']['id'], 0);
        $oldplan = null;
        if (!array_key_exists('old', $msg)) {
            $msg['old'] = ['id' => 0];
        }
        $oldplan = new CorePlan($msg['old']['id'], 0);

        $csu = new CoreSpaceUser();
        $managers = $csu->managersOrAdmin($space['id']);
        $planInfo = $plan->plan();
        if (!$planInfo) {
            Configuration::getLogger()->error('invalid plan', $msg);
            return;
        }
        Configuration::getLogger()->debug('[plan] edit check flags', ['flags' => $plan->Flags()]);
        if ($plan->hasFlag(CorePlan::FLAGS_GRAFANA) !== $oldplan->hasFlag(CorePlan::FLAGS_GRAFANA)) {
            if ($plan->hasFlag(CorePlan::FLAGS_GRAFANA)) {
                Configuration::getLogger()->debug('[plan] add grafana', ['plan' => $planInfo['name']]);
                foreach ($managers as $manager) {
                    $g->addUser($space['shortname'], $manager['login'], $manager['apikey']);
                }
            } else {
                Configuration::getLogger()->debug('[plan] del grafana', ['plan' => $planInfo['name']]);
                foreach ($managers as $manager) {
                    $g->delUser($space['shortname'], $manager['login']);
                }
            }
        } else {
            Configuration::getLogger()->debug('[plan] no grafana change');
        }
        $m = new CoreHistory();
        $m->add($msg['space']['id'], $msg['_user'] ?? null, "Space plan updated: ".$planInfo['name']);
    }

    public function customerImport()
    {
        $cp = new CoreSpace();
        $spaces = $cp->getSpaces('id');
        foreach ($spaces as $space) {
            $this->spaceCustomerEdit(Events::ACTION_CUSTOMER_EDIT, ['space' => ['id' => $space['id']]]);
        }
    }

    public function resourceImport()
    {
        $cp = new CoreSpace();
        $spaces = $cp->getSpaces('id');
        foreach ($spaces as $space) {
            $this->spaceResourceEdit(Events::ACTION_RESOURCE_EDIT, ['space' => ['id' => $space['id']]]);
        }
    }

    public function quoteImport()
    {
        $cp = new CoreSpace();
        $spaces = $cp->getSpaces('id');
        foreach ($spaces as $space) {
            $this->spaceQuoteEdit(Events::ACTION_QUOTE_EDIT, ['space' => ['id' => $space['id']]]);
        }
    }

    public function serviceImport()
    {
        $cp = new CoreSpace();
        $spaces = $cp->getSpaces('id');
        foreach ($spaces as $space) {
            $this->spaceServiceEdit(Events::ACTION_SERVICE_EDIT, ['space' => ['id' => $space['id']]]);
        }
    }

    public function calentryImport()
    {
        $em = new EventModel();
        $sql = "SELECT * FROM `bk_calendar_entry`;";
        $resdb = $em->runRequest($sql);
        while ($res = $resdb->fetch()) {
            $this->calentryEdit(["action" => Events::ACTION_CAL_ENTRY_EDIT, "bk_calendar_entry_old" => null, "bk_calendar_entry" => ["id" => intval($res['id']), "id_space" => $res['id_space']]]);
        }
    }

    public function calentryEdit($msg)
    {
        $this->logger->debug('[calentryEdit][nothing to do]', ['calentry_id' => $msg['bk_calendar_entry']['id']]);
    }

    public function calentryRemove($msg)
    {
        $this->logger->debug('[calentryRemove][nothing to do]', ['calentry_id' => $msg['bk_calendar_entry']['id']]);
    }

    public function invoiceImport()
    {
        $em = new EventModel();
        $sql = "SELECT * FROM `in_invoice`;";
        $resdb = $em->runRequest($sql);
        $i = 0;
        while ($res = $resdb->fetch()) {
            $dt = DateTime::createFromFormat("Y-m-d H:i:s", $res["date_generated"]." 00:00:00");
            $timestamp = $dt->getTimestamp() + $i;
            $i++;
            $this->invoiceEdit(["action" => Events::ACTION_INVOICE_EDIT, "invoice" => ["id" => intval($res['id']), "created_at" => $timestamp], "id_space" => $res['id_space']]);
        }
    }


    public function invoiceEdit($msg)
    {
        $this->logger->debug('[invoiceEdit][nothing to do]', ['id_invoice' => $msg['invoice']['id']]);
    }

    public function invoiceDelete($msg)
    {
        $this->logger->debug('[invoiceDelete][nothing to do', ['id_invoice' => $msg['invoice']['id']]);
    }

    public function statRequest($msg)
    {
        Configuration::getLogger()->debug('[statRequest] '.$msg['stat'].' statistics');
        $c = new CoreFiles();
        $f = $c->get($msg['file']['id']);
        $file = $c->path($f);
        $id_space = $msg['space']['id'];
        $lang = $msg['lang'] ?? 'en';
        $c->status($msg['space']['id'], $msg['file']['id'], CoreFiles::$IN_PROGRESS, '');
        try {
            switch ($msg["stat"]) {
                case GlobalStats::STATS_GLOBAL:
                    $gs = new GlobalStats();
                    $gs->generateStats($file, $msg['dateBegin'], $msg['dateEnd'], $msg['excludeColorCode'], $msg['generateclientstats'], $msg['space']['id'], $lang);
                    break;
                case BkStats::STATS_AUTH_STAT:
                    $bs = new BkStats();
                    $bs->generateStats($file, $msg['space']['id'], $msg['dateBegin'], $msg['dateEnd'], $lang);
                    break;
                case BkStats::STATS_AUTH_LIST:
                    $statUserModel = new BkStatsUser();
                    $resource_id = $msg['resource_id'];
                    if ($msg['email'] != "") {
                        $f = $statUserModel->authorizedUsers($file, $resource_id, $id_space, $lang, true);
                    } else {
                        $f = $statUserModel->authorizedUsers($file, $resource_id, $id_space, $lang);
                    }
                    break;
                case BkStats::STATS_BK_USERS:
                    $model = new BkStatsUser();
                    $users = $model->bookingUsers($id_space, $msg['dateBegin'], $msg['dateEnd'], $lang);
                    $bs = new BkStats();
                    $bs->exportstatbookingusersCSV($file, $users);
                    break;
                case BkStats::STATS_BK:
                    $bs = new BkStats();
                    $bs->getBalanceReport($file, $id_space, $msg['dateBegin'], $msg['dateEnd'], $msg['excludeColorCode'], $msg['generateclientstats'], null, $lang);
                    break;
                case BkStats::STATS_QUANTITIES:
                    $bs = new BkStats();
                    $bs->getQuantitiesReport($file, $id_space, $msg['dateBegin'], $msg['dateEnd'], $lang);
                    break;
                case BkStats::STATS_BK_TIME:
                    $bs = new BkStats();
                    $bs->getReservationsRespReport($file, $id_space, $msg['dateBegin'], $msg['dateEnd'], $lang);
                    break;
                case SeStats::STATS_PROJECTS:
                    $ss = new SeStats();
                    $ss->generateBalanceReport($file, $id_space, $msg['dateBegin'], $msg['dateEnd'], $lang);
                    break;
                case SeStats::STATS_PROJECT_SAMPLES:
                    $ss = new SeStats();
                    $ss->samplesReport($file, $id_space, $lang);
                    break;
                case SeStats::STATS_MAIL_RESPS:
                    $ss = new SeStats();
                    $ss->emailRespsReport($file, $id_space, $msg['dateBegin'], $msg['dateEnd'], $lang);
                    break;
                case SeStats::STATS_ORDERS:
                    $ss = new SeOrderStats();
                    $ss->generateBalance($file, $id_space, $msg['dateBegin'], $msg['dateEnd'], null, $lang);
                    break;
                default:
                    Configuration::getLogger()->error('[statRequest] unknown request', ['stat' => $msg['stat']]);
                    break;
            }
        } catch (Throwable $e) {
            Configuration::getLogger()->debug('[statRequest][error] '.$msg['stat'].' statistics', ['error' => $e->getMessage()]);
            $c->status($msg['space']['id'], $msg['file']['id'], CoreFiles::$ERROR, $e->getMessage());
            throw $e;
        }
        $c->status($msg['space']['id'], $msg['file']['id'], CoreFiles::$READY, '');
        Configuration::getLogger()->debug('[statRequest] '.$msg['stat'].' statistics done!');
    }


    public function closeRequest($id_space, $rid, $found)
    {
        $cv = new CoreVirtual();
        if (!$found) {
            $cv->updateRequest($id_space, 'invoices', $rid, 'nothing to invoice');
        } else {
            $cv->deleteRequest($id_space, 'invoices', $rid);
        }
    }

    public function campaignRequest($msg)
    {
        Configuration::getLogger()->debug('[campaignRequest] ', ['campaign' => $msg['campaign']['id']]);
        $id_space = $msg['space']['id'];
        $c = new CoreSpace();
        $space = $c->getSpace($id_space);
        $campaign_id = $msg['campaign']['id'];
        $cm = new RatingCampaign();
        $campaign = $cm->get($id_space, $campaign_id);
        if (!$campaign) {
            Configuration::getLogger()->error('[campaignRequest] campaign not found', ['campaign' => $msg['campaign']]);
            return;
        }
        $b = new BkCalendarEntry();
        $emails = $b->getEmailsWithEntriesForPeriod($id_space, $campaign['from_date'], $campaign['to_date'], CoreSpace::$USER);
        $p = new SeProject();
        $pemails = $p->getEmailsForClosedProjectsByPeriod($id_space, date('Y-m-d', $campaign['from_date']), date('Y-m-d', $campaign['to_date']));
        foreach ($pemails as $email) {
            if (!in_array($email, $emails)) {
                $emails[] = $email;
            }
        }
        $cm->set($campaign['id_space'], $campaign['id'], $campaign['from_date'], $campaign['to_date'], $campaign['limit_date'], $campaign['message'], count($emails));

        $me = new Email();
        $from = Configuration::get('smtp_from');
        if ($c->getSpaceMenusRole($id_space, "helpdesk")) {
            $from = $me->getFromEmail($space['shortname']);
        }
        $fromName = "Platform-Manager";

        $cus = new CoreUserSettings();
        $cu = new CoreUser();
        $from_date_str = '';
        $to_date_str = '';
        $limit_date_str = '';
        if ($campaign['from_date'] ?? '') {
            $from_date_str = date('Y-m-d', $campaign['from_date']);
        }
        if ($campaign['to_date'] ?? '') {
            $to_date_str = date('Y-m-d', $campaign['to_date']);
        }
        if ($campaign['limit_date'] ?? '') {
            $limit_date_str = date('Y-m-d', $campaign['limit_date']);
        }

        foreach ($emails as $email) {
            $user = $cu->getUserByEmail($email['email']);
            $lang = $cus->getUserSetting($user['id'], "language") ?? 'en';
            $message = RatingTranslator::NewCampaign($lang).'<br/>'.$campaign['message'];
            $link = Configuration::get('public_url').'/rating/'.$id_space.'/campaign/'.$campaign_id.'/rate';
            $message .= '<br/><a href="'.$link.'">'.$link.'</a>';
            if ($limit_date_str) {
                $message .= '<br/><p>'.RatingTranslator::Deadline($lang).': '.$limit_date_str.'</p>';
            }
            $period = CoreTranslator::dateFromEn($from_date_str, $lang).' - '.CoreTranslator::dateFromEn($to_date_str, $lang);
            $me->sendEmail($from, $fromName, $email['email'], RatingTranslator::Survey($lang).': '.$period, $message, bcc:false, mailing:'campaign@rating');
        }
        Configuration::getLogger()->debug('[campaignRequest] '.$msg['campaign']['id'].' done!');
    }

    public function invoiceRequest($msg)
    {
        $id_space = $msg['space']['id'];
        $id_user = $msg['user']['id'];
        $cus = new CoreUserSettings();
        $lang = $cus->getUserSetting($id_user, "language") ?? 'en';
        $type = $msg['type'];
        $rid = $msg["request"]["id"];
        $cv = new CoreVirtual();
        Configuration::getLogger()->debug('[invoice][request]', ['type' => $type]);
        $cv->updateRequest($id_space, 'invoices', $rid, 'generating');
        try {
            switch ($type) {
                case GlobalInvoice::$INVOICES_GLOBAL_ALL:
                    $beginPeriod = $msg['period_begin'];
                    $endPeriod = $msg['period_end'];
                    $gi = new GlobalInvoice();
                    $found = $gi->invoiceAll($id_space, $beginPeriod, $endPeriod, $id_user, $lang);
                    $this->closeRequest($id_space, $rid, $found);
                    break;
                case GlobalInvoice::$INVOICES_GLOBAL_CLIENT:
                    $beginPeriod = $msg['period_begin'];
                    $endPeriod = $msg['period_end'];
                    $id_client = $msg['id_client'];
                    $gi = new GlobalInvoice();
                    $found = $gi->invoice($id_space, $beginPeriod, $endPeriod, $id_client, $id_user, $lang);
                    $this->closeRequest($id_space, $rid, $found);
                    break;
                case BookingInvoice::$INVOICES_BOOKING_ALL:
                    $beginPeriod = $msg['period_begin'];
                    $endPeriod = $msg['period_end'];
                    $gi = new BookingInvoice();
                    $found = $gi->invoiceAll($id_space, $beginPeriod, $endPeriod, $id_user, $lang);
                    $this->closeRequest($id_space, $rid, $found);
                    break;
                case BookingInvoice::$INVOICES_BOOKING_CLIENT:
                    $beginPeriod = $msg['period_begin'];
                    $endPeriod = $msg['period_end'];
                    $id_client = $msg['id_client'];
                    $gi = new BookingInvoice();
                    $found = $gi->invoiceClient($id_space, $beginPeriod, $endPeriod, $id_client, $id_user, $lang);
                    $this->closeRequest($id_space, $rid, $found);
                    break;
                case ServicesInvoice::$INVOICES_SERVICES_ORDERS_CLIENT:
                    $beginPeriod = $msg['period_begin'];
                    $endPeriod = $msg['period_end'];
                    $id_client = $msg['id_client'];
                    $gi = new ServicesInvoice();
                    $found = $gi->invoiceOrders($id_space, $beginPeriod, $endPeriod, $id_client, $id_user, $lang);
                    $this->closeRequest($id_space, $rid, $found);
                    break;
                case ServicesInvoice::$INVOICES_SERVICES_PROJECTS_CLIENT:
                    $beginPeriod = $msg['period_begin'];
                    $endPeriod = $msg['period_end'];
                    $id_client = $msg['id_client'];
                    $id_projects = $msg['id_projects'];
                    $gi = new ServicesInvoice();
                    $found = $gi->invoiceProjects($id_space, $beginPeriod, $endPeriod, $id_client, $id_user, $id_projects, $lang);
                    $this->closeRequest($id_space, $rid, $found);
                    break;
                default:
                    $cv->updateRequest($id_space, 'invoices', $rid, 'error: unknown request '.$type);
                    Configuration::getLogger()->error('[invoiceRequest] unknown request type', ['type' => $type]);
                    break;
            }
        } catch (Throwable $e) {
            $cv = new CoreVirtual();
            $cv->updateRequest($id_space, 'invoices', $rid, 'error: '.$e->getMessage());
            throw $e;
        }
    }

    /**
     * Handle message from rabbitmq
     *
     * @param PhpAmqpLib\Message\AMQPMessage $msg message (content in $msg->body in text format)
     */
    public function message($msg)
    {
        $this->logger->info('[message]', ['message' => $msg]);
        $reqStart = microtime(true);
        $ok = true;
        try {
            $data = json_decode($msg->body, true);
            $action = $data['action'] ?? -1;  // if action not in message, set to -1 and fail
            if (!is_int($action)) {
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
                    break;
                case Events::ACTION_INVOICE_EDIT:
                    $this->invoiceEdit($data);
                    break;
                case Events::ACTION_INVOICE_DELETE:
                    $this->invoiceDelete($data);
                    break;
                case Events::ACTION_INVOICE_REQUEST:
                    $this->invoiceRequest($data);
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
                case Events::ACTION_PLAN_EDIT:
                    $this->spacePlanEdit($data);
                    break;
                case Events::ACTION_STATISTICS_REQUEST:
                    $this->statRequest($data);
                    break;
                case Events::ACTION_RATING_CAMPAIGN_NEW:
                    $this->campaignRequest($data);
                    break;
                default:
                    $this->logger->error('[message] unknown message', ['action' => $data]);
                    $ok = false;
                    break;
            }
        } catch (Throwable $e) {
            $ok = false;
            $this->logger->error('[message] error', ['message' => $e->getMessage(), 'line' => $e->getLine(), 'stack' => $e->getTraceAsString()]);
        }
        $reqEnd = microtime(true);
        $this->prometheus($reqStart, $reqEnd, $action, $ok);
        $this->logger->info('[message] done!');
    }
}


class Events
{
    public const ACTION_SPACE_CREATE = 0;
    public const ACTION_SPACE_DELETE = 1;
    public const ACTION_SPACE_USER_JOIN = 2;
    public const ACTION_SPACE_USER_UNJOIN = 3;
    public const ACTION_SPACE_USER_ROLEUPDATE = 4;
    public const ACTION_USER_APIKEY = 5;
    public const ACTION_PLAN_EDIT = 6;
    public const ACTION_CAL_ENTRY_EDIT = 100;
    public const ACTION_CAL_ENTRY_REMOVE = 101;
    public const ACTION_HELPDESK_TICKET = 200;

    public const ACTION_INVOICE_EDIT = 300;
    public const ACTION_INVOICE_DELETE = 301;
    public const ACTION_INVOICE_REQUEST = 302;

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

    public const ACTION_STATISTICS_REQUEST = 800;

    public const ACTION_RATING_CAMPAIGN_NEW = 900;

    private static $connection;
    private static $channel;

    /**
     * Initialize client
     * @throws PfmParamException
     * @throws Exception
     */
    public static function getChannel()
    {
        if (self::$channel === null) {
            $amqpHost = Configuration::getOrThrow('amqp_host');
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
    public static function close()
    {
        if (self::$channel != null) {
            try {
                self::$channel->close();
                self::$connection->close();
            } catch (Throwable $e) {
                Configuration::getLogger()->error('[event] failed to close connection', ['error' => $e->getMessage()]);
            }
        }
        self::$channel = null;
    }

    /**
     * Sends a message to rabbitmq
     * @param array $message message to send
     */
    public static function send(array $message)
    {
        if (getenv("PFM_MODE") == "test") {
            Configuration::getLogger()->info('[event] test mode, call method', ['message' => $message]);
            $m = new EventHandler();
            $msg = new FakeMsg();
            $message['_user'] = $_SESSION['login'] ?? 'unknown';
            $msg->body  = json_encode($message);
            $m->message($msg);
            return;
        }
        try {
            $channel = self::getChannel();
            Configuration::getLogger()->debug('[event] send', ['message' => $message]);
            $message['_user'] = $_SESSION['login'] ?? 'unknown';
            $amqpMsg = new AMQPMessage(json_encode($message));
            $channel->basic_publish($amqpMsg, 'pfm_events', '');
        } catch (Exception $e) {
            Configuration::getLogger()->error('[event] error', ['message' => $e->getMessage(), 'line' => $e->getLine(), "file" => $e->getFile(),  'stack' => $e->getTraceAsString()]);
            return;
        }
    }
}
