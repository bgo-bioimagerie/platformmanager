<?php

require_once 'Modules/core/Model/CoreSpace.php';

require_once 'Modules/booking/Model/BkCalendarEntry.php';

class CaldavcalController  extends CorecookiesecureController {

    function discoveryAction($id_space) {
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if($plan->hasFlag(CorePlan::FLAGS_CALDAV)) {
            throw new PfmParamException('Caldav not availabale in your space plan');
        }
        header('Allow: OPTIONS,GET,REPORT,PROPFIND');
        header('DAV: 2, calendar-access');
    }


    function propfindAction($id_space) {
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if($plan->hasFlag(CorePlan::FLAGS_CALDAV)) {
            throw new PfmParamException('Caldav not availabale in your space plan');
        }
        $doc = new SimpleXMLElement(file_get_contents('php://input'));
        Configuration::getLogger()->debug("[caldav][propfind]", ['doc' => $doc]);
        $prop = $doc->xpath('{DAV:}prop');
        $result_props = [];
        foreach($prop as $node){
            switch ($node->tag) {
                case 'current_user-privilege-set':
                    $result_props[] = '
                        <current-user-principal>
                            <D:unauthenticated/>
                        </current-user-principal>';
                    break;
                case 'getctag':
                    Configuration::getLogger()->debug('[caldav] get ctag');
                    $bm = new BkCalendarEntry();
                    
                    $updates = $bm->lastUser($id_space, $_SESSION['id_user']);
                   
                    $eTag = 0;
                    if($updates) {
                        $eTag = max($updates['last_update'], $updates['last_delete']);
                    }
                    $result_props[] = sprintf('<CS:getctag>%s<CS:getctag>', $eTag);
                    break;
                default:
                    break;
            }
        }
        $data = sprintf('<?xml version="1.0" encoding="utf-8" ?>
            <multistatus xmlns:d="DAV:" xmlns:CS="http://calendarserver.org/ns/">
                <response>
                    <href>/caldav/%s</href>
                    <propstat>
                        <prop>
                        %s
                        </prop>
                        <status>HTTP/1.1 200 OK</status>
                    </propstat>
                </response>
            </multistatus>
        ', $id_space, implode('', $result_props));
        http_response_code(207);
        header('Content-Type: application/xml');
        echo $data;
    }

    function reportAction($id_space) {
        if(isset($_SERVER['PHP_AUTH_USER'])) {
            $login = $_SERVER['PHP_AUTH_USER'];
            $pwd = $_SERVER['PHP_AUTH_PW'];
            if ($this->user->isLocalUser($login)) {
                $this->logger->debug('[auth] local user', ['user' => $login]);
                $this->user->connect($login, $pwd);
            } else {
                // search for LDAP account
                $this->logger->debug('[auth] check ldap', ['ldap' => CoreLdapConfiguration::get('ldap_use', 0)]);
                if (CoreLdapConfiguration::get('ldap_use', 0)) {
                    $this->logger->debug('[auth] ldap user', ['user' => $login]);
                    $modelLdap = new CoreLdap();
                    $ldapResult = $modelLdap->getUser($login, $pwd);
                    if ($ldapResult == "error") {
                        return "Cannot connect to ldap using the given login and password";
                    } else {
                        // update the user infos
                        $this->user->setExtBasicInfo($login, $ldapResult["name"], $ldapResult["firstname"], $ldapResult["mail"], 1);
                        $userInfo = $this->user->getUserByLogin($login);
                        if(!$userInfo['apikey']) {
                            $this->user->newApiKey($userInfo['idUser']);
                        }
                        $this->user->isActive($login);
                    }
                }
            }

        }
        try {
            $this->checkAuthorizationMenuSpace("caldav", $id_space, $_SESSION["id_user"] ?? 0);
        } catch(Exception) {
            http_response_code(401);
            header('Content-Type: application/xml');
            header('WWW-Authenticate: basic');
            echo sprintf('<?xml version="1.0" encoding="utf-8" ?>
            <multistatus xmlns:d="DAV:" xmlns:CS="http://calendarserver.org/ns/">
                <response>
                    <href>/caldav/%s</href>
                    <propstat>
                        <prop/>
                        <status>HTTP/1.1 401 Unauthorized</status>
                    </propstat>
                </response>
            </multistatus>
            ', $id_space);
        }
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if($plan->hasFlag(CorePlan::FLAGS_CALDAV)) {
            throw new PfmParamException('Caldav not availabale in your space plan');
        }
        // $m = new CoreSpace();
        // $role = $m->getUserSpaceRole($id_space, $_SESSION['id_user']);
        $doc = new SimpleXMLElement(file_get_contents('php://input'));
        Configuration::getLogger()->debug("[caldav][report]", ['doc' => $doc]);


        Configuration::getLogger()->debug("[caldav] get bookings for user");
        $fromTS = 0;
        $toTS = 0;
        $xmlRoot = $doc->tag;
        $url = '';
        $bm = new BkCalendarEntry();
        if (array_key_exists('calendar-entry', $xmlRoot)){
            $filter = $doc->xpath('{urn:ietf:params:xml:ns:caldav}filter');
            if($filter) {
                $comp_filter = $filter[0]->xpath('{urn:ietf:params:xml:ns:caldav}comp-filter');
                foreach($comp_filter as $cf){
                    if ($cf->tag == '{urn:ietf:params:xml:ns:caldav}time-range') {
                        $trs = $cf->start;
                        $tre = $cf->end;
                        $fromTS = DateTime::createFromFormat(DateTime::ISO8601, $trs)->getTimestamp();
                        $toTS = DateTime::createFromFormat(DateTime::ISO8601, $tre)->getTimestamp();
                    }
                }
                $updates = $bm->lastUserPeriod($id_space, $_SESSION['id_user'], $fromTS, $toTS);
                   
                $eTag = 0;
                if($updates) {
                    $eTag = max($updates['last_update'], $updates['last_delete']);
                }
                $data = sprintf('<?xml version="1.0" encoding="utf-8" ?>
                    <multistatus xmlns:d="DAV:" xmlns:CS="http://calendarserver.org/ns/">
                        <response>
                            <href>/caldav/%s/%d-%d.ics</href>
                            <propstat>
                                <prop>
                                <getetag>"%d"</getetag>
                                </prop>
                                <status>HTTP/1.1 200 OK</status>
                            </propstat>
                        </response>
                    </multistatus>
                ', $id_space, $fromTS, $toTS, implode('', $eTag));
                http_response_code(207);
                header('Content-Type: application/xml');
                echo $data;
                return;
            }

        }
        if(array_key_exists('calendar-multiget', $xmlRoot)){
                foreach($doc->children() as $child) {
                    if(!array_key_exists('href', $child->tag)) {
                        continue;
                    }
                    $url = $child->text;
                    $urlElts = explode('/', $url);
                    $info = $urlElts[count($urlElts)-1];
                    $queryElts = explode('-', str_replace('.ics', '', $info));
                    $fromTS = intval($queryElts[0]);
                    $toTS = intval($queryElts[1]);
                }

        }
        
        $updates = $bm->lastUserPeriod($id_space, $_SESSION['id_user'], $fromTS, $toTS);
        $eTag = 0;
        if($updates) {
            $eTag = max($updates['last_update'], $updates['last_delete']);
        }
        $events = '';
        $bookings = [];
        if($fromTS && $toTS) {
            $bookings = $bm->getUserPeriodBooking($id_space, $_SESSION['id_user'], $fromTS, $toTS);
        }
        foreach ($bookings as $booking) {
            $start = date('Ymd', $booking['start_time']).'T'.date('his', $booking['start_time']);
            $end = date('Ymd', $booking['end_time']).'T'.date('his', $booking['end_time']);
            $events .= sprintf('BEGIN:VEVENT
UID:%s
SUMMARY:%s
DESCRIPTION:
DTSTAMP:%s
DTSTART:%s
DTEND:%s
STATUS:CONFIRMED
END::VEVENT
', $booking['id'], $booking['resource_name'], $start, $end);
        }
        $ccalendar = '';
        if($events){
            $ccalendar = sprintf('<C:calendar-data>BEGIN:VCALENDAR
            VERSION:2.0
            PRODID:-//Platform Manager.//CalDAV Server//EN
            %s
            END:VCALENDAR
            </C:calendar-data>', $events);
        }
        
        $data = sprintf('<?xml version="1.0" encoding="utf-8" ?>
        <multistatus xmlns="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
        <response>
        <href>%s</href>
        <propstat>
        <prop>
        <getetag>"%d"</getetag>
        %s
        </prop>
        <status>HTTP/1.1 200 OK</status>
        </propstat>
        </response>
        </multistatus>
        ', $url, $eTag, $ccalendar);
        http_response_code(207);
        header('Content-Type: application/xml');
        echo $data;
    }


}

?>