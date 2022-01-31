<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreVirtual.php';

/**
 * Class defining the Area model
 *
 * @author Sylvain Prigent
 */
class InInvoice extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {

        $this->tableName = "in_invoice";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("number", "varchar(50)", "");
        $this->setColumnsInfo("id_space", "int(11)", "");
        $this->setColumnsInfo("period_begin", "date", "");
        $this->setColumnsInfo("period_end", "date", "");
        $this->setColumnsInfo("date_generated", "date", "");
        $this->setColumnsInfo("date_send", "date", "");
        $this->setColumnsInfo("visa_send", "int(11)", 0);
        $this->setColumnsInfo("date_paid", "date", "");
        $this->setColumnsInfo("id_unit", "int(11)", 0);
        $this->setColumnsInfo("id_responsible", "int(11)", 0);
        $this->setColumnsInfo("total_ht", "varchar(50)", "0");
        $this->setColumnsInfo("id_project", "int(11)", 0);
        $this->setColumnsInfo('title', 'varchar(255)', "");
        $this->setColumnsInfo("is_paid", "int(1)", 0);
        $this->setColumnsInfo("module", "varchar(200)", "");
        $this->setColumnsInfo("controller", "varchar(200)", "");
        $this->setColumnsInfo("id_edited_by", "int(11)", 0);
        $this->setColumnsInfo("discount", "varchar(100)", 0);
        $this->primaryKey = "id";
    }
    
    public function mergeUsers($users){
        for($i = 1 ; $i < count($users) ; $i++){
            $sql = "UPDATE in_invoice SET id_responsible=? WHERE id_responsible=?";
            $this->runRequest($sql, array($users[0], $users[$i]));
        }
    }


    public function getIdFromName($name, $id_space){
        $sql = "SELECT id FROM in_invoice WHERE number=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $id_space));
        if ($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function setSend($id_space, $id, $date, $visa){
        if($date == "") {
            $date = null;
        }
        $sql = "UPDATE in_invoice SET date_send=?, visa_send=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($date, $visa, $id, $id_space));
    }
    
    public function setDiscount($id_space, $id, $discount) {
        $sql = "UPDATE in_invoice SET discount=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($discount, $id, $id_space));
    }

    public function getDiscount($id_space, $id) {
        $sql = "SELECT discount FROM in_invoice WHERE id=? AND id_space=? AND deleted=0";
        $d = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $d[0];
    }

    public function get($id_space, $id) {
        $sql = "SELECT * FROM in_invoice WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function getByNumber($id_space, $number) {
        $sql = "SELECT * FROM in_invoice WHERE number=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($number, $id_space))->fetch();
    }

    public function setTitle($id_space ,$id_invoice, $title) {
        $sql = "UPDATE in_invoice SET title=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($title, $id_invoice, $id_space));
    }

    public function setTotal($id_space, $id_invoice, $total) {
        $sql = "UPDATE in_invoice SET total_ht=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($total, $id_invoice, $id_space));
    }

    public function setDatePaid($id_space, $id, $date) {
        //echo "set date = " . $date . "<br/>";
        //echo "where id = " . $id . "<br/>";
        if($date == "") {
            $date = null;
        }
        $sql = "UPDATE in_invoice SET date_paid=?, is_paid=1 WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($date, $id, $id_space));
        
        //$sql2 = "UPDATE in_invoice SET is_paid=1 WHERE id=? AND id_space=? AND deleted=0";
        //$this->runRequest($sql2, array($id, $id_space));
    }

    public function setEditedBy($id_space, $id_invoice, $id_user) {
        $sql = "UPDATE in_invoice SET id_edited_by=? WHERE id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_user, $id_invoice, $id_space));
    }

    public function addInvoice($module, $controller, $id_space, $number, $date_generated, $id_responsible, $total_ht = 0, $period_begin = null, $period_end = null, $id_project = 0) {
        if($date_generated == "") {
            $date_generated = null;
        }
        if($period_begin == "") {
            $period_begin = null;
        }
        if($period_end == "") {
            $period_end = null;
        }
        $sql = "INSERT INTO in_invoice (module, controller, id_space, number, date_generated, id_unit, id_responsible, total_ht, period_begin, period_end, id_project) VALUES (?,?,?,?,?,?,?,?,?,?,?)";
        $this->runRequest($sql, array($module, $controller, $id_space, $number, $date_generated, 0, $id_responsible, $total_ht, $period_begin, $period_end, $id_project));
        return $this->getDatabase()->lastInsertId();
    }

    public function getAll($sortentry = "number") {
        $sql = "SELECT in_invoice.*, ec_units.name AS unit, core_users.name AS resp, core_users.firstname AS respfirstname "
                . "FROM in_invoice "
                . "INNER JOIN ec_units ON ec_units.id=in_invoice.id_unit "
                . "INNER JOIN core_users ON core_users.id=in_invoice.id_responsible "
                . "WHERE deleted=0 "
                . "ORDER BY " . $sortentry . " DESC;";
        return $this->runRequest($sql)->fetchAll();
    }

    public function getBySpace($id_space, $sortentry = "number") {
        $sql = "SELECT in_invoice.*, ec_units.name AS unit, core_users.name AS resp, core_users.firstname AS respfirstname "
                . "FROM in_invoice "
                . "INNER JOIN ec_units ON ec_units.id=in_invoice.id_unit "
                . "INNER JOIN core_users ON core_users.id=in_invoice.id_responsible "
                . "WHERE in_invoice.id_space=? AND in_invoice.deleted=0 "
                . "ORDER BY " . $sortentry . " DESC;";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getByPeriod($id_space, $begin, $end, $sortentry = "number") {
        $sql = "SELECT in_invoice.*, ec_units.name AS unit, core_users.name AS resp, core_users.firstname AS respfirstname "
                . "FROM in_invoice "
                . "INNER JOIN ec_units ON ec_units.id=in_invoice.id_unit "
                . "INNER JOIN core_users ON core_users.id=in_invoice.id_responsible "
                . "WHERE in_invoice.deleted=0 AND in_invoice.id_space=? AND in_invoice.date_generated >=? AND in_invoice.date_generated <=? "
                . "ORDER BY " . $sortentry . " DESC;";
        return $this->runRequest($sql, array($id_space, $begin, $end))->fetchAll();
    }
    
    public function getSentByPeriod($id_space, $sent, $begin, $end, $sortentry = "number"){
        
        $dateSendCondition = "";
        if($sent == 0){
            $dateSendCondition = "AND date_send is null ";
        }
        else{
            $dateSendCondition = "AND date_send is not null ";
        }
        
        $sql = "SELECT in_invoice.*, cl_clients.name AS resp "
                . "FROM in_invoice "
                . "INNER JOIN cl_clients ON cl_clients.id=in_invoice.id_responsible "
                . "WHERE in_invoice.deleted=0 AND in_invoice.id_space=? AND in_invoice.date_generated >=? AND in_invoice.date_generated <=? "
                . $dateSendCondition
                . "ORDER BY " . $sortentry . " DESC;";
        return $this->runRequest($sql, array($id_space, $begin, $end))->fetchAll();
    }

    // @bug, should get unique ids, no increment (parallel requests will result in conflicts)
    public function getNextNumber(int $id_space) {

        $cv = new CoreVirtual();
        $curYear = date("Y", time());
        $newNumber = $cv->incr($id_space, "invoices:$curYear");

        $num = "";
        if ($newNumber < 10) {
            $num = "000" . $newNumber;
        } else if ($newNumber >= 10 && $newNumber < 100) {
            $num = "00" . $newNumber;
        } else if ($newNumber >= 100 && $newNumber < 1000) {
            $num = "0" . $newNumber;
        } else {
            $num = $newNumber;
        }
        return $curYear . "-" . $num;

        /*
        if ($previousNumber == "") {
            $sql = "SELECT * FROM in_invoice ORDER BY number DESC;";
            $req = $this->runRequest($sql);

            $lastNumber = "";
            if ($req->rowCount() > 0) {
                $bill = $req->fetch();
                $lastNumber = $bill["number"];
            }
        } else {
            $lastNumber = $previousNumber;
        }
        if ($lastNumber != "") {
            //echo "lastNumber = " . $lastNumber . "<br/>";
            $lastNumber = explode("-", $lastNumber);
            $lastNumberY = $lastNumber[0];
            $lastNumberN = $lastNumber[1];

            if ($lastNumberY == date("Y", time())) {
                $lastNumberN = (int) $lastNumberN + 1;
            } else {
                return date("Y", time()) . "-0001";
            }
            $num = "";
            if ($lastNumberN < 10) {
                $num = "000" . $lastNumberN;
            } else if ($lastNumberN >= 10 && $lastNumberN < 100) {
                $num = "00" . $lastNumberN;
            } else if ($lastNumberN >= 100 && $lastNumberN < 1000) {
                $num = "0" . $lastNumberN;
            }
            return $lastNumberY . "-" . $num;
        } else {
            return date("Y", time()) . "-0001";
        }
        */
    }

    public function allPeriodYears($id_space, $periodBegin, $periodEnd){
        $sql = "SELECT date_generated FROM in_invoice WHERE id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();

        if(!$periodBegin) {
            $periodBegin = "0000-01-01";
        }
        if(!$periodEnd) {
            $periodEnd = "0000-12-31";
        }
        
        // extract years
        if (count($data) > 0) {
            $firstDate = $data[0][0];
            $firstDateInfo = explode("-", $firstDate);
            $firstYear = $firstDateInfo[0];
            $i = 0;
            while ($firstYear == "0000") {
                $i++;
                $firstDate = $data[$i][0];
                $firstDateInfo = explode("-", $firstDate);
                $firstYear = $firstDateInfo[0];
            }
            
            $periodBeginInfo = explode('-', $periodBegin);
            if ( $firstDateInfo[1]."-".$firstDateInfo[2] >=  $periodBeginInfo[1] ."-".$periodBeginInfo[2] ){
                $firstYear = $firstYear-1;
            }

            // last year
            $lastDate = $data[count($data) - 1][0];
            $lastDateInfo = explode("-", $lastDate);
            $periodEndInfo = explode('-', $periodEnd);
            if ( $lastDateInfo[1]."-".$lastDateInfo[2] >=  $periodEndInfo[1] ."-".$periodEndInfo[2] ){
                $lastYear = $lastDateInfo[0] +1;
            }
            else{
                $lastYear = $lastDateInfo[0];
            }
            
            $years = array();
            for ($i = $firstYear; $i <= $lastYear; $i++) {
                $years[] = $i;
            }
            
            return $years;
        }
        return array();
    }
    
    public function allYears($id_space) {

        $sql = "SELECT date_generated FROM in_invoice WHERE id_space=? AND deleted=0 ORDER BY date_generated ASC";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();

        if ($data && !empty($data)) {
            $firstYear = null;
            $lastYear = null;
            foreach($data as $invoice_date) {
                if($invoice_date["date_generated"] != null) {
                    $lastDateInfo = explode("-", $invoice_date["date_generated"]);
                    $lastYear = $lastDateInfo[0];
                    if($firstYear == null) {
                        $firstDateInfo = explode("-", $invoice_date["date_generated"]);
                        $firstYear = $firstDateInfo[0];
                    }
                }
            }

            // $firstDateInfo = explode("-", $firstDate);
            // $firstYear = $firstDateInfo[0];
            /*
            $i = 0;
            while ($firstYear == "0000") {
                $i++;
                $firstDate = $data[$i]["date_generated"];
                $firstDateInfo = explode("-", $firstDate);
                $firstYear = $firstDateInfo[0];
            }


            $lastDate = $data[count($data) - 1]["date_generated"];
            $lastDateInfo = explode("-", $lastDate);
            $lastYear = $lastDateInfo[0];
            */

            $years = array();
            for ($i = $firstYear; $i <= $lastYear; $i++) {
                $years[] = $i;
            }
            return $years;
        }
        return array();
    }

    public function getAllInvoicesPeriod($periodStart, $periodEnd, $id_space) {
        $sql = "select * from in_invoice WHERE date_generated >= ? AND date_generated <= ? AND id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($periodStart, $periodEnd, $id_space));
        return $user->fetchAll();
    }

    public function getInvoicesPeriod($controller, $periodStart, $periodEnd, $id_space) {
        $sql = "select * from in_invoice WHERE date_generated >= ? "
                . "AND date_generated <= ? AND controller=? "
                . "AND id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($periodStart, $periodEnd, $controller, $id_space));
        return $user->fetchAll();
    }

    public function getInvoiceNumber($id_space, $id_invoice) {
        $sql = "SELECT number FROM in_invoice WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_invoice, $id_space));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return "";
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE in_invoice SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        // $sql = "DELETE FROM in_invoice WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

    public function mergeUnits($units){
        
        for( $i = 1 ; $i<count($units) ; $i++){
            $sql = "UPDATE in_invoice SET id_unit=? WHERE id_unit=?";
            $this->runRequest($sql, array($units[0], $units[$i]));
        }
    }
}
