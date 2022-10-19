<?php

require_once 'Framework/Model.php';

/**
 * Class defining the GRR area model
 *
 * @author Sylvain Prigent
 */
class BkCalendarPeriod extends Model
{
    public function __construct()
    {
        $this->tableName = "bk_calendar_period";
    }

    /**
     * Create the calendar entry table
     *
     * @return PDOStatement
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `bk_calendar_period` (
		`id` int(11) NOT NULL AUTO_INCREMENT,	
		`choice` varchar(50) NOT NULL,	
		`optionval` varchar(50) NOT NULL,
        `enddate` DATE,
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);

        $this->addColumn('bk_calendar_period', 'enddate', 'date', "");
    }

    public function setEndDate($idSpace, $id, $date)
    {
        if ($date == "") {
            $date = null;
        }
        $sql = "UPDATE bk_calendar_period SET enddate=? WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($date, $id, $idSpace));
        return $id;
    }

    public function isCalPeriod($idSpace, $id)
    {
        $sql = "select * from bk_calendar_period where id=? AND id_space=?";
        $req = $this->runRequest($sql, array($id, $idSpace));
        return ($req->rowCount() == 1);
    }

    public function setPeriod($idSpace, $id, $choice, $option)
    {
        if ($this->isCalPeriod($idSpace, $id)) {
            $sql = "UPDATE bk_calendar_period SET choice=?, optionval=? WHERE id=? AND id_space=?";
            $this->runRequest($sql, array($choice, $option, $id, $idSpace));
            return $id;
        } else {
            $sql = "INSERT INTO bk_calendar_period (choice, optionval, id_space) VALUES (?,?,?)";
            $this->runRequest($sql, array($choice, $option, $idSpace));
            return $this->getDatabase()->lastInsertId();
        }
    }

    public function getPeriod($idSpace, $id)
    {
        $sql = "SELECT * FROM bk_calendar_period WHERE id=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id, $idSpace));
        return $req->fetch();
    }

    public function deleteAllPeriodEntries($idSpace, $id)
    {
        if ($id > 0) {
            $sql2 = "DELETE FROM bk_calendar_entry WHERE period_id=? AND id_space=?";
            $this->runRequest($sql2, array($id, $idSpace));
        }
    }

    public function deleteAllPeriod($idSpace, $id)
    {
        if ($id > 0) {
            $sql = "UPDATE bk_calendar_period SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
            $this->runRequest($sql, array($id, $idSpace));

            $sql2 = "UPDATE bk_calendar_entry SET deleted=1,deleted_at=NOW() WHERE period_id=? AND id_space=?";
            $this->runRequest($sql2, array($id, $idSpace));
        }
    }
}
