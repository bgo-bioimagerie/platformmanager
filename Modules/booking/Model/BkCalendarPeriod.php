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

    public function setEndDate($id_space, $id, $date) {
        $sql = "UPDATE bk_calendar_period SET enddate=? WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($date, $id, $id_space));
        return $id;
    }

    public function isCalPeriod($id_space, $id) {
        $sql = "select * from bk_calendar_period where id=? AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space));
        return ($req->rowCount() == 1);
    }

    public function setPeriod($id_space, $id, $choice, $option) {
        if ($this->isCalPeriod($id)) {
            $sql = "UPDATE bk_calendar_period SET choice=?, optionval=? WHERE id=? AND id_space=?";
            $this->runRequest($sql, array($choice, $option, $id, $id_space));
            return $id;
        } else {
            $sql = "INSERT INTO bk_calendar_period (choice, optionval, id_space) VALUES (?,?,?)";
            $this->runRequest($sql, array($choice, $option, $id_space));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function getPeriod($id_space, $id) {
        $sql = "SELECT * FROM bk_calendar_period WHERE id=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space));
        return $req->fetch();
    }

    public function deleteAllPeriodEntries($id_space, $id) {

        if ($id > 0) {
            $sql2 = "DELETE FROM bk_calendar_entry WHERE period_id=? AND id_space=?";
            $this->runRequest($sql2, array($id, $id_space));
        }
    }
    
    public function deleteAllPeriod($id_space, $id) {

        if ($id > 0) {
            $sql = "UPDATE bk_calendar_period SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
            // $sql = "DELETE FROM bk_calendar_period WHERE id=? AND id_space=?";
            $this->runRequest($sql, array($id, $id_space));

            $sql2 = "UPDATE bk_calendar_entry SET deleted=1,deleted_at=NOW() WHERE period_id=? AND id_space=?";
            //$sql2 = "DELETE FROM bk_calendar_entry WHERE period_id=? AND id_space=?";
            $this->runRequest($sql2, array($id, $id_space));
        }
    }

}
