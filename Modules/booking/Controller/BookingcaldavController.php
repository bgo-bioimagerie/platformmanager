<?php

require_once 'Modules/core/Model/CoreSpace.php';

require_once 'Modules/booking/Model/BkCalendarEntry.php';

class BookingcaldavController extends CorecookiesecureController
{
    public function discoveryAction($idSpace=0)
    {
        if ($idSpace) {
            $sm = new CoreSpace();
            $space = $sm->getSpace($idSpace);
            $plan = new CorePlan($space['plan'], $space['plan_expire']);
            if (!$plan->hasFlag(CorePlan::FLAGS_CALDAV)) {
                throw new PfmParamException('Caldav not available in your space plan');
            }
        }
        header('Allow: OPTIONS,REPORT,PROPFIND');
        header('DAV: 2, calendar-access');
    }



    public function propfindAction($idSpace, $id_cal=0)
    {
        $allowed = true;
        $idUser = $this->auth();

        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if (!$plan->hasFlag(CorePlan::FLAGS_CALDAV)) {
            Configuration::getLogger()->debug('Caldav not available in your space plan');
            $allowed = false;
        }
        if ($idUser == 0) {
            http_response_code(401);
            header('Content-Type: text/xml');
            header('WWW-Authenticate: Basic realm="server"');
            echo sprintf('<?xml version="1.0" encoding="utf-8" ?>
            <multistatus xmlns:d="DAV:" xmlns:CS="http://calendarserver.org/ns/">
                <response>
                    <href>/caldav/%s/%s/</href>
                    <propstat>
                        <prop/>
                        <status>HTTP/1.1 401 Unauthorized</status>
                    </propstat>
                </response>
            </multistatus>
            ', $idSpace, $id_cal);
            return;
        }
        if (!$allowed) {
            http_response_code(403);
            header('Content-Type: text/xml');
            echo sprintf('<?xml version="1.0" encoding="utf-8" ?>
            <multistatus xmlns:d="DAV:" xmlns:CS="http://calendarserver.org/ns/">
                <response>
                    <href>/caldav/%s/%s/</href>
                    <propstat>
                        <prop/>
                        <status>HTTP/1.1 403 Forbidden</status>
                    </propstat>
                </response>
            </multistatus>
            ', $idSpace, $id_cal);
            return;
        }

        $depth = $_SERVER['HTTP_DEPTH'] ?? '0';

        $doc = new SimpleXMLElement(file_get_contents('php://input'));
        Configuration::getLogger()->debug("[caldav][propfind]", ['depth' => $depth, 'doc' => $doc->asXML()]);
        $doc->registerXPathNamespace('a', 'DAV:');
        $doc->registerXPathNamespace('C', 'urn:ietf:params:xml:ns:caldav');
        $doc->registerXPathNamespace('cs', 'http://calendarserver.org/ns/');
        //$prop = $doc->xpath('a:prop');
        $result_props = [];
        $currentUserPrincipal = $doc->xpath('//a:current-user-principal');
        if (!empty($currentUserPrincipal)) {
            $result_props[] = '
                        <d:current-user-principal>
                            <d:href>/caldav/'.$idSpace.'/'.$id_cal.'/</d:href>
                        </d:current-user-principal>';
        }
        $currentUserPrivilegeSet = $doc->xpath('//a:current-user-privilege-set');
        if (!empty($currentUserPrivilegeSet)) {
            $result_props[] = '
            <d:current-user-privilege-set>
                <d:privilege><d:read/></d:privilege>
            </d:current-user-privilege-set>';
        }


        $resourceType = $doc->xpath('//a:resourcetype');
        if (!empty($resourceType)) {
            if ($depth==1) {
                $result_props[] = '<d:resourcetype><cal:calendar/></d:resourcetype>';
            } else {
                $result_props[] = '<d:resourcetype><d:collection/><cal:calendar/></d:resourcetype>';
            }
        }

        $calendarHomeSet = $doc->xpath('//C:calendar-home-set');
        if (!empty($calendarHomeSet)) {
            $result_props[] = '
            <C:calendar-home-set>
                <d:href>/caldav/'.$idSpace.'/1/</d:href>
            </C:calendar-home-set>';
        }

        $supportedReportSet = $doc->xpath('//a:supported-report-set');
        if (!empty($supportedReportSet)) {
            $result_props[] = '
            <d:supported-report-set>
                <d:supported-report>
                    <d:report>
                        <C:calendar-multiget />
                    </d:report>
                </d:supported-report>
                <d:supported-report>
                    <d:report>
                        <C:calendar-query />
                    </d:report>
                </d:supported-report>
            </d:supported-report-set>';
        }

        $supportedCalendarComponentSet = $doc->xpath('//C:supported-calendar-component-set');
        if (!empty($supportedCalendarComponentSet)) {
            $result_props[] = '
            <C:supported-calendar-component-set>
                <C:comp name="VEVENT" />
            </C:supported-calendar-component-set>';
        }


        $contentTag = $doc->xpath('//a:getcontenttype');
        if (!empty($contentTag)) {
            if ($depth==0) {
                $result_props[] = '<d:getcontenttype>httpd/unix-directory</d:getcontenttype>';
            } else {
                $result_props[] = '<d:getcontenttype>text/calendar;charset=utf-8;component=vevent</d:getcontenttype>';
            }
        }

        $geteTag = $doc->xpath('//a:getetag');
        if (!empty($geteTag) && $depth==1) {
            Configuration::getLogger()->debug('[caldav] get etag');
            $bm = new BkCalendarEntry();

            $updates = $bm->lastUser($idSpace, $idUser);

            $eTag = 0;
            Configuration::getLogger()->debug('[caldav] get etag', ['u' => $updates]);
            if ($updates) {
                $eTag = max(intval($updates['last_update']), intval($updates['last_delete']), intval($updates['last_start']));
            }
            $result_props[] = sprintf('<d:getetag>"%s"</d:getetag>', $eTag);
        }
        $cTag = $doc->xpath('//cs:getctag');
        if (!empty($cTag)) {
            Configuration::getLogger()->debug('[caldav] get ctag');
            $bm = new BkCalendarEntry();

            $updates = $bm->lastUser($idSpace, $idUser);

            $eTag = 0;
            Configuration::getLogger()->debug('[caldav] get etag', ['u' => $updates]);
            if ($updates) {
                $eTag = max(intval($updates['last_update']), intval($updates['last_delete']), intval($updates['last_start']));
            }
            $result_props[] = sprintf('<d:getctag>"%s"</d:getctag>', $eTag);
        }

        $displayName = $doc->xpath('//a:displayname');
        if (!empty($displayName)) {
            if ($depth == 1) {
                $result_props[] = '<d:displayname>bookings</d:displayname>';
            } else {
                $result_props[] = sprintf('<d:displayname>%s</d:displayname>', $this->currentSpace['name']);
            }
        }

        $extra_response = [];
        if (1==0&& $depth==1 && !empty($resourceType)) {
            $extra_response[] = sprintf('<d:response>
            <d:href>/caldav/%s/0/</d:href>
            <d:propstat>
                <d:prop>
                    <d:getcontenttype>httpd/unix-directory</d:getcontenttype>
                    <d:resourcetype><d:collection/><cal:calendar/></d:resourcetype>
                </d:prop>
                <d:status>HTTP/1.1 200 OK</d:status>
            </d:propstat>
        </d:response>', $idSpace);
        }
        $data = sprintf('<?xml version="1.0" encoding="utf-8" ?>
            <d:multistatus xmlns:d="DAV:" xmlns:cs="http://calendarserver.org/ns/" xmlns:C="urn:ietf:params:xml:ns:caldav" xmlns:cal="urn:ietf:params:xml:ns:caldav">
                <d:response>
                    <d:href>/caldav/%s/%s/</d:href>
                    <d:propstat>
                        <d:prop>
                        %s
                        </d:prop>
                        <d:status>HTTP/1.1 200 OK</d:status>
                    </d:propstat>
                </d:response>
                %s
            </d:multistatus>
        ', $idSpace, $id_cal, implode("\n", $result_props), implode("\n", $extra_response));
        http_response_code(207);
        header('Content-Type: text/xml');
        echo $data;
    }

    protected function auth()
    {
        $um  = new CoreUser();
        if (!isset($_SERVER['PHP_AUTH_USER'])) {
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
        } catch (PfmAuthException) {
            Configuration::getLogger()->debug('[caldav] user not found', ['login' => $login]);
        }
        return $user ? $user['idUser'] : 0;
    }

    public function reportAction($idSpace)
    {
        $idUser = $this->auth();

        try {
            $this->checkAuthorizationMenuSpace("booking", $idSpace, $idUser);
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
            ', $idSpace);
            return;
        }
        $plan = new CorePlan($this->currentSpace['plan'], $this->currentSpace['plan_expire']);
        if (!$plan->hasFlag(CorePlan::FLAGS_CALDAV)) {
            throw new PfmParamException('Caldav not available in your space plan');
        }

        $input = file_get_contents('php://input');
        $doc = new SimpleXMLElement($input);
        Configuration::getLogger()->debug("[caldav][report]", ['user' => $idUser, 'doc' => $doc->asXML(), 'name' => $doc->getName()]);


        Configuration::getLogger()->debug("[caldav] get bookings for user");
        $fromTS = 0;
        $toTS = 0;
        $url = '';
        $bm = new BkCalendarEntry();

        $doc->registerXPathNamespace('a', 'urn:ietf:params:xml:ns:caldav');
        if ($doc->getName() == 'calendar-query') {
            $filter = $doc->xpath('a:filter');
            if ($filter) {
                $filter[0]->registerXPathNamespace('a', 'urn:ietf:params:xml:ns:caldav');
                $comp_filter = $filter[0]->xpath('a:comp-filter');

                foreach ($comp_filter as $cf) {
                    $cf->registerXPathNamespace('a', 'urn:ietf:params:xml:ns:caldav');
                    $tr = $cf->xpath('//a:time-range');
                    foreach ($tr as $range) {
                        $trs = $range['start'];
                        $tre = $range['end'];
                        $fromTS = DateTime::createFromFormat('Ymd\THisP', $trs)->getTimestamp();
                        $toTS = DateTime::createFromFormat('Ymd\THisP', $tre)->getTimestamp();
                    }
                }
            }
        }
        foreach ($doc->children('DAV:') as $child) {
            if ($child->getName() != 'href') {
                continue;
            }
            $url = (string)$child;
            $urlElts = explode('/', $url);
            $info = $urlElts[count($urlElts)-1];
            $queryElts = explode('-', str_replace('.ics', '', $info));
            if (count($queryElts) == 2) {
                $fromTS = intval($queryElts[0]);
                $toTS = intval($queryElts[1]);
            }
        }



        $updates = $bm->lastUserPeriod($idSpace, $idUser, $fromTS, $toTS);
        $eTag = 0;

        if ($updates) {
            $eTag = max($updates['last_update'], $updates['last_delete'], $updates['last_start']);
        }

        $bookings = $bm->getUserPeriodBooking($idSpace, $idUser, $fromTS, $toTS);
        $responses = '';
        foreach ($bookings as $booking) {
            $created = strtotime($booking['created_at']);
            $created = date('Ymd', $created).'T'.date('His', $created).'';
            $modified = strtotime($booking['updated_at']);
            $modified = date('Ymd', $modified).'T'.date('His', $modified).'';
            $start = date('Ymd', $booking['start_time']).'T'.date('His', $booking['start_time']).'';
            $end = date('Ymd', $booking['end_time']).'T'.date('His', $booking['end_time']).'';
            $desc = $booking['resource_name'];
            if ($booking['short_description']) {
                $desc .= ' - '.$booking['short_description'];
            }
            $event = sprintf('BEGIN:VEVENT
UID:%s
SUMMARY:%s
DESCRIPTION:%s
DTSTAMP:%s
DTSTART:%s
DTEND:%s
STATUS:CONFIRMED
LAST-MODIFIED: %s
END:VEVENT
', $booking['id'].'@pfm-bookings', $booking['resource_name'], $desc, $created, $start, $end, $modified);
            $url = sprintf("/caldav/%s/1/%d.ics", $idSpace, $booking['id']);
            $responses .= sprintf('<response>
<href>%s</href>
<propstat>
<prop>
<getetag>"%d"</getetag>
<C:calendar-data>BEGIN:VCALENDAR
PRODID:-//Platform Manager.//CalDAV Server//EN
VERSION:2.0
%sEND:VCALENDAR
</C:calendar-data>
</prop>
<status>HTTP/1.1 200 OK</status>
</propstat>
</response>
', $url, $modified, $event);
        }

        $data = sprintf('<?xml version="1.0" encoding="utf-8" ?>
        <multistatus xmlns="DAV:" xmlns:C="urn:ietf:params:xml:ns:caldav">
        %s
        </multistatus>
        ', $responses);
        http_response_code(207);
        header('Content-Type: text/xml');
        echo $data;
    }
}
