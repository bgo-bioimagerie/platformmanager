<?php

require_once 'Modules/core/Model/CoreSpace.php';

require_once 'Modules/booking/Model/BkCalendarEntry.php';

class BookingcaldavController extends CorecookiesecureController {

    function discoveryAction($id_space) {
        $sm = new CoreSpace();
        $space = $sm->getSpace($id_space);
        $plan = new CorePlan($space['plan'], $space['plan_expire']);
        if(!$plan->hasFlag(CorePlan::FLAGS_CALDAV)) {
            throw new PfmParamException('Caldav not available in your space plan');
        }
        header('Allow: OPTIONS,GET,REPORT,PROPFIND');
        header('DAV: 2, calendar-access');
    }


    function propfindAction($id_space) {
        $allowed = true;
        $id_user = $this->auth();

        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if(!$plan->hasFlag(CorePlan::FLAGS_CALDAV)) {
            Configuration::getLogger()->debug('Caldav not available in your space plan');
            $allowed = false;
        }
        if($id_user == 0) {
            http_response_code(401);
            header('Content-Type: text/xml');
            echo sprintf('<?xml version="1.0" encoding="utf-8" ?>
            <multistatus xmlns:d="DAV:" xmlns:CS="http://calendarserver.org/ns/">
                <response>
                    <href>/caldav/%s/</href>
                    <propstat>
                        <prop/>
                        <status>HTTP/1.1 401 Unauthorized</status>
                    </propstat>
                </response>
            </multistatus>
            ', $id_space);
            return;
        }
        if(!$allowed) {
            http_response_code(403);
            header('Content-Type: text/xml');
            echo sprintf('<?xml version="1.0" encoding="utf-8" ?>
            <multistatus xmlns:d="DAV:" xmlns:CS="http://calendarserver.org/ns/">
                <response>
                    <href>/caldav/%s/</href>
                    <propstat>
                        <prop/>
                        <status>HTTP/1.1 403 Forbidden</status>
                    </propstat>
                </response>
            </multistatus>
            ', $id_space);
            return;
        }
        $doc = new SimpleXMLElement(file_get_contents('php://input'));
        Configuration::getLogger()->debug("[caldav][propfind]", ['doc' => $doc->asXML()]);
        $doc->registerXPathNamespace('a', 'DAV:');
        $prop = $doc->xpath('a:prop');
        $result_props = [];
        foreach($prop as $node){
            Configuration::getLogger()->debug('[caldav][propfind]', ['tag' => $node->tag]);
            switch ($node->tag) {
                case 'current-user-principal':
                    $result_props[] = '
                        <D:current-user-principal>
                            <D:href>/caldav/'.$id_space.'/</D:href>
                        </D:current-user-principal>';
                    break;
                case 'current-user-privilege-set':
                    $result_props[] = '
                        <D:current-user-privilege-set>
                            <D:privilege><D:read/></D:privilege>
                        </D:current-user-privilege-set>';
                    break;
                case 'getctag':
                    Configuration::getLogger()->debug('[caldav] get ctag');
                    $bm = new BkCalendarEntry();
                    
                    $updates = $bm->lastUser($id_space, $id_user);
                   
                    $eTag = 0;
                    Configuration::getLogger()->debug('[caldav] get ctag', ['u' => $updates]);
                    if($updates) {
                        $eTag = max(intval($updates['last_update']), intval($updates['last_delete']), intval($updates['last_start']));
                    }
                    $result_props[] = sprintf('<CS:getctag>%s<CS:getctag>', $eTag);
                    break;
                case 'displayname':
                    $result_props[] = sprintf('<D:displayname>%s<D:displayname>', 'Platform Manager bookings');
                    break;
                default:
                    break;
            }
        }
        $data = sprintf('<?xml version="1.0" encoding="utf-8" ?>
            <multistatus xmlns:d="DAV:" xmlns:CS="http://calendarserver.org/ns/">
                <response>
                    <href>/caldav/%s/</href>
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
        header('Content-Type: text/xml');
        echo $data;
    }

    function auth(){
        $um  = new CoreUser();
        if(!isset($_SERVER['PHP_AUTH_USER'])) {
            return 0;
        }
        $login = $_SERVER['PHP_AUTH_USER'];
        $pwd = $_SERVER['PHP_AUTH_PW'];
        if ($um->isLocalUser($login)) {
            Configuration::getLogger()->debug('[auth] local user', ['user' => $login]);
            $um->connect($login, $pwd);
        } else {
            // search for LDAP account
            Configuration::getLogger()->debug('[auth] check ldap', ['ldap' => CoreLdapConfiguration::get('ldap_use', 0)]);
            if (CoreLdapConfiguration::get('ldap_use', 0)) {
                Configuration::getLogger()->debug('[auth] ldap user', ['user' => $login]);
                $modelLdap = new CoreLdap();
                $ldapResult = $modelLdap->getUser($login, $pwd);
                if ($ldapResult == "error") {
                    Configuration::getLogger()->debug("[caldav] auth error", ['user' => $login]);
                    return 0;
                }
            }
        }

        try {
            $user = $um->getUserBylogin($login);
        } catch(Exception) {
            Configuration::getLogger()->debug('[caldav] user not found', ['login' => $login]);
        }
        return $user ? $user['idUser'] : 0;
    }

    function reportAction($id_space) {
        $id_user = $this->auth();

        try {
            $this->checkAuthorizationMenuSpace("booking", $id_space, $id_user);
        } catch(Exception) {
            http_response_code(401);
            header('Content-Type: text/xml');
            header('WWW-Authenticate: basic');
            echo sprintf('<?xml version="1.0" encoding="utf-8" ?>
            <multistatus xmlns:d="DAV:" xmlns:CS="http://calendarserver.org/ns/">
                <response>
                    <href>/caldav/%s/</href>
                    <propstat>
                        <prop/>
                        <status>HTTP/1.1 401 Unauthorized</status>
                    </propstat>
                </response>
            </multistatus>
            ', $id_space);
            return;
        }
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if(!$plan->hasFlag(CorePlan::FLAGS_CALDAV)) {
            throw new PfmParamException('Caldav not available in your space plan');
        }

        $input = file_get_contents('php://input');
        $doc = new SimpleXMLElement($input);
        Configuration::getLogger()->debug("[caldav][report]", ['user' => $id_user, 'doc' => $doc->asXML(), 'name' => $doc->getName()]);


        Configuration::getLogger()->debug("[caldav] get bookings for user");
        $fromTS = 0;
        $toTS = 0;
        $url = '';
        $bm = new BkCalendarEntry();

        $doc->registerXPathNamespace('a', 'urn:ietf:params:xml:ns:caldav');
        if ($doc->getName() == 'calendar-query'){
            $filter = $doc->xpath('a:filter');
            if($filter) {
                $filter[0]->registerXPathNamespace('a', 'urn:ietf:params:xml:ns:caldav');
                $comp_filter = $filter[0]->xpath('a:comp-filter');

                foreach($comp_filter as $cf){
                    $cf->registerXPathNamespace('a', 'urn:ietf:params:xml:ns:caldav');
                    $tr = $cf->xpath('//a:time-range');
                    foreach ($tr as $range) {
                        $trs = $range['start'];
                        $tre = $range['end'];
                        $fromTS = DateTime::createFromFormat('Ymd\THisP', $trs)->getTimestamp();
                        $toTS = DateTime::createFromFormat('Ymd\THisP', $tre)->getTimestamp();
                    }
                }
                $updates = $bm->lastUserPeriod($id_space, $id_user, $fromTS, $toTS);
                   
                $eTag = 0;
                if($updates) {
                    $eTag = max(intval($updates['last_update']), intval($updates['last_delete']), intval($updates['last_start']));
                }

                $data = sprintf('<?xml version="1.0" encoding="utf-8" ?>
                    <multistatus xmlns="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
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
                ', $id_space, $fromTS, $toTS, $eTag);
                http_response_code(207);
                header('Content-Type: text/xml');
                echo $data;
                return;
            }

        }
        if($doc->getName() == 'calendar-multiget'){
                foreach($doc->children('DAV:') as $child) {
                    if($child->getName() != 'href') {
                        continue;
                    }
                    $url = (string)$child;
                    $urlElts = explode('/', $url);
                    $info = $urlElts[count($urlElts)-1];
                    $queryElts = explode('-', str_replace('.ics', '', $info));
                    $fromTS = intval($queryElts[0]);
                    $toTS = intval($queryElts[1]);
                }

        }

        $updates = $bm->lastUserPeriod($id_space, $id_user, $fromTS, $toTS);
        $eTag = 0;

        if($updates) {
            $eTag = max($updates['last_update'], $updates['last_delete'], $updates['last_start']);
        }
        $events = '';
        
        $bookings = $bm->getUserPeriodBooking($id_space, $id_user, $fromTS, $toTS);
        foreach ($bookings as $booking) {
            $start = date('Ymd', $booking['start_time']).'T'.date('His', $booking['start_time']).'Z';
            $end = date('Ymd', $booking['end_time']).'T'.date('His', $booking['end_time']).'Z';
            $desc = $booking['resource_name'].' - '.$booking['short_description'] ?? '';
            $events .= sprintf('BEGIN:VEVENT
UID:%s
SUMMARY:%s
DESCRIPTION:%s
DTSTAMP:%s
DTSTART:%s
DTEND:%s
STATUS:CONFIRMED
END:VEVENT
', $booking['id'], $booking['resource_name'], $desc, $start, $start, $end);
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
        header('Content-Type: text/xml');
        echo $data;
    }


}

?>