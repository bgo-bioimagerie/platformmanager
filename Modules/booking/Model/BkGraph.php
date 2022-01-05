<?php

require_once 'Framework/Model.php';
require_once 'Modules/booking/Model/BkNightWE.php';

/**
 * Class defining the Sygrrif graph model
 *
 * @author Sylvain Prigent
 */
class BkGraph extends Model {

    /**
     * @deprecated since ec_user depreciation
     */
    public function getStatReservationPerResponsible($dateBegin, $dateEnd, $id_space, $resps, $excludeColorCode) {
        if($dateBegin == "") {
            throw new PfmParamException("invalid start date");
        }
        if($dateEnd == "") {
            throw new PfmParamException("invalid end date");
        }
        $dateBeginArray = explode("-", $dateBegin);
        $day_start = $dateBeginArray[2];
        $month_start = $dateBeginArray[1];
        $year_start = $dateBeginArray[0];

        $dateEndArray = explode("-", $dateEnd);
        $day_end = $dateEndArray[2];
        $month_end = $dateEndArray[1];
        $year_end = $dateEndArray[0];

        $timeBegin = mktime(0, 0, 0, $month_start, $day_start, $year_start);
        $timeEnd = mktime(0, 0, 0, $month_end, $day_end, $year_end);

        $data = array();

        $in_color = "";
        $pass = 0;
        foreach ($excludeColorCode as $col) {
            $in_color .= $col . ",";
            $pass++;
        }
        if ($pass > 0) {
            $in_color = substr($in_color, 0, -1);
        }

        $sql = 'SELECT * FROM re_info WHERE id_space=? AND deleted=0';
        $resources = $this->runRequest($sql, array($id_space))->fetchAll();

        foreach ($resources as $resource) {
            $d['resource'] = $resource["name"];
            foreach ($resps as $resp) {
                $sql = "SELECT * FROM bk_calendar_entry WHERE resource_id=? AND "
                        . "recipient_id IN (SELECT id_user FROM ec_j_user_responsible WHERE id_resp=?) "  // @bug ec_j_user_responsible does not exists
                        . " AND start_time >=" . $timeBegin . " AND end_time <=" . $timeEnd . " "
                        . " AND id_space=?"
                        . " AND deleted=0 ";
                if ($in_color != "") {
                    $sql .= ' AND color_type_id NOT IN (' . $in_color . ')';
                }
                $resa = $this->runRequest($sql, array($resource['id'], $resp['id'], $id_space));

                $resatable = $resa->fetchAll();
                $timeSec = 0;
                foreach ($resatable as $r) {
                    $timeSec += $r['end_time'] - $r['start_time'];
                }
                $d['resp_' . $resp['id']] = array($resa->rowCount(), round($timeSec / 3600));
            }
            $data[] = $d;
        }
        return $data;
    }

    public function getStatReservationPerClient($dateBegin, $dateEnd, $id_space, $clients, $excludeColorCode) {
        if($dateBegin == "") {
            throw new PfmParamException("invalid start date");
        }
        if($dateEnd == "") {
            throw new PfmParamException("invalid end date");
        }
        $dateBeginArray = explode("-", $dateBegin);
        $day_start = $dateBeginArray[2];
        $month_start = $dateBeginArray[1];
        $year_start = $dateBeginArray[0];

        $dateEndArray = explode("-", $dateEnd);
        $day_end = $dateEndArray[2];
        $month_end = $dateEndArray[1];
        $year_end = $dateEndArray[0];

        $timeBegin = mktime(0, 0, 0, $month_start, $day_start, $year_start);
        $timeEnd = mktime(0, 0, 0, $month_end, $day_end, $year_end);

        $data = array();

        $in_color = "";
        $pass = 0;
        foreach ($excludeColorCode as $col) {
            $in_color .= $col . ",";
            $pass++;
        }
        if ($pass > 0) {
            $in_color = substr($in_color, 0, -1);
        }
        $sql = 'SELECT * FROM re_info WHERE id_space=? AND deleted=0';
        $resources = $this->runRequest($sql, array($id_space))->fetchAll();
        // TODO: optimize this !!!
        foreach ($resources as $resource) {
            $d['resource'] = $resource["name"];
            foreach ($clients as $client) {
                $sql = "SELECT * FROM bk_calendar_entry WHERE resource_id=? AND "
                        . "recipient_id IN (SELECT id_user FROM cl_j_client_user WHERE id_client=?) "
                        . " AND start_time >=" . $timeBegin . " AND end_time <=" . $timeEnd . " "
                        . " AND id_space=?"
                        . " AND deleted=0 ";
                if ($in_color != "") {
                    $sql .= ' AND color_type_id NOT IN (' . $in_color . ')';
                }
                $resa = $this->runRequest($sql, array($resource['id'], $client['id'], $id_space));
                $resatable = $resa->fetchAll();
                $timeSec = 0;
                foreach ($resatable as $r) {
                    $timeSec += $r['end_time'] - $r['start_time'];
                }
                $d['client_' . $client['id']] = array($resa->rowCount(), round($timeSec / 3600));
            }
            $data[] = $d;
        }
        return $data;
    }

