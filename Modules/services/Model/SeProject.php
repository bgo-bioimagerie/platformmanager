<?php

require_once 'Framework/Model.php';
require_once 'Modules/invoices/Model/InInvoice.php';

require_once 'Modules/clients/Model/ClClientUser.php';
require_once 'Modules/clients/Model/ClClient.php';

require_once 'Modules/services/Model/SePrice.php';
require_once 'Modules/services/Model/StockShelf.php';



/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class SeProject extends Model
{
    public function __construct()
    {
        $this->tableName = "se_project";
    }

    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `se_project` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `id_space` int(11) NOT NULL,
            `name` varchar(150) NOT NULL DEFAULT '',
            `id_resp` int(11) NOT NULL,
            `id_user` int(11) NOT NULL,
            `date_open` DATE,
            `date_close` DATE,
            `new_team` int(4) NOT NULL DEFAULT 1,
            `new_project` int(4) NOT NULL DEFAULT 1,
            `time_limit` varchar(100) NOT NULL DEFAULT '', 
            `id_origin` int(11) NOT NULL DEFAULT 0,
            `closed_by` int(11) NOT NULL DEFAULT 0,
            `in_charge` int(11) NOT NULL DEFAULT 0,
            `samplereturn` text,
            `samplereturndate` DATE,
            `id_sample_cabinet` int(11) NOT NULL DEFAULT 0,
            `samplestocked` int(1) NOT NULL DEFAULT 0,
            `samplescomment` text,
            PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);

        $this->addColumn('se_project', 'id_origin', 'int(11)', 0);
        $this->addColumn('se_project', 'closed_by', 'int(11)', 0);
        $this->addColumn('se_project', 'in_charge', 'int(11)', 0);
        $this->addColumn('se_project', 'samplereturn', 'TEXT', '');
        $this->addColumn('se_project', 'samplereturndate', 'date', '');
        $this->addColumn('se_project', 'id_sample_cabinet', 'int(11)', 0);
        $this->addColumn('se_project', 'samplestocked', 'int(1)', 0);
        $this->addColumn('se_project', 'samplescomment', 'TEXT', "");

        $sql2 = "CREATE TABLE IF NOT EXISTS `se_project_service` (
		    `id` int(11) NOT NULL AUTO_INCREMENT,
            `id_project` int(11) NOT NULL,
            `id_service` int(11) NOT NULL,
            `date` date,
            `quantity` varchar(255) NOT NULL,
            `comment` varchar(255) NOT NULL,
            `id_invoice` int(11) NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql2);

        $sql3 = "CREATE TABLE IF NOT EXISTS `se_project_user` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `id_user` int(11) NOT NULL,
            `id_project` int(11) NOT NULL,
            `id_space` int(11) NOT NULL,
            PRIMARY KEY (`id`)
        );";

        $this->runRequest($sql3);
    }

    public function setSampleStock($idSpace, $id, $samplestocked, $id_cabinet, $cabinetcomment)
    {
        $sql = "UPDATE se_project SET samplestocked=?, id_sample_cabinet=?, samplescomment=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($samplestocked, $id_cabinet, $cabinetcomment, $id, $idSpace));
    }

    public function sampleReturn($idSpace, $id, $samplereturn, $samplereturndate)
    {
        $sql = "UPDATE se_project SET samplereturn=?, samplereturndate=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($samplereturn, $samplereturndate, $id, $idSpace));
    }

    public function closeProject($idSpace, $id, $date_close, $closed_by)
    {
        if ($date_close == "") {
            $date_close = null;
        }
        $sql = "UPDATE se_project SET date_close=?, closed_by=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($date_close, $closed_by, $id, $idSpace));
    }

    public function getRespsPeriod($idSpace, $periodStart, $periodEnds)
    {
        $sql = "SELECT DISTINCT id_resp "
                . " FROM se_project "
                . " WHERE deleted=0 AND id_space=? AND date_open<=? AND (date_close is null OR date_close>=?) ";
        $req = $this->runRequest($sql, array($idSpace, $periodEnds, $periodStart));
        $data = $req->fetchAll();
        $modelClient = new ClClient();
        for ($i = 0; $i < count($data); $i++) {
            $clientInfo = $modelClient->get($idSpace, $data[$i]["id_resp"]);
            $data[$i]["name"] = $clientInfo["name"];
            $data[$i]["email"] = $clientInfo["email"];
        }
        return $data;
    }

    public function mergeUsers($users)
    {
        for ($i = 1; $i < count($users); $i++) {
            $sql = "UPDATE se_project SET id_resp=? WHERE id_resp=?";
            $this->runRequest($sql, array($users[0], $users[$i]));

            $sql2 = "UPDATE se_project SET id_user=? WHERE id_user=?";
            $this->runRequest($sql2, array($users[0], $users[$i]));
        }
    }

    public function setSampleReturn($idSpace, $id, $samplereturn, $samplereturndate)
    {
        if ($samplereturndate == "") {
            $samplereturndate = null;
        }
        $sql = "UPDATE se_project SET samplereturn=?, samplereturndate=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($samplereturn, $samplereturndate, $id, $idSpace));
    }

    public function getIdFromName($name, $idSpace)
    {
        $sql = "SELECT id FROM se_project WHERE name=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $idSpace));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function allOpenedProjects($idSpace)
    {
        $sql = "SELECT * FROM se_project WHERE id_space=? AND deleted=0 AND date_close is null ORDER BY date_open ASC;";
        $projects = $this->runRequest($sql, array($idSpace))->fetchAll();
        return $projects;
    }

    public function allPeriodProjects($idSpace, $periodStart, $periodEnd)
    {
        $sql = "SELECT * FROM se_project WHERE deleted=0 AND id_space=? AND ("
                . " ( date_open<=? AND date_close>=? AND date_close<=? ) "
                . " OR ( date_open>=? AND date_open<=? AND date_close>=? AND date_close<=? ) "
                . " OR ( date_open>=? AND date_open<=? AND date_close>=? ) "
                . " OR ( date_open<=? AND date_close>=?) "
                . " OR date_close is null "
                . ") ORDER BY date_open ASC;";
        $projects = $this->runRequest($sql, array($idSpace,
                    $periodStart, $periodStart, $periodEnd,
                    $periodStart, $periodEnd, $periodStart, $periodEnd,
                    $periodStart, $periodEnd, $periodEnd,
                    $periodStart, $periodEnd
                ))->fetchAll();

        $modelUser = new CoreUser();

        for ($i = 0; $i < count($projects); $i++) {
            $projects[$i]["user_name"] = $modelUser->getUserFullName($projects[$i]['id_user']);
            $projects[$i]["resp_name"] = $modelUser->getUserFullName($projects[$i]['id_resp']);
        }
        return $projects;
    }

    public function allOpenedProjectsByInCharge($idSpace, $id_incharge)
    {
        $sql = "SELECT * FROM se_project WHERE deleted=0 AND id_space=? AND in_charge=? AND date_close is null ORDER BY date_open ASC;";
        $projects = $this->runRequest($sql, array($idSpace, $id_incharge))->fetchAll();
        return $projects;
    }

    public function allPeriodProjectsByInCharge($idSpace, $id_incharge, $periodStart, $periodEnd)
    {
        $sql = "SELECT * FROM se_project WHERE deleted=0 AND id_space=? AND in_charge=? AND date_open<=? AND (date_close is null OR date_close>=?) ORDER BY date_open ASC;";
        $projects = $this->runRequest($sql, array($idSpace, $id_incharge, $periodEnd, $periodStart))->fetchAll();
        return $projects;
    }

    public function deleteEntry($idSpace, $id)
    {
        $sql = "DELETE FROM se_project_service WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }

    /**
     * Gets a list of years from list of project dates
     * Can specify a breakpoint $periodEnd in order to
     *  - append one year from result
     *      if greatest date day and month > $periodEnd day and month
     * - remove first year from result
     *      if smaller date day and month < $periodEnd day and month
     * @param array $data list of dates
     * @param string $periodEnd
     *
     * @return array list of years
     *
     */
    protected function extractYears($data, $periodEnd = null)
    {
        if (!empty($data)) {
            $secureDate = date('Y-m-d');
            $firstDate = date_parse($data[0][0] ?? $secureDate);
            $lastDate = date_parse(end($data)[0] ?? $secureDate);
            $firstYear = $firstDate['year'];
            $lastYear = $lastDate['year'];

            if ($periodEnd) {
                $parsed_periodEnd = date_parse($periodEnd);
                $firstDate_WoYear = mktime(0, 0, 0, $firstDate['month'], $firstDate['day']);
                $lastDate_WoYear = mktime(0, 0, 0, $lastDate['month'], $lastDate['day']);
                $periodEnd_WoYear = mktime(0, 0, 0, $parsed_periodEnd['month'], $parsed_periodEnd['day']);

                if ($lastDate_WoYear > $periodEnd_WoYear) {
                    $lastYear += 1;
                }

                // increment first year if first project closed after periodEnd and and first year is not the current one
                if (($firstDate_WoYear > $periodEnd_WoYear) && ($firstYear != date('Y'))) {
                    $firstYear += 1;
                }
            }
            $years = array();
            for ($i = $firstYear; $i <= $lastYear; $i++) {
                $years[] = $i;
            }
            return $years;
        }
        return array();
    }

    public function closedProjectsPeriods($idSpace, $periodEnd)
    {
        $sql = "SELECT date_close FROM se_project WHERE date_close is not null AND id_space=? AND deleted=0 ORDER BY date_close ASC";
        $data = $this->runRequest($sql, array($idSpace))->fetchAll();
        return $this->extractYears($data, $periodEnd);
    }

    public function closedProjectsYears($idSpace)
    {
        $sql = "SELECT date_close FROM se_project WHERE deleted=0 AND date_close is not null AND id_space=? ORDER BY date_open ASC";
        $data = $this->runRequest($sql, array($idSpace))->fetchAll();
        return $this->extractYears($data);
    }

    public function allProjectsYears($idSpace)
    {
        $sql = "SELECT date_open from se_project WHERE deleted=0 AND id_space=? ORDER BY date_open ASC";
        $data = $this->runRequest($sql, array($idSpace))->fetchAll();
        return $this->extractYears($data);
    }

    public function getProjectsOpenedPeriodResp($idSpace, $beginPeriod, $endPeriod, $id_resp)
    {
        $sql = "SELECT id FROM se_project WHERE id_space=? AND deleted=0 AND date_close is null AND date_open>=? AND date_open<? AND id_resp=?";
        $req = $this->runRequest($sql, array($idSpace, $beginPeriod, $endPeriod, $id_resp))->fetchAll();
        $data = array();
        foreach ($req as $d) {
            $data[] = $d["id"];
        }
        return $data;
    }

    public function getServicesInvoice($idSpace, $id_invoice)
    {
        $sql = "SELECT id FROM se_project_service WHERE id_invoice=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_invoice, $idSpace))->fetchAll();
    }

    public function setServiceInvoice($idSpace, $id, $id_invoice)
    {
        $sql = "UPDATE se_project_service SET id_invoice=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_invoice, $id, $idSpace));
    }

    public function getResp($idSpace, $id_project)
    {
        $sql = "SELECT id_resp FROM se_project WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_project, $idSpace))->fetch();
        return $req[0];
    }

    public function getName($idSpace, $id_project)
    {
        $sql = "SELECT name FROM se_project WHERE id=? AND id_space=? AND deleted=0";
        $tmp = $this->runRequest($sql, array($id_project, $idSpace))->fetch();
        return $tmp ? $tmp[0] : null;
    }

    public function getNoInvoicesServices($idSpace, $id_project)
    {
        $sql = "SELECT * FROM se_project_service WHERE id_project=? AND id_invoice=0 AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_project, $idSpace))->fetchAll();
    }

    public function getOpenedProjectForList($idSpace)
    {
        $sql = "SELECT * FROM se_project WHERE date_close is null AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($idSpace))->fetchAll();

        $ids = array();
        $names = array();
        $ids[] = 0;
        $names[] = "...";

        $modelClient = new ClClient();
        foreach ($req as $r) {
            $ids[] = $r["id"];
            $names[] = $modelClient->getName($idSpace, $r['id_resp']) . ": " . $r["name"];
        }
        return array("ids" => $ids, "names" => $names);
    }

    public function setOrigin($idSpace, $id, $id_origin)
    {
        $sql = "UPDATE se_project SET id_origin=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_origin, $id, $idSpace));
    }

    public function setClosedBy($idSpace, $id, $idClose)
    {
        $sql = "UPDATE se_project SET closed_by=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($idClose, $id, $idSpace));
    }

    public function setInCharge($idSpace, $id, $id_visa)
    {
        $sql = "UPDATE se_project SET in_charge=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_visa, $id, $idSpace));
    }

    public function setEntry($idSpace, $id_entry, $id_project, $id_service, $date, $quantity, $comment, $id_invoice = 0)
    {
        if ($date == "") {
            $date = null;
        }
        if ($id_entry > 0) {
            //echo "update service: p:" . $id_project . ", s" . $id_service . ", date:" . $date . "<br/>";
            $sql = "UPDATE se_project_service SET quantity=?, comment=?, id_invoice=?, id_project=?, id_service=?, date=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($quantity, $comment, $id_invoice, $id_project, $id_service, $date, $id_entry, $idSpace));
            return $id_entry;
        } else {
            //echo "add service: p:" . $id_project . ", s" . $id_service . ", date:" . $date . "<br/>";
            $sql = "INSERT INTO se_project_service (id_project, id_service, date, quantity, comment, id_invoice, id_space) VALUES (?,?,?,?,?,?,?)";
            $this->runRequest($sql, array($id_project, $id_service, $date, $quantity, $comment, $id_invoice, $idSpace));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function setService($idSpace, $id_project, $id_service, $date, $quantity, $comment, $id_invoice = 0)
    {
        if ($date == "") {
            $date = null;
        }
        if ($this->isProjectService($idSpace, $id_project, $id_service, $date)) {
            //echo "update service: p:" . $id_project . ", s" . $id_service . ", date:" . $date . "<br/>";
            $sql = "UPDATE se_project_service SET quantity=?, comment=?, id_invoice=? WHERE id_project=? AND id_service=? AND date=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($quantity, $comment, $id_invoice, $id_project, $id_service, $date, $idSpace));
        } else {
            //echo "add service: p:" . $id_project . ", s" . $id_service . ", date:" . $date . "<br/>";
            $sql = "INSERT INTO se_project_service (id_project, id_service, date, quantity, comment, id_invoice, id_space) VALUES (?,?,?,?,?,?,?)";
            $this->runRequest($sql, array($id_project, $id_service, $date, $quantity, $comment, $id_invoice, $idSpace));
        }
    }

    public function addService($idSpace, $id_project, $id_service, $date, $quantity, $comment, $id_invoice = 0)
    {
        if ($date == "") {
            $date = null;
        }
        $sql = "INSERT INTO se_project_service (id_project, id_service, date, quantity, comment, id_invoice, id_space) VALUES (?,?,?,?,?,?,?)";
        $this->runRequest($sql, array($id_project, $id_service, $date, $quantity, $comment, $id_invoice, $idSpace));
    }

    public function removeUnsetServices($idSpace, $id_project, $servicesIds, $servicesDates)
    {
        $sql = "SELECT * FROM se_project_service WHERE id_project=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_project, $idSpace))->fetchAll();
        foreach ($data as $d) {
            $found = false;
            for ($i = 0; $i < count($servicesIds); $i++) {
                if ($servicesIds[$i] == $d["id_service"] && $servicesDates[$i] == $d["date"]) {
                    $found = true;
                    break;
                }
            }
            if (!$found) {
                //echo "delete service id: " . $d["id_service"] . ", date: " . $d["date"] . "<br/>";
                $sql = "DELETE FROM se_project_service WHERE id_project=? AND id_service=? AND date=? AND id_space=?";
                $this->runRequest($sql, array($id_project, $d["id_service"], $d["date"], $idSpace));
            }
        }
    }

    public function isProjectService($idSpace, $id_project, $id_service, $date)
    {
        $sql = "SELECT * FROM se_project_service WHERE id_project=? AND id_service=? AND date=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_project, $id_service, $date, $idSpace));
        if ($req->rowCount() >= 1) {
            return true;
        }
        return false;
    }

    public function getProjectServicesDefault($idSpace, $id_project)
    {
        $sql = "SELECT * FROM se_project_service WHERE id_project=? AND id_space=? AND deleted=0 ORDER BY date ASC";
        return $this->runRequest($sql, array($id_project, $idSpace))->fetchAll();
    }

    public function getProjectServicesBase($idSpace, $id_project)
    {
        $sql = "SELECT * FROM se_project_service WHERE id_project=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_project, $idSpace))->fetchAll();
        return $data;
    }

    public function getAllServices($idSpace)
    {
        $sql = "SELECT * FROM se_project_service WHERE id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($idSpace));
        return $req->fetchAll();
    }

    public function getProjectServices($idSpace, $id_project)
    {
        $sql = "SELECT * FROM se_project_service WHERE id_project=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_project, $idSpace))->fetchAll();
        $dates = array();
        $services = array();
        $quantities = array();
        $comments = array();
        foreach ($data as $d) {
            $dates[] = $d["date"];
            $services[] = $d["id_service"];
            $quantities[] = $d["quantity"];
            $comments[] = $d["comment"];
        }
        return array("services" => $services, "quantities" => $quantities, "dates" => $dates, "comments" => $comments);
    }

    public function getProjectServiceQuantity($idSpace, $id_project, $id_service)
    {
        $sql = "SELECT quantity FROM se_project_service WHERE id_project=? AND id_service=? AND id_space=? AND deleted=0";

        $req = $this->runRequest($sql, array($id_project, $id_service, $idSpace));
        if ($req->rowCount() == 1) {
            return $req->fetch();
        }
        return 0;
    }

    public function setProject($id, $idSpace, $name, $id_resp, $idUser, $date_open, $date_close, $new_team, $new_project, $time_limit)
    {
        if ($date_open == "") {
            $date_open = null;
        }
        if ($date_close == "") {
            $date_close = null;
        }
        if ($this->isProject($idSpace, $id)) {
            $this->updateEntry($id, $idSpace, $name, $id_resp, $idUser, $date_open, $date_close, $new_team, $new_project, $time_limit);
            return $id;
        } else {
            return $this->addEntry($idSpace, $name, $id_resp, $idUser, $date_open, $date_close, $new_team, $new_project, $time_limit);
        }
    }

    public function isProject($idSpace, $id)
    {
        $sql = "SELECT * FROM se_project WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function addEntry($idSpace, $name, $id_resp, $idUser, $date_open, $date_close, $new_team, $new_project, $time_limit)
    {
        if ($date_open == "") {
            $date_open = null;
        }
        if ($date_close == "") {
            $date_close = null;
        }
        $sql = "INSERT INTO se_project (id_space, name, id_resp, id_user, date_open, date_close, new_team, new_project, time_limit)
				 VALUES(?,?,?,?,?,?,?,?,?)";
        $this->runRequest($sql, array(
            $idSpace, $name, $id_resp, $idUser, $date_open, $date_close, $new_team, $new_project, $time_limit
        ));
        return $this->getDatabase()->lastInsertId();
    }

    public function updateEntry($id, $idSpace, $name, $id_resp, $idUser, $date_open, $date_close, $new_team, $new_project, $time_limit)
    {
        if ($date_open == "") {
            $date_open = null;
        }
        if ($date_close == "") {
            $date_close = null;
        }
        $sql = "update se_project set name=?, id_resp=?, id_user=?, date_open=?, date_close=?, new_team=?, new_project=?, time_limit=?
		        where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($name, $id_resp, $idUser, $date_open, $date_close, $new_team, $new_project, $time_limit, $id, $idSpace));
    }

    public function entries($idSpace, $yearBegin = "", $yearEnd = "", $sortentry = 'id')
    {
        $sql = "SELECT * FROM se_project WHERE id_space=? AND deleted=0 ";
        if ($yearBegin != "" && $yearEnd != "") {
            $sql .= "AND date_open >= '" . $yearBegin . "' AND date_open <= '" . $yearEnd . "' ";
        }
        $sql .= " ORDER BY " . $sortentry . " ASC;";

        $req = $this->runRequest($sql, array($idSpace));
        $entries = $req->fetchAll();
        $modelUser = new CoreUser();

        for ($i = 0; $i < count($entries); $i++) {
            $entries[$i]["user_name"] = $modelUser->getUserFullName($entries[$i]['id_user']);
            $entries[$i]["resp_name"] = $modelUser->getUserFullName($entries[$i]['id_resp']);
        }
        return $entries;
    }

    public function openedEntries($idSpace, $sortentry = 'id')
    {
        $sql = "select * from se_project WHERE date_close is null AND deleted=0 AND id_space=? order by " . $sortentry . " ASC;";
        $req = $this->runRequest($sql, array($idSpace));

        $entries = $req->fetchAll();
        $modelUser = new CoreUser();

        for ($i = 0; $i < count($entries); $i++) {
            $entries[$i]["user_name"] = $modelUser->getUserFullName($entries[$i]['id_user']);
            $entries[$i]["resp_name"] = $modelUser->getUserFullName($entries[$i]['id_resp']);
        }
        return $entries;
    }

    public function closedEntries($idSpace, $yearBegin = "", $yearEnd = "", $sortentry = 'id')
    {
        $sql = "SELECT * FROM se_project WHERE date_close is not null AND id_space=? AND deleted=0 ";
        if ($yearBegin != "" && $yearEnd != "") {
            $sql .= "AND date_close >= '" . $yearBegin . "' AND date_close <= '" . $yearEnd . "' ";
        }
        $sql .= " order by " . $sortentry . " ASC;";
        //echo "yearBegin = " . $yearBegin . "<br/>";
        //echo "yearEnd = " . $yearEnd . "<br/>";
        //echo "sql = " . $sql . "<br/>";
        $req = $this->runRequest($sql, array($idSpace));

        $entries = $req->fetchAll();
        $modelUser = new CoreUser();

        for ($i = 0; $i < count($entries); $i++) {
            $entries[$i]["user_name"] = $modelUser->getUserFullName($entries[$i]['id_user']);
            $entries[$i]["resp_name"] = $modelUser->getUserFullName($entries[$i]['id_resp']);
        }
        return $entries;
    }

    public function defaultEntryValues()
    {
        $entry["id"] = "";
        $entry["id_space"] = "";
        $entry["name"] = "";
        $entry["id_resp"] = "";
        $entry["id_user"] = "";
        $entry["date_open"] = date("Y-m-d");
        $entry["date_close"] = "";
        $entry["new_team"] = "";
        $entry["new_project"] = "";
        $entry["time_limit"] = "";
        $entry["id_origin"] = "";
        $entry["in_charge"] = 0;
        return $entry;
    }

    public function getProjectEntry($idSpace, $id)
    {
        $sql = "SELECT * from se_project_service where id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        $entry = $req->fetch();

        return $entry;
    }

    public function getEntry($idSpace, $id)
    {
        $sql = "SELECT * from se_project where id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        $entry = $req->fetch();

        return $entry;
    }

    public function setEntryClosed($idSpace, $id, $date_close)
    {
        if ($date_close == "") {
            $date_close = null;
        }
        $sql = "UPDATE se_project set date_close=?
		        where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($date_close, $id, $idSpace));
    }

    public function getInfoFromInvoice($id_invoice, $idSpace)
    {
        $sql = "SELECT * FROM in_invoice_item WHERE id_invoice=? AND id_space=? AND deleted=0";
        $invoiceItem = $this->runRequest($sql, array($id_invoice, $idSpace))->fetch();
        $details = explode(";", $invoiceItem["details"]);
        $proj = explode("=", $details[count($details) - 2]);
        $projUrl = explode("/", $proj[1]);
        $projID = $projUrl[2];
        $sqlp = "SELECT * FROM se_project WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sqlp, array($projID, $idSpace));
        if ($req->rowCount() > 0) {
            $info = $req->fetch();
        } else {
            $info = array();
            $info['closed_by'] = "";
            $info['closed_by_in'] = "";
        }

        if ($info['closed_by']) {
            $modelUser = new CoreUser();
            $sql2 = "SELECT id_user FROM se_visa WHERE id=? AND id_space=? AND deleted=0";
            $idUser = $this->runRequest($sql2, array($info['closed_by'], $idSpace))->fetch();
            $info['closed_by'] = $modelUser->getUserFullName($idUser[0]);
            $info['closed_by_in'] = $modelUser->getUserInitials($idUser[0]);
        } else {
            $info['closed_by'] = "";
            $info['closed_by_in'] = "";
        }
        return $info;
    }

    public function getProjectsOpenedPeriod($beginPeriod, $endPeriod, $idSpace)
    {
        $sql = "SELECT * FROM se_project WHERE date_open>=? AND date_open<=? AND id_space=? AND deleted=0";
        $projects = $this->runRequest($sql, array($beginPeriod, $endPeriod, $idSpace))->fetchAll();
        $modelUser = new CoreUser();
        $modelSampleCabinet = new StockShelf();
        foreach ($projects as $project) {
            $sql = "SELECT id_user FROM se_visa WHERE id=? AND id_space=? AND deleted=0";
            $requestResult = $this->runRequest($sql, array($project['closed_by'], $idSpace))->fetchAll();
            $idUser = empty($requestResult) ?: $requestResult[0];
            $project['closed_by'] = !empty($requestResult) ? $modelUser->getUserFullName($idUser[0]) : "";
            $project['closed_by_in'] = !empty($requestResult) ? $modelUser->getUserInitials($idUser[0]) : "";
            $project["sample_cabinet"] = $modelSampleCabinet->getFullName($idSpace, $project["id_sample_cabinet"]);
        }
        return $projects;
    }

    public function getPeriodeServicesBalances($idSpace, $beginPeriod, $endPeriod)
    {
        $sql = "select * from se_project where id_space=? AND deleted=0 AND (date_close>=? OR date_close is null)";
        $req = $this->runRequest($sql, array($idSpace, $beginPeriod));
        $projects = $req->fetchAll();


        $modelClient = new ClClient();
        $modelServices = new SeService();
        $items = $modelServices->getBySpace($idSpace);
        //print_r($items);

        for ($i = 0; $i < count($projects); $i++) {
            $projectEntries = $this->getPeriodProjectEntries($idSpace, $projects[$i]["id"], $beginPeriod, $endPeriod);


            // get active items
            $activeItems = $this->getProjectItems($projectEntries);
            $itemsSummary = $this->getProjectItemsSymmary($projectEntries, $activeItems);

            $projects[$i]["entries"] = $itemsSummary;
            $LABpricingid = $modelClient->getPricingID($idSpace, $projects[$i]["id_resp"]);
            $projects[$i]["total"] = $this->calculateProjectTotal($idSpace, $itemsSummary, $LABpricingid);
        }

        return array("items" => $items, "projects" => $projects);
    }

    public function getPeriodeBilledServicesBalances($idSpace, $beginPeriod, $endPeriod)
    {
        // get the projects
        $sql1 = "select * from se_project where deleted=0 AND id IN (SELECT DISTINCT id_project FROM se_project_service WHERE deleted=0 AND id_invoice IN(SELECT id FROM in_invoice WHERE date_generated>=? AND date_generated<=? AND id_space=? AND deleted=0))";
        $req1 = $this->runRequest($sql1, array($beginPeriod, $endPeriod, $idSpace));
        $projects = $req1->fetchAll();

        $items = array();
        $modelClient = new ClClient();
        for ($i = 0; $i < count($projects); $i++) {
            $projectEntries = $this->getPeriodBilledProjectEntries($idSpace, $projects[$i]["id"], $beginPeriod, $endPeriod);

            // get active items
            $activeItems = $this->getProjectItems($projectEntries);
            $itemsSummary = $this->getProjectItemsSymmary($projectEntries, $activeItems);
            //print_r($itemsSummary);

            $projects[$i]["entries"] = $itemsSummary;
            //print_r($itemsSummary);
            foreach ($itemsSummary as $itSum) {
                if ($itSum["pos"] > 0 && !in_array($itSum["id"], $items)) {
                    $items[] = $itSum["id"];
                }
            }

            $LABpricingid = $modelClient->getPricingID($idSpace, $projects[$i]["id_resp"]);
            $projects[$i]["total"] = $this->calculateProjectTotal($idSpace, $itemsSummary, $LABpricingid);
        }

        return array("items" => $items, "projects" => $projects);
    }

    protected function getPeriodBilledProjectEntries($idSpace, $id_proj, $beginPeriod, $endPeriod)
    {
        $sql = "select * from se_project_service where id_project=? AND deleted=0 AND id_invoice IN (SELECT id FROM in_invoice WHERE date_generated>=? AND date_generated<=? AND module='services' AND deleted=0 and id_space=?)";
        $req = $this->runRequest($sql, array(
            $id_proj, $beginPeriod, $endPeriod, $idSpace
        ));
        $entries = $req->fetchAll();

        $modelBill = new InInvoice();
        for ($i = 0; $i < count($entries); $i++) {
            if ($entries[$i]["id_invoice"] > 0) {
                $entries[$i]["invoice"] = $modelBill->getInvoiceNumber($idSpace, $entries[$i]["id_invoice"]);
            }
        }
        return $entries;
    }

    public function getPeriodProjectEntries($idSpace, $id_proj, $beginPeriod, $endPeriod)
    {
        $sql = "select * from se_project_service where id_project=? AND date>=? AND date<=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array(
            $id_proj, $beginPeriod, $endPeriod, $idSpace
        ));
        $entries = $req->fetchAll();

        $modelBill = new InInvoice();
        for ($i = 0; $i < count($entries); $i++) {
            //print_r($entries[$i]);
            if ($entries[$i]["id_invoice"] > 0) {
                $entries[$i]["invoice"] = $modelBill->getInvoiceNumber($idSpace, $entries[$i]["id_invoice"]);
            }
        }

        return $entries;
    }

    protected function getProjectItems($projectEntries)
    {
        $projectItems = array();
        foreach ($projectEntries as $entry) {
            //print_r($entry);
            $itemID = $entry["id_service"];
            $found = false;
            foreach ($projectItems as $item) {
                if ($item == $itemID) {
                    $found = true;
                }
            }
            if ($found == false) {
                $projectItems[] = $itemID;
            }
        }
        return $projectItems;
    }

    protected function getProjectItemsSymmary($projectEntries, $activeItems)
    {
        $activeItemsSummary = array();
        for ($i = 0; $i < count($activeItems); $i++) {
            $qi = 0;
            foreach ($projectEntries as $order) {
                //print_r($order);
                if ($order["id_service"] == $activeItems[$i]) {
                    $qi += floatval($order["quantity"]);
                }
            }
            $activeItemsSummary[$i]["id"] = $activeItems[$i];
            $activeItemsSummary[$i]["pos"] = 0;
            if ($qi > 0) {
                $activeItemsSummary[$i]["pos"] = 1;
                $activeItemsSummary[$i]["sum"] = $qi;
            }
        }
        return $activeItemsSummary;
    }

    protected function calculateProjectTotal($idSpace, $activeItems, $LABpricingid)
    {
        $totalHT = 0;
        $itemPricing = new SePrice();
        foreach ($activeItems as $item) {
            if ($item["pos"] > 0) {
                $unitaryPrice = $itemPricing->getPrice($idSpace, $item["id"], $LABpricingid);
                //print_r($unitaryPrice);
                $totalHT += (float) $item["sum"] * (float) $unitaryPrice;
            }
        }
        return $totalHT;
    }

    public function getReturnedSamples($idSpace)
    {
        $sql = "SELECT * FROM se_project WHERE id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($idSpace))->fetchAll();
        $modelUser = new CoreUser();
        $modelClient = new ClClient();
        $modelSampleCabinet = new StockShelf();
        for ($i = 0; $i < count($data); $i++) {
            $clientInfo = $modelClient->get($idSpace, $data[$i]['id_resp']);

            $data[$i]['resp'] = $clientInfo["contact_name"];
            $data[$i]['user'] = $modelUser->getUserFullName($data[$i]['id_user']);
            $data[$i]['unit'] = $modelClient->getInstitution($idSpace, $data[$i]['id_resp']);
            $data[$i]["sample_cabinet"] = $modelSampleCabinet->getFullName($idSpace, $data[$i]["id_sample_cabinet"]);
        }
        return $data;
    }

    /**
     * Delete a unit
     * @param number $id Unit ID
     */
    public function delete($idSpace, $id)
    {
        $sql = "UPDATE se_project SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }

     /**
     * Get user future bookings
     */
    public function getUserProjects($idSpace, $idUser): array
    {
        $q = array('id_user' => $idUser);
        $sql = 'SELECT se_project.*, spaces.name as space FROM se_project';
        $sql .= ' INNER JOIN core_spaces AS spaces ON spaces.id = se_project.id_space';
        $sql .= ' WHERE se_project.closed_by = 0 AND se_project.deleted = 0 AND se_project.in_charge IN (SELECT id FROM se_visa WHERE id_user=:id_user)';
        if ($idSpace && intval($idSpace) > 0) {
            $q['id_space'] = $idSpace;
            $sql .= ' AND se_project.id_space = :id_space';
        }
        $sql .= ' ORDER BY se_project.date_open DESC';
        $req = $this->runRequest($sql, $q);
        if ($req->rowCount() > 0) {
            return $req->fetchAll();
        }
        return [];
    }

    public function closedProjectsByPeriod($idSpace, $idUser, $date_from, $date_end)
    {
        $sql = 'SELECT * FROM se_project WHERE deleted=0 AND date_close is not null AND (date_close>=? AND date_close<=?) AND id_space=? AND id_user=?';
        $req = $this->runRequest($sql, [$date_from, $date_end, $idSpace, $idUser]);
        if ($req->rowCount() > 0) {
            return $req->fetchAll();
        }
        return [];
    }

    public function getEmailsForClosedProjectsByPeriod($idSpace, $date_from, $date_end)
    {
        $sql = 'SELECT DISTINCT core_users.email AS email FROM core_users INNER JOIN se_project on se_project.id_user=core_users.id WHERE se_project.deleted=0 AND se_project.date_close is not null AND (se_project.date_close>=? AND se_project.date_close<=?) AND se_project.id_space=?';
        $req = $this->runRequest($sql, [$date_from, $date_end, $idSpace]);
        if ($req->rowCount() > 0) {
            return $req->fetchAll();
        }
        return [];
    }
    ///// SE_PROJECT_USER METHODS /////

    public function getProjectUsers($idSpace, $id_project)
    {
        $sql = "SELECT * FROM se_project_user WHERE id_space=? AND id_project=? AND deleted=0;";
        $req = $this->runRequest($sql, array($idSpace, $id_project));
        return $req->fetchAll();
    }

    public function getProjectUsersIds($idSpace, $id_project)
    {
        $sql = "SELECT id_user FROM se_project_user WHERE id_space=? AND id_project=? AND deleted=0;";
        $req = $this->runRequest($sql, array($idSpace, $id_project));
        return $req->fetchAll();
    }

    public function setProjectUser($idSpace, $idUser, $id_project)
    {
        if (!$this->isProjectUser($idSpace, $idUser, $id_project)) {
            $sql = "INSERT INTO se_project_user (id_space, id_user, id_project) VALUES (?, ?, ?)";
            $this->runRequest($sql, array($idSpace, $idUser, $id_project));
            return $this->getDatabase()->lastInsertId();
        } else {
            return null;
        }
    }

    public function isProjectUser($idSpace, $idUser, $id_project)
    {
        $sql = "SELECT * FROM se_project_user WHERE id_user=? AND id_project=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($idUser, $id_project, $idSpace));
        if ($req->rowCount() >= 1) {
            return true;
        }
        return false;
    }

    public function deleteProjectUser($idSpace, $idUser, $id_project)
    {
        $sql = "UPDATE se_project_user SET deleted=1,deleted_at=NOW() WHERE id_user=? AND id_project=? AND id_space=?";
        $this->runRequest($sql, array($idUser, $id_project, $idSpace));
    }

    public function deleteAllProjectUsers($idSpace, $idUser)
    {
        $project_users = $this->getProjectUsers($idSpace, $idUser);
        if (!empty($project_users)) {
            foreach ($project_users as $project_user) {
                $this->deleteProjectUser($idSpace, $idUser, $project_user['id']);
            }
        }
    }
}
