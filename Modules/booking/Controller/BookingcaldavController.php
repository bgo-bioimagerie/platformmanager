<?php

require_once 'Modules/core/Model/CoreSpace.php';

require_once 'Modules/booking/Model/BkCalendarEntry.php';

class BookingcaldavController  extends CorecookiesecureController {

    function discoveryAction($id_space) {
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if(!$plan->hasFlag(CorePlan::FLAGS_CALDAV)) {
            throw new PfmParamException('Caldav not available in your space plan');
        }
        header('Allow: OPTIONS,GET,REPORT,PROPFIND');
        header('DAV: 2, calendar-access');
    }


    function propfindAction($id_space) {
        $id_user = $this->auth();
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if(!$plan->hasFlag(CorePlan::FLAGS_CALDAV)) {
            throw new PfmParamException('Caldav not available in your space plan');
        }
        $doc = new SimpleXMLElement(file_get_contents('php://input'));
        Configuration::getLogger()->debug("[caldav][propfind]", ['doc' => $doc->asXML()]);
        $doc->registerXPathNamespace('a', 'DAV:');
        $prop = $doc->xpath('a:prop');
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
                    
                    $updates = $bm->lastUser($id_space, $id_user);
                   
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
                    return "Cannot connect to ldap using the given login and password";
                } else {
                    // update the user infos
                    $um->setExtBasicInfo($login, $ldapResult["name"], $ldapResult["firstname"], $ldapResult["mail"], 1);
                    $userInfo = $um->getUserByLogin($login);
                    if(!$userInfo['apikey']) {
                        $um->newApiKey($userInfo['idUser']);
                    }
                    $um->isActive($login);
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
                    <href>/caldav/%s</href>
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
        // $m = new CoreSpace();
        // $role = $m->getUserSpaceRole($id_space, $_SESSION['id_user']);
        $input = file_get_contents('php://input');
        $doc = new SimpleXMLElement($input);
        Configuration::getLogger()->debug("[caldav][report]", ['user' => $id_user, 'doc' => $doc->asXML(), 'name' => $doc->getName()]);


        Configuration::getLogger()->debug("[caldav] get bookings for user");
        $fromTS = 0;
        $toTS = 0;
        //  $xmlRoot = $doc->tag;
        $url = '';
        $bm = new BkCalendarEntry();

        /*
        foreach($doc->getDocNamespaces() as $strPrefix => $strNamespace) {
            if(strlen($strPrefix)==0) {
                $strPrefix="a"; //Assign an arbitrary namespace prefix.
            }
            $doc->registerXPathNamespace($strPrefix,$strNamespace);
        }
        */
        $doc->registerXPathNamespace('a', 'urn:ietf:params:xml:ns:caldav');
        Configuration::getLogger()->debug('?????????? name', ['xml' => $doc->getName()]);
        if ($doc->getName() == 'calendar-query'){
            Configuration::getLogger()->debug('??????????', ['xml' => 'calendar-query']);
            $filter = $doc->xpath('a:filter');
            if($filter) {
                $filter[0]->registerXPathNamespace('a', 'urn:ietf:params:xml:ns:caldav');
                Configuration::getLogger()->debug('?????????? filter found', ['xml' => $filter[0]->asXML()]);
                $comp_filter = $filter[0]->xpath('a:comp-filter');
                if($comp_filter){
                    foreach ($comp_filter as $f) {
                        Configuration::getLogger()->debug('?????????? comp-filter found', ['xml' => $f->asXml()]);
                    }
                } else {
                    Configuration::getLogger()->debug('??????????', ['xml' => 'comp-filter not found']);
                }

                foreach($comp_filter as $cf){
                    Configuration::getLogger()->debug('?????????? comp-filter found', ['xml' => $f->asXml()]);
                    $cf->registerXPathNamespace('a', 'urn:ietf:params:xml:ns:caldav');
                    $tr = $cf->xpath('//a:time-range');
                    foreach ($tr as $range) {
                        Configuration::getLogger()->debug('?????????? time range found', ['xml' => $range->asXml(), 'start' => $range['start']]);
                        $trs = $range['start'];
                        $tre = $range['end'];
                        $fromTS = DateTime::createFromFormat('Ymd\ThisP', $trs)->getTimestamp();
                        $toTS = DateTime::createFromFormat('Ymd\ThisP', $tre)->getTimestamp();
                    }
                    /*
                    if ($cf->tag == 'a:time-range') {
                        $trs = $cf->start;
                        $tre = $cf->end;
                        $fromTS = DateTime::createFromFormat(DateTime::ISO8601, $trs)->getTimestamp();
                        $toTS = DateTime::createFromFormat(DateTime::ISO8601, $tre)->getTimestamp();
                    }
                    */
                }
                $updates = $bm->lastUserPeriod($id_space, $id_user, $fromTS, $toTS);
                Configuration::getLogger()->debug('???? last user period', ['p' => $updates]);
                   
                $eTag = 0;
                if($updates) {
                    $eTag = max(intval($updates['last_update']), intval($updates['last_delete']));
                }
                Configuration::getLogger()->debug('???? etag', ['p' => $eTag]);

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
                Configuration::getLogger()->debug('???????? caldav res', ['xml' => $data]);
                http_response_code(207);
                header('Content-Type: text/xml');
                echo $data;
                return;
            }

        }
        if($doc->getName() == 'calendar-multiget'){
                Configuration::getLogger()->debug('????, multti get', ['c' => $doc->children('DAV:')->asXML()]);
                foreach($doc->children('DAV:') as $child) {
                    Configuration::getLogger()->debug('????, multti get', ['x' => $child->asXML(), 'n' => $child->getName()]);
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
        Configuration::getLogger()->debug('???????? get booking between', ['f' => $fromTS, 't' => $toTS]);
        $updates = $bm->lastUserPeriod($id_space, $id_user, $fromTS, $toTS);
        $eTag = 0;
        if($updates) {
            $eTag = max($updates['last_update'], $updates['last_delete']);
        }
        $events = '';
        $bookings = [];
        if($fromTS && $toTS) {
            $bookings = $bm->getUserPeriodBooking($id_space, $id_user, $fromTS, $toTS);
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
END::VEVENT', $booking['id'], $booking['resource_name'], $start, $start, $end);
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
        Configuration::getLogger()->debug('???????? caldav res', ['xml' => $data]);
        http_response_code(207);
        header('Content-Type: text/xml');
        echo $data;
    }


}

?>