    public function getStatReservationPerMonth($month_start, $year_start, $month_end, $year_end, $id_space, $exclude_color) {

        $in_color = "";
        $pass = 0;
        foreach ($exclude_color as $col) {
            $in_color .= $col . ",";
            $pass++;
        }
        if ($pass > 0) {
            $in_color = substr($in_color, 0, -1);
        }

        $countResa = array();
        $timeResa = array();
        for ($y = $year_start; $y <= $year_end; $y++) {
            // start month
            $start_month = 1;
            if ($y == $year_start) {
                $start_month = $month_start;
            }
            // end month
            $stop_month = 12;
            if ($y == $year_end) {
                $stop_month = $month_end;
            }
            for ($m = $start_month; $m <= $stop_month; $m++) {

                $dstart = mktime(0, 0, 0, $m, 1, $y); // Le premier jour du mois en cours
                $dend = mktime(0, 0, 0, $m + 1, 1, $y); // Le 0eme jour du mois suivant == le dernier jour du mois en cour

                $sql = 'SELECT * FROM bk_calendar_entry WHERE deleted=0 AND id_space=? AND resource_id IN (SELECT id FROM re_info WHERE id_space=? AND deleted=0) AND start_time >=' . $dstart . ' AND end_time <=' . $dend;
                if ($in_color != "") {
                    $sql .= ' AND color_type_id NOT IN (' . $in_color . ')';
                }
                //echo 'sql = '.$sql . '<br/>';
                $req = $this->runRequest($sql, array($id_space, $id_space));
                $countResa[] = $req->rowCount();
                $data = $req->fetchAll();
                $timeSec = 0;
                foreach ($data as $resa) {
                    $timeSec += $resa['end_time'] - $resa['start_time'];
                }
                $timeResa[] = round($timeSec / 3600);
                $dates[] = date('M Y', $dstart);
            }
        }
        return array('count' => $countResa, 'time' => $timeResa, 'dates' => $dates);
    }

    public function getReservationPerResourceColor($id_space, $dateBegin, $dateEnd, $idResource, $idColorCode) {
        if($dateBegin == "") {
            throw new PfmParamException("invalid start date");
        }
        if($dateEnd == "") {
            throw new PfmParamException("invalid end date");
        }

        $dateBeginArray = explode('-', $dateBegin);
        $dateEndArray = explode('-', $dateEnd);
        $dstart = mktime(0, 0, 0, $dateBeginArray[1], $dateBeginArray[2], $dateBeginArray[0]); // Le premier jour du mois en cours
        $dend = mktime(0, 0, 0, $dateEndArray[1], $dateEndArray[2], $dateEndArray[0]); // Le 0eme jour du mois suivant == le dernier jour du mois en cour

        $sql = 'SELECT * FROM bk_calendar_entry WHERE deleted=0 AND id_space=? AND resource_id=? AND start_time >=' . $dstart . ' AND end_time <=' . $dend;
        $sql .= ' AND color_type_id =?';
        $req = $this->runRequest($sql, array($id_space, $idResource, $idColorCode));
        $data = $req->fetchAll();
        $timeSec = 0;
        foreach ($data as $resa) {
            $timeSec += $resa['end_time'] - $resa['start_time'];
        }
        $timeResa = round($timeSec / 3600);
        return $timeResa;
    }

    public function getStatReservationPerResource($dateBegin, $dateEnd, $id_space, $excludeColorCode) {

        $in_color = "";
        $pass = 0;
        foreach ($excludeColorCode as $col) {
            $in_color .= $col . ",";
            $pass++;
        }
        if ($pass > 0) {
            $in_color = substr($in_color, 0, -1);
        }

        $sql = "SELECT * FROM re_info WHERE id_space=? AND deleted=0";
        $resourcesIds = $this->runRequest($sql, array($id_space))->fetchAll();

        $countResa = array();
        $timeResa = array();
        $countCancelled = array();
        $timeCancelled = array();

        $resourcesNames = array();
        $resourcesIdsOut = array();


        if($dateBegin == "") {
            throw new PfmParamException("invalid start date");
        }
        if($dateEnd == "") {
            throw new PfmParamException("invalid end date");
        }

        $dateBeginArray = explode('-', $dateBegin);
        $dateEndArray = explode('-', $dateEnd);
        $dstart = mktime(0, 0, 0, $dateBeginArray[1], $dateBeginArray[2], $dateBeginArray[0]); // Le premier jour du mois en cours
        $dend = mktime(0, 0, 0, $dateEndArray[1], $dateEndArray[2], $dateEndArray[0]); // Le 0eme jour du mois suivant == le dernier jour du mois en cour

        foreach ($resourcesIds as $res) {

            $sql = 'SELECT * FROM bk_calendar_entry WHERE deleted=0 AND id_space=? AND resource_id=? AND start_time >=' . $dstart . ' AND end_time <=' . $dend;
            if ($in_color != "") {
                $sql .= ' AND color_type_id NOT IN (' . $in_color . ')';
            }
            //echo 'sql = '.$sql . '<br/>';
            $req = $this->runRequest($sql, array($id_space, $res['id']));
            //$countResa[] = $req->rowCount();
            $data = $req->fetchAll();
            $timeSec = 0;
            $timeSecCancelled = 0;
            $countResaOK = 0;
            $countResaCancelled= 0;
            foreach ($data as $resa) {
                if ($resa['deleted'] == 0) {
                    $timeSec += $resa['end_time'] - $resa['start_time'];
                    $countResaOK += 1;
                } else {
                    $timeSecCancelled += $resa['end_time'] - $resa['start_time'];
                    $countResaCancelled += 1;
                }
            }
            $countResa[] = $countResaOK;
            $countCancelled[] = $countResaCancelled;
            $timeResa[] = round($timeSec / 3600);
            $timeCancelled[] = round($timeSecCancelled / 3600);
            $resourcesNames[] = $res['name'];
            $resourcesIdsOut[] = $res['id'];
        }
        return array('count' => $countResa, 'time' => $timeResa, 'resource' => $resourcesNames,
            'resourcesids' => $resourcesIdsOut, 'countCancelled' => $countCancelled, 'timeCancelled' => $timeCancelled);
    }

