<?php

require_once 'Framework/Errors.php';
require_once 'Framework/Model.php';
require_once 'Framework/Configuration.php';

/**
 * Generic file handler
 */
class CoreHistory extends Model
{
    public function __construct()
    {
        $this->tableName = "core_history";
    }

    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `core_history` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `id_space` int(11) NOT NULL,
        `user` varchar(30) NOT NULL,
        `message` varchar(255) NOT NULL DEFAULT '',
        PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
    }

    /**
     * Create a new entry
     */
    public function add($id_space, $user, $message)
    {
        if (!$user) {
            return null;
        }
        $sql = 'INSERT INTO core_history (id_space, user, message) VALUES (?,?,?)';
        $this->runRequest($sql, array($id_space, $user, $message));
        return $this->getDatabase()->lastInsertId();
    }

    /**
     * List history events for space between from and to
     *
     * @param int $fromDate, timestamp
     * @param int $toDate , timestamp
     */
    public function list($id_space, $fromDate=null, $toDate=null)
    {
        if ($toDate == null) {
            $toDate = time();
        }
        if ($fromDate == null) {
            $fromDate = $toDate - 3600*24;
        }
        $sql = 'SELECT * from core_history WHERE id_space=? AND created_at>=? AND created_at<? ORDER BY created_at DESC';
        return $this->runRequest($sql, array($id_space, date('Y-m-d H:i:s', $fromDate), date('Y-m-d H:i:s', $toDate)))->fetchAll();
    }
}
