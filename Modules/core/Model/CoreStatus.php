<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Status model
 *
 * @author Sylvain Prigent
 */
class CoreStatus extends Model {

    public static $USER = 1;
    public static $ADMIN = 2;

    public function __construct() {
        $this->tableName = "core_status";
    }
    
    /**
     * Create the status table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `core_status` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(30) NOT NULL DEFAULT '',
        PRIMARY KEY (`id`)
        );";

        $pdo = $this->runRequest($sql);


        $sql2 = "DELETE FROM core_status WHERE id > 5";
        $this->runRequest($sql2);
        return $pdo;
    }

    /**
     * Create the defaults status
     * 
     * @return PDOStatement
     */
    public function createDefaultStatus() {

        if (!$this->isStatus(1)) {
            $sql = 'INSERT INTO `core_status` (`name`) VALUES("user")';
            $pdo = $this->runRequest($sql);
        }

        if (!$this->isStatus(2)) {
            $sql = 'INSERT INTO `core_status` (`name`) VALUES("admin")';
            $pdo = $this->runRequest($sql);
        }
   }

    /**
     * Get all the status names
     * 
     * @return multitype: array
     */
    public function allStatus() {
        $sql = "select name from core_status";
        $status = $this->runRequest($sql);
        return $status->fetchAll();
    }
    
        /**
     * Get all the status
     * 
     * @return multitype: array
     */
    public function allStatusInfo() {
        $sql = "select * from core_status";
        $status = $this->runRequest($sql);
        return $status->fetchAll();
    }

    /**
     * Get the status Id and names
     * 
     * @return multitype: array
     */
    public function statusIDName() {
        $sql = "select id, name from core_status";
        $status = $this->runRequest($sql);
        return $status->fetchAll();
    }

    /**
     * get the name of a status from it id
     * 
     * @param int $id Id of the status to get
     * @throws Exception
     * @return mixed the status name if exists
     */
    public function getStatusName($id) {
        //echo "id status = " . $id;
        $sql = "select name from core_status where id=?";
        $status = $this->runRequest($sql, array($id));
        if ($status->rowCount() == 1) {
            $tmp = $status->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            return "";
        }
    }

    public function isStatus($id) {
        $sql = "select id from core_status where id=?";
        $user = $this->runRequest($sql, array($id));
        if ($user->rowCount() == 1) {
            return true; // get the first line of the result
        } else {
            return false;
        }
    }

}