    /**
     * Generate a graph containing the number of reservation per month
     * @param unknown $year
     * @return multitype:multitype:unknown  number
     */
    public function getYearNumResGraph($id_space, $month_start, $year_start, $month_end, $year_end) {

        $num = 0;
        $numTotal = 0;
        $graph = array();
        $monthIds = array();
        $i = 0;
        for ($y = $year_start; $y <= $year_end; $y++) {
            // start month
            $start_month = 1;
            if ($y == $year_start) {
                $start_month = $month_start;
            }
            // end month
            $stop_month = 12;
            if ($y == $year_end) {
                $stop_month = $month_end;
            }
            for ($m = $start_month; $m <= $stop_month; $m++) {
                $dstart = mktime(0, 0, 0, $m, 1, $y); // Le premier jour du mois en cours
                $dend = mktime(0, 0, 0, $m + 1, 1, $y); // Le 0eme jour du mois suivant == le dernier jour du mois en cour

                $sql = 'SELECT DISTINCT resource_id FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . $dstart . ' AND end_time <=' . $dend . ' ORDER by resource_id';
                $req = $this->runRequest($sql, array($id_space));
                $numMachinesFormesTotal = $req->rowCount();
                $machinesFormesListe = $req->fetchAll();

                $num = 0;
                foreach ($machinesFormesListe as $machine) {
                    // test if the resource still exists
                    $sql = 'SELECT name FROM re_info WHERE id_space=? AND deleted=0 AND id ="' . $machine[0] . '"';
                    $req = $this->runRequest($sql, array($id_space));
                    $res = $req->fetchAll();
                    if (count($res) > 0) {
                        $sql = 'SELECT * FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . $dstart . ' AND end_time <=' . $dend . ' AND resource_id ="' . $machine[0] . '"';
                        $req = $this->runRequest($sql, array($id_space));
                        $num += $req->rowCount();
                    }
                }

                $i++;
                $numTotal += $num;
                $graph[$i] = $num;
                $monthIds[$i] = $m;
            }
        }

        $graphData = array('numTotal' => $numTotal, 'graph' => $graph, 'monthIds' => $monthIds);
        return $graphData;
    }

    /**
     * Generate a graph containing the number of hours of reservation per year
     * @param unknown $year
     * @return multitype:number multitype:number
     */
    public function getYearNumHoursResGraph($id_space, $month_start, $year_start, $month_end, $year_end) {

        $timeResa = 0.0;
        $timeTotal = 0.0;
        $graph = array();
        $monthIds = array();
        $i = 0;
        for ($y = $year_start; $y <= $year_end; $y++) {
            // start month
            $start_month = 1;
            if ($y == $year_start) {
                $start_month = $month_start;
            }
            // end month
            $stop_month = 12;
            if ($y == $year_end) {
                $stop_month = $month_end;
            }
            for ($m = $start_month; $m <= $stop_month; $m++) {
                $dstart = mktime(0, 0, 0, $m, 1, $y); // Le premier jour du mois en cours
                $dend = mktime(0, 0, 0, $m + 1, 1, $y); // Le 0eme jour du mois suivant == le dernier jour du mois en cour

                $sql = 'SELECT DISTINCT resource_id FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . $dstart . ' AND end_time <=' . $dend . ' ORDER by resource_id';
                $req = $this->runRequest($sql, array($id_space));
                $numMachinesFormesTotal = $req->rowCount();
                $machinesFormesListe = $req->fetchAll();

                $timeResa = 0;
                foreach ($machinesFormesListe as $machine) {
                    // test if the resource still exists
                    $sql = 'SELECT name FROM re_info WHERE id_space=? AND deleted=0 AND id =?';
                    $req = $this->runRequest($sql, array($id_space, $machine[0]));
                    $res = $req->fetchAll();
                    if (count($res) > 0) {
                        $sql = 'SELECT * FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . $dstart . ' AND end_time <=' . $dend . ' AND resource_id ="' . $machine[0] . '"';
                        $req = $this->runRequest($sql, array($id_space));
                        $datas = $req->fetchAll();


                        foreach ($datas as $data) {
                            if ($data["end_time"] - $data["start_time"] >= 0) {
                                $timeResa += (float) ($data["end_time"] - $data["start_time"]) / (float) 3600;
                            } else {
                                echo "WARNING: error in reservation : <br/>";
                                print_r($data);
                            }
                        }
                    }
                }
                $i++;
                $timeTotal += $timeResa;
                $graph[$i] = $timeResa;
                $monthIds[$i] = $m;
            }
        }
        $timeTotal = round($timeTotal);
        $graphData = array('timeTotal' => $timeTotal, 'graph' => $graph, 'monthIds' => $monthIds);
        return $graphData;
    }

