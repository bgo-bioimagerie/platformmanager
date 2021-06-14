<?php

require_once 'Framework/Model.php';

/**
 * Class defining the GRR area model
 *
 * @author Sylvain Prigent
 */
class BkCalendarPeriod extends Model {

    /**
     * Create the calendar entry table
     *
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `bk_calendar_period` (
		`id` int(11) NOT NULL AUTO_INCREMENT,	
		`choice` varchar(50) NOT NULL,	
		`optionval` varchar(50) NOT NULL,
        `enddate` DATE NOT NULL,
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);
        
        $this->addColumn('bk_calendar_period', 'enddate', 'DATE', "0000-00-00");
    }

    public function setEndDate($id, $date) {
        $sql = "UPDATE bk_calendar_period SET enddate=?WHERE id=?";
        $this->runRequest($sql, array($date, $id));
        return $id;
    }

    public function isCalPeriod($id) {
        $sql = "select * from bk_calendar_period where id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function setPeriod($id, $choice, $option) {
        if ($this->isCalPeriod($id)) {
            //echo "update period <br/>";
            $sql = "UPDATE bk_calendar_period SET choice=?, optionval=? WHERE id=?";
            $this->runRequest($sql, array($choice, $option, $id));
            return $id;
        } else {
            //echo "insert period <br/>";
            $sql = "INSERT INTO bk_calendar_period (choice, optionval) VALUES (?,?)";
            $this->runRequest($sql, array($choice, $option));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function getPeriod($id) {
        $sql = "SELECT * FROM bk_calendar_period WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        return $req->fetch();
    }

    public function deleteAllPeriodEntries($id) {

        if ($id > 0) {
            $sql2 = "DELETE FROM bk_calendar_entry WHERE period_id=?";
            $this->runRequest($sql2, array($id));
        }
    }
    
    public function deleteAllPeriod($id) {

        if ($id > 0) {
            $sql = "DELETE FROM bk_calendar_period WHERE id=?";
            $this->runRequest($sql, array($id));

            $sql2 = "DELETE FROM bk_calendar_entry WHERE period_id=?";
            $this->runRequest($sql2, array($id));
        }
    }

}