    /**
     * Generate a pie chart number of reservation per resource
     * @param number $year
     * @return unknown
     */
    public function getCamembertArray($id_space, $month_start, $year_start, $month_end, $year_end) {
        $sql = 'SELECT DISTINCT resource_id FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . mktime(0, 0, 0, $month_start, 1, $year_start) . ' AND end_time <=' . mktime(0, 0, 0, $month_end + 1, 0, $year_end) . ' ORDER by resource_id';
        $req = $this->runRequest($sql, array($id_space));
        $numMachinesFormesTotal = $req->rowCount();
        $machinesFormesListe = $req->fetchAll();

        $machineFormes = array();
        $i = -1;
        foreach ($machinesFormesListe as $mFL) {
            $i++;
            $sql = 'SELECT * FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . mktime(0, 0, 0, $month_start, 1, $year_start) . ' AND end_time <=' . mktime(0, 0, 0, $month_end + 1, 1, $year_end) . ' AND resource_id ="' . $mFL[0] . '"';
            $req = $this->runRequest($sql, array($id_space));
            $numMachinesFormes[$i][0] = $mFL[0];
            $numMachinesFormes[$i][1] = $req->rowCount();

            $sql = 'SELECT name FROM re_info WHERE id_space=? AND deleted=0 AND id =?';
            $req = $this->runRequest($sql, array($id_space, $mFL[0]));
            $res = $req->fetchAll();
            $nomMachine = "-";
            if (!empty($res)) {
                $nomMachine = $res[0][0];
            }

            $numMachinesFormes[$i][0] = $nomMachine;
        }
        return $numMachinesFormes;
    }

    private function calculateReservationTime($searchDate_start, $searchDate_end, $night_start, $night_end, $we_array) {

        $gap = 60;
        $timeStep = $searchDate_start;
        $nb_hours_day = 0;
        $nb_hours_night = 0;
        $nb_hours_we = 0;
        while ($timeStep <= $searchDate_end) {
            // test if pricing is we
            if (in_array(date("N", $timeStep), $we_array) && in_array(date("N", $timeStep + $gap), $we_array)) {  // we pricing
                $nb_hours_we += $gap;
            } else {
                $H = date("H", $timeStep);

                if ($H >= $night_end && $H < $night_start) { // price day
                    $nb_hours_day += $gap;
                } else { // price night
                    $nb_hours_night += $gap;
                }
            }
            $timeStep += $gap;
        }

        $resaDayNightWe[0] = round($nb_hours_day / 3600, 1);
        $resaDayNightWe[1] = round($nb_hours_night / 3600, 1);
        $resaDayNightWe[2] = round($nb_hours_we / 3600, 1);
        return $resaDayNightWe;
    }

    private function getFirstPricing($id_space) {
        $pricingModel = new BkNightWE();
        $pricingsInfo = $pricingModel->getSpacePrices($id_space);
        return $pricingsInfo[0];
    }

    /**
     * Generate a pie chart number of hours of reservation per resource
     * @param unknown $year
     * @return unknown
     */
    public function getCamembertTimeArray($id_space, $month_start, $year_start, $month_end, $year_end) {
        $sql = 'SELECT DISTINCT resource_id FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . mktime(0, 0, 0, $month_start, 1, $year_start) . ' AND end_time <=' . mktime(0, 0, 0, $month_end + 1, 0, $year_end) . ' ORDER by resource_id';
        $req = $this->runRequest($sql, array($id_space));
        $numMachinesFormesTotal = $req->rowCount();
        $machinesFormesListe = $req->fetchAll();
        $numMachinesFormes = array();

        $i = -1;
        foreach ($machinesFormesListe as $mFL) {

            // get the night and we periods
            $pricingInfo = $this->getFirstPricing($id_space);
            $night_start = $pricingInfo['night_start'];
            $night_end = $pricingInfo['night_end'];
            $we_array1 = explode(",", $pricingInfo['choice_we']);
            $we_array = array();
            for ($s = 0; $s < count($we_array1); $s++) {
                if ($we_array1[$s] > 0) {
                    $we_array[] = $s + 1;
                }
            }

            // get all the reservations
            $i++;
            $sql = 'SELECT * FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . mktime(0, 0, 0, $month_start, 1, $year_start) . ' AND end_time <=' . mktime(0, 0, 0, $month_end + 1, 1, $year_end) . ' AND resource_id ="' . $mFL[0] . '"';
            $req = $this->runRequest($sql, array($id_space));
            $resas = $req->fetchAll();

            // calculate the reservation time
            $timeResa = 0.0;
            $timeResaNight = 0.0;
            $timeResaWe = 0.0;
            foreach ($resas as $resa) {
                $timeResaArray = $this->calculateReservationTime($resa["start_time"], $resa["end_time"], $night_start, $night_end, $we_array);
                $timeResa += $timeResaArray[0];
                $timeResaNight += $timeResaArray[1];
                $timeResaWe += $timeResaArray[2];
            }
            $numMachinesFormes[$i][1] = $timeResa;
            $numMachinesFormes[$i][2] = $timeResaNight;
            $numMachinesFormes[$i][3] = $timeResaWe;

            $sql = 'SELECT name FROM re_info WHERE id_space=? AND deleted=0 AND id =?';
            $req = $this->runRequest($sql, array($id_space, $mFL[0]));
            $res = $req->fetchAll();
            $nomMachine = "-";
            if (!empty($res)) {
                $nomMachine = $res[0][0];
            }

            $numMachinesFormes[$i][0] = $nomMachine;
        }

        return $numMachinesFormes;
    }

    /**
     * Generate a pie chart number of reservation per resource
     * @param unknown $year
     * @param unknown $numTotal
     * @return string
     */
    public function getCamembertContent($id_space, $month_start, $year_start, $month_end, $year_end, $numTotal) {
        $sql = 'SELECT DISTINCT resource_id FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . mktime(0, 0, 0, $month_start, 1, $year_start) . ' AND end_time <=' . mktime(0, 0, 0, $month_end + 1, 0, $year_end) . ' ORDER by resource_id';
        $req = $this->runRequest($sql, array($id_space));
        $numMachinesFormesTotal = $req->rowCount();
        $machinesFormesListe = $req->fetchAll();

        $i = 0;
        $numMachinesFormes = array();
        $angle = 0;
        $departX = 300 + 250 * cos(0);
        $departY = 300 - 250 * sin(0);

        $test = '<g fill="rgb(97, 115, 169)">';
        $test .= '<title>Réservations</title>';
        $test .= '<desc>287</desc>';
        $test .= '<rect x="0" y="0" width="1000" height="600" fill="white" stroke="black" stroke-width="0"/>';
        $couleur = array("#FC441D", "#FE8D11", "#FCC212", "#FFFD32", "#D0E92B", "#53D745", "#6AC720", "#156947", "#291D81", "#804DA4", "#E4AADF", "#A7194B", "#FE0000",
            "#FC441D", "#FE8D11", "#FCC212", "#FFFD32", "#D0E92B", "#53D745", "#6AC720", "#156947", "#291D81", "#804DA4", "#E4AADF", "#A7194B", "#FE0000"
        );

        foreach ($machinesFormesListe as $mFL) {
            $sql = 'SELECT * FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . mktime(0, 0, 0, $month_start, 1, $year_start) . ' AND end_time <=' . mktime(0, 0, 0, $month_end + 1, 1, $year_end) . ' AND resource_id ="' . $mFL[0] . '"';
            $req = $this->runRequest($sql, array($id_space));
            $numMachinesFormes[$i][0] = $mFL[0];
            $numMachinesFormes[$i][1] = $req->rowCount();

            $curentAngle = 2 * pi() * $numMachinesFormes[$i][1] / $numMachinesFormesTotal;

            $sql = 'SELECT name FROM re_info WHERE id_space=? AND deleted=0 AND id =?';
            $req = $this->runRequest($sql, array($id_space, $mFL[0]));
            $res = $req->fetchAll();
            $nomMachine = "-";
            if (!empty($res)) {
                $nomMachine = $res[0][0];
            }

            if ($nomMachine != "-") {

                if ($curentAngle > pi()) {

                    $angle += $curentAngle / 2;

                    $arriveeX = 300 + 250 * cos($angle);
                    $arriveeY = 300 - 250 * sin($angle);

                    $test .= '<path d="M ' . $departX . ' ' . $departY . ' A 250 250 0 0 0 ' . $arriveeX . ' ' . $arriveeY . ' L 300 300" fill="' . $couleur[$i] . '" stroke="black" stroke-width="0"  />';
                    $test .= '<g>';

                    $departX = $arriveeX;
                    $departY = $arriveeY;
                    $angle += $curentAngle / 2;
                } else {
                    $angle += $curentAngle;
                }

                $arriveeX = 300 + 250 * cos($angle);
                $arriveeY = 300 - 250 * sin($angle);

                $test .= '<path d="M ' . $departX . ' ' . $departY . ' A 250 250 0 0 0 ' . $arriveeX . ' ' . $arriveeY . ' L 300 300" fill="' . $couleur[$i] . '"/>';
                $test .= '<g>';
                $test .= '<rect x="580" y="' . (83 + 40 * $i) . '" width="30" height="20" rx="5" ry="5" fill="' . $couleur[$i] . '" stroke="' . $couleur[$i] . '" stroke-width="0"/>';

                $test .= '<text x="615" y="' . (90 + 40 * $i) . '" font-size="25" fill="black" stroke="none" text-anchor="start" baseline-shift="-11px">' . $nomMachine . ' : ' . $numMachinesFormes[$i][1] . '</text>';
                $test .= '</g>';


                $departX = $arriveeX;
                $departY = $arriveeY;

                $i++;
            }
        }

        $test .= '</g>';
        return $test;
    }

    /**
     * Generate a pie chart number of reservation per resource
     * @param unknown $year
     * @param unknown $numTotal
     * @return string
     */
    public function getCamembertContentResourceType($id_space, $month_start, $year_start, $month_end, $year_end, $numTotal) {
        $sql = 'SELECT DISTINCT resource_id FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . mktime(0, 0, 0, $month_start, 1, $year_start) . ' AND end_time <=' . mktime(0, 0, 0, $month_end + 1, 0, $year_end) . ' ORDER by resource_id';
        $req = $this->runRequest($sql, array($id_space));
        $numMachinesFormesTotal = $req->rowCount();
        $machinesFormesListe = $req->fetchAll();

        for ($m = 0; $m < count($machinesFormesListe); $m++) {
            $sql = 'SELECT category_id from re_info WHERE id=? AND id_space=? AND deleted=0';
            $req = $this->runRequest($sql, array($machinesFormesListe[$m]["resource_id"], $id_space));
            $val = $req->fetch();
            $machinesFormesListe[$m]["category_id"] = $val[0];
        }

        $sql = 'SELECT * from re_infocategory WHERE id_space=? AND deleted=0 ORDER BY name ASC;';
        $req = $this->runRequest($sql, array($id_space));
        $resourceTypeList = $req->fetchAll();

        $i = 0;
        $numMachinesFormes = array();
        $angle = 0;
        $departX = 300 + 250 * cos(0);
        $departY = 300 - 250 * sin(0);

        $test = '<g fill="rgb(97, 115, 169)">';
        $test .= '<title>Réservations</title>';
        $test .= '<desc>287</desc>';
        $test .= '<rect x="0" y="0" width="1000" height="600" fill="white" stroke="black" stroke-width="0"/>';
        $couleur = array("#FC441D", "#FE8D11", "#FCC212", "#FFFD32", "#D0E92B", "#53D745", "#6AC720", "#156947", "#291D81", "#804DA4", "#E4AADF", "#A7194B", "#FE0000",
            "#FC441D", "#FE8D11", "#FCC212", "#FFFD32", "#D0E92B", "#53D745", "#6AC720", "#156947", "#291D81", "#804DA4", "#E4AADF", "#A7194B", "#FE0000"
        );

        for ($i = 0; $i < count($resourceTypeList); $i++) {

            $resTypID = $resourceTypeList[$i]["id"];
            $count = 0;
            foreach ($machinesFormesListe as $mFL) {

                if ($mFL["category_id"] == $resTypID) {
                    $sql = 'SELECT * FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . mktime(0, 0, 0, $month_start, 1, $year_start) . ' AND end_time <=' . mktime(0, 0, 0, $month_end + 1, 1, $year_end) . ' AND resource_id ="' . $mFL[0] . '"';
                    $req = $this->runRequest($sql, array($id_space));
                    $count += $req->rowCount();
                }
            }
            $resourceTypeList[$i]["number_resa"] = $count;

            $curentAngle = 2 * pi() * $resourceTypeList[$i]["number_resa"] / $numTotal;

            if ($curentAngle > pi()) {

                $angle += $curentAngle / 2;

                $arriveeX = 300 + 250 * cos($angle);
                $arriveeY = 300 - 250 * sin($angle);

                $test .= '<path d="M ' . $departX . ' ' . $departY . ' A 250 250 0 0 0 ' . $arriveeX . ' ' . $arriveeY . ' L 300 300" fill="' . $couleur[$i] . '" stroke="black" stroke-width="0"  />';
                $test .= '<g>';

                $departX = $arriveeX;
                $departY = $arriveeY;
                $angle += $curentAngle / 2;
            } else {
                $angle += $curentAngle;
            }

            $arriveeX = 300 + 250 * cos($angle);
            $arriveeY = 300 - 250 * sin($angle);

            $test .= '<path d="M ' . $departX . ' ' . $departY . ' A 250 250 0 0 0 ' . $arriveeX . ' ' . $arriveeY . ' L 300 300" fill="' . $couleur[$i] . '"/>';
            $test .= '<g>';
            $test .= '<rect x="580" y="' . (83 + 40 * $i) . '" width="30" height="20" rx="5" ry="5" fill="' . $couleur[$i] . '" stroke="' . $couleur[$i] . '" stroke-width="0"/>';

            $nomMachine = $resourceTypeList[$i]["name"];
            $test .= '<text x="615" y="' . (90 + 40 * $i) . '" font-size="25" fill="black" stroke="none" text-anchor="start" baseline-shift="-11px">' . $nomMachine . ' : ' . $resourceTypeList[$i]["number_resa"] . '</text>';
            $test .= '</g>';


            $departX = $arriveeX;
            $departY = $arriveeY;
        }

        $test .= '</g>';
        return $test;
    }

    /**
     * Generate a pie chart time of reservation per resource
     * @param unknown $year
     * @param unknown $numTotal
     * @return string
     */
    public function getCamembertTimeContentResourceType($id_space, $month_start, $year_start, $month_end, $year_end, $numTotal) {

        $sql = 'SELECT DISTINCT resource_id FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . mktime(0, 0, 0, $month_start, 1, $year_start) . ' AND end_time <=' . mktime(0, 0, 0, $month_end + 1, 0, $year_end) . ' ORDER by resource_id';
        $req = $this->runRequest($sql, array($id_space));
        $numMachinesFormesTotal = $req->rowCount();
        $machinesFormesListe = $req->fetchAll();

        // get the night and we periods
        $pricingInfo = $this->getFirstPricing($id_space);
        $night_start = $pricingInfo['night_start'];
        $night_end = $pricingInfo['night_end'];
        $we_array1 = explode(",", $pricingInfo['choice_we']);
        $we_array = array();
        for ($s = 0; $s < count($we_array1); $s++) {
            if ($we_array1[$s] > 0) {
                $we_array[] = $s + 1;
            }
        }

        for ($m = 0; $m < count($machinesFormesListe); $m++) {
            $sql = 'SELECT category_id from re_info WHERE id=? AND id_space=? AND deleted=0';
            $req = $this->runRequest($sql, array($machinesFormesListe[$m]["resource_id"], $id_space));
            $val = $req->fetch();
            $machinesFormesListe[$m]["category_id"] = $val[0];
        }

        $sql = 'SELECT * from re_infocategory WHERE id_space=? AND deleted=0 ORDER BY name ASC;';
        $req = $this->runRequest($sql, array($id_space));
        $resourceTypeList = $req->fetchAll();

        $i = 0;
        $numMachinesFormes = array();
        $angle = 0;
        $departX = 300 + 250 * cos(0);
        $departY = 300 - 250 * sin(0);

        $test = '<g fill="rgb(97, 115, 169)">';
        $test .= '<title>Réservations</title>';
        $test .= '<desc>287</desc>';
        $test .= '<rect x="0" y="0" width="1000" height="600" fill="white" stroke="black" stroke-width="0"/>';
        $couleur = array("#FC441D", "#FE8D11", "#FCC212", "#FFFD32", "#D0E92B", "#53D745", "#6AC720", "#156947", "#291D81", "#804DA4", "#E4AADF", "#A7194B", "#FE0000",
            "#FC441D", "#FE8D11", "#FCC212", "#FFFD32", "#D0E92B", "#53D745", "#6AC720", "#156947", "#291D81", "#804DA4", "#E4AADF", "#A7194B", "#FE0000"
        );

        $resourceType = 1; // forced to count night and we prices (to be modified if needed)

        for ($i = 0; $i < count($resourceTypeList); $i++) {


            $resTypID = $resourceTypeList[$i]["id"];
            $timeResa = 0;
            $timeResaNight = 0;
            $timeResaWe = 0;
            foreach ($machinesFormesListe as $mFL) {

                if ($mFL["category_id"] == $resTypID) {
                    $sql = 'SELECT * FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . mktime(0, 0, 0, $month_start, 1, $year_start) . ' AND end_time <=' . mktime(0, 0, 0, $month_end + 1, 1, $year_end) . ' AND resource_id ="' . $mFL[0] . '"';
                    $req = $this->runRequest($sql, array($id_space));
                    $numMachinesFormes[$i][0] = $mFL[0];

                    $resas = $req->fetchAll();
                    foreach ($resas as $resa) {
                        if ($resourceType == 1) {
                            $timeResaArray = $this->calculateReservationTime($resa["start_time"], $resa["end_time"], $night_start, $night_end, $we_array);
                            $timeResa += $timeResaArray[0];
                            $timeResaNight += $timeResaArray[1];
                            $timeResaWe += $timeResaArray[2];
                        } else {
                            $timeResa += (float) ($resa["end_time"] - $resa["start_time"]) / (float) 3600;
                        }
                    }
                }
            }

            $curentAngle = 2 * pi() * ($timeResa + $timeResaNight + $timeResaWe) / $numTotal;

            if ($curentAngle > pi()) {

                $angle += $curentAngle / 2;

                $arriveeX = 300 + 250 * cos($angle);
                $arriveeY = 300 - 250 * sin($angle);

                $test .= '<path d="M ' . $departX . ' ' . $departY . ' A 250 250 0 0 0 ' . $arriveeX . ' ' . $arriveeY . ' L 300 300" fill="' . $couleur[$i] . '" stroke="black" stroke-width="0"  />';
                $test .= '<g>';

                $departX = $arriveeX;
                $departY = $arriveeY;
                $angle += $curentAngle / 2;
            } else {
                $angle += $curentAngle;
            }

            $arriveeX = 300 + 250 * cos($angle);
            $arriveeY = 300 - 250 * sin($angle);

            $test .= '<path d="M ' . $departX . ' ' . $departY . ' A 250 250 0 0 0 ' . $arriveeX . ' ' . $arriveeY . ' L 300 300" fill="' . $couleur[$i] . '"/>';
            $test .= '<g>';
            $test .= '<rect x="580" y="' . (83 + 40 * $i) . '" width="30" height="20" rx="5" ry="5" fill="' . $couleur[$i] . '" stroke="' . $couleur[$i] . '" stroke-width="0"/>';

            $sql = 'SELECT name FROM re_info WHERE id ="' . $mFL[0] . '"';
            $req = $this->runRequest($sql);
            $res = $req->fetchAll();
            $nomMachine = $resourceTypeList[$i]["name"];

            if ($timeResaNight != 0 || $timeResaWe != 0) {
                $test .= '<text x="615" y="' . (90 + 40 * $i) . '" font-size="20" fill="black" stroke="none" text-anchor="start" baseline-shift="-11px">' . $nomMachine . ' : ' . $timeResa . "|" . $timeResaNight . "|" . $timeResaWe . '</text>';
            } else {
                $test .= '<text x="615" y="' . (90 + 40 * $i) . '" font-size="20" fill="black" stroke="none" text-anchor="start" baseline-shift="-11px">' . $nomMachine . ' : ' . $timeResa . '</text>';
            }
            $test .= '</g>';

            $departX = $arriveeX;
            $departY = $arriveeY;
        }


        $test .= '</g>';
        return $test;
    }

    /**
     * Generate a pie chart time of reservation per resource
     * @param unknown $year
     * @param unknown $numTotal
     * @return string
     */
    public function getCamembertTimeContent($id_space, $month_start, $year_start, $month_end, $year_end, $numTotal) {
        $sql = 'SELECT DISTINCT resource_id FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . mktime(0, 0, 0, $month_start, 1, $year_start) . ' AND end_time <=' . mktime(0, 0, 0, $month_end + 1, 0, $year_end) . ' ORDER by resource_id';
        $req = $this->runRequest($sql, array($id_space));
        $numMachinesFormesTotal = $req->rowCount();
        $machinesFormesListe = $req->fetchAll();

        // get the night and we periods
        $pricingInfo = $this->getFirstPricing($id_space);
        $night_start = $pricingInfo['night_start'];
        $night_end = $pricingInfo['night_end'];
        $we_array1 = explode(",", $pricingInfo['choice_we']);
        $we_array = array();
        for ($s = 0; $s < count($we_array1); $s++) {
            if ($we_array1[$s] > 0) {
                $we_array[] = $s + 1;
            }
        }

        $i = 0;
        $numMachinesFormes = array();
        $angle = 0;
        $departX = 300 + 250 * cos(0);
        $departY = 300 - 250 * sin(0);

        $test = '<g fill="rgb(97, 115, 169)">';
        $test .= '<title>Réservations</title>';
        $test .= '<desc>287</desc>';
        $test .= '<rect x="0" y="0" width="1500" height="600" fill="white" stroke="black" stroke-width="0"/>';
        $couleur = array("#FC441D", "#FE8D11", "#FCC212", "#FFFD32", "#D0E92B", "#53D745", "#6AC720", "#156947", "#291D81", "#804DA4", "#E4AADF", "#A7194B", "#FE0000",
            "#FC441D", "#FE8D11", "#FCC212", "#FFFD32", "#D0E92B", "#53D745", "#6AC720", "#156947", "#291D81", "#804DA4", "#E4AADF", "#A7194B", "#FE0000"
        );

        foreach ($machinesFormesListe as $mFL) {

            $sql = 'SELECT name FROM re_info WHERE id_space=? AND deleted=0 AND id =?';
            $req = $this->runRequest($sql, array($id_space, $mFL[0]));
            $res = $req->fetchAll();
            $nomMachine = "-";
            if (count($res) > 0) {
                $nomMachine = $res[0][0];
            }

            if ($nomMachine != "-") {
                // get the resource type

                $sql = 'SELECT * FROM bk_calendar_entry WHERE id_space=? AND deleted=0 AND start_time >=' . mktime(0, 0, 0, $month_start, 1, $year_start) . ' AND end_time <=' . mktime(0, 0, 0, $month_end + 1, 1, $year_end) . ' AND resource_id ="' . $mFL[0] . '"';
                $req = $this->runRequest($sql, array($id_space));
                $numMachinesFormes[$i][0] = $mFL[0];

                $resas = $req->fetchAll();
                $timeResa = 0.0;
                $timeResaNight = 0.0;
                $timeResaWe = 0.0;
                foreach ($resas as $resa) {
                    $timeResaArray = $this->calculateReservationTime($resa["start_time"], $resa["end_time"], $night_start, $night_end, $we_array);
                    $timeResa += $timeResaArray[0];
                    $timeResaNight += $timeResaArray[1];
                    $timeResaWe += $timeResaArray[2];
                }
                //echo "timeResa = " . $timeResa . "<br/>";
                $numMachinesFormes[$i][1] = round($timeResa, 1);
                $numMachinesFormes[$i][2] = round($timeResaNight, 1);
                $numMachinesFormes[$i][3] = round($timeResaWe, 1);

                $curentAngle = 2 * pi() * ($numMachinesFormes[$i][1] + $numMachinesFormes[$i][2] + $numMachinesFormes[$i][3]) / $numMachinesFormesTotal;

                if ($curentAngle > pi()) {

                    $angle += $curentAngle / 2;

                    $arriveeX = 300 + 250 * cos($angle);
                    $arriveeY = 300 - 250 * sin($angle);

                    $test .= '<path d="M ' . $departX . ' ' . $departY . ' A 250 250 0 0 0 ' . $arriveeX . ' ' . $arriveeY . ' L 300 300" fill="' . $couleur[$i] . '" stroke="black" stroke-width="0"  />';
                    $test .= '<g>';

                    $departX = $arriveeX;
                    $departY = $arriveeY;
                    $angle += $curentAngle / 2;
                } else {
                    $angle += $curentAngle;
                }

                $arriveeX = 300 + 250 * cos($angle);
                $arriveeY = 300 - 250 * sin($angle);

                $test .= '<path d="M ' . $departX . ' ' . $departY . ' A 250 250 0 0 0 ' . $arriveeX . ' ' . $arriveeY . ' L 300 300" fill="' . $couleur[$i] . '"/>';
                $test .= '<g>';
                $test .= '<rect x="580" y="' . (83 + 40 * $i) . '" width="30" height="20" rx="5" ry="5" fill="' . $couleur[$i] . '" stroke="' . $couleur[$i] . '" stroke-width="0"/>';

                if ($numMachinesFormes[$i][2] == 0 && $numMachinesFormes[$i][3] == 0) {
                    $test .= '<text x="615" y="' . (90 + 40 * $i) . '" font-size="20" fill="black" stroke="none" text-anchor="start" baseline-shift="-11px">' . $nomMachine . ' : ' . $numMachinesFormes[$i][1] . "" . '</text>';
                } else {
                    $test .= '<text x="615" y="' . (90 + 40 * $i) . '" font-size="20" fill="black" stroke="none" text-anchor="start" baseline-shift="-11px">' . $nomMachine . ' : ' . $numMachinesFormes[$i][1] . "|" . $numMachinesFormes[$i][2] . "|" . $numMachinesFormes[$i][3] . "" . '</text>';
                }
                $test .= '</g>';

                $departX = $arriveeX;
                $departY = $arriveeY;
                $i++;
            }
        }

        $test .= '</g>';
        return $test;
    }

}
