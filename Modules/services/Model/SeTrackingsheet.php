<?php

require_once 'Framework/Model.php';

/**
 * Class defining the tracking sheet for se_projects
 *
 * @author Sylvain Prigent
 */
class SeTrackingsheet extends Model {

        public function __construct() {
        $this->tableName = "se_tracking_sheet";
    }

    public function createTable() {
        // TODO: [tracking] complete db columns
        $sql = "CREATE TABLE IF NOT EXISTS `se_tracking_sheet` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_space` int NOT NULL,
            `id_project` int NOT NULL,
            `date` int NOT NULL,
            `comment` varchar(255) NOT NULL DEFAULT '',
            `doc_url` varchar(255) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
    }

    public function getForSpace($id_space) {
        $sql = "SELECT * FROM se_tracking_sheet WHERE id_space=? AND deleted=0 ORDER BY date DESC;";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }

    public function getByProject($id_project, $id_space) {
        $sql = "SELECT * FROM se_tracking_sheet WHERE id_space=? AND id_project=? deleted=0 ORDER BY date DESC;";
        $req = $this->runRequest($sql, array($id_space, $id_project));
        return $req->fetchAll();
    }
    
    // TODO: [tracking] replace $params by se_tracking_sheet attributes
    public function set($id, $id_space, $params) {
        if ($this->isTrackingSheet($id_space, $id)) {
            $sql = "UPDATE se_purchase SET params=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($params, $id, $id_space));
            return $id;
        }
        else {
            $sql = "INSERT INTO se_purchase (params, id_space) VALUES (?,?)";
            $this->runRequest($sql, array($params, $id_space));
            return $this->getDatabase()->lastInsertId();
        }
    }
    
    public function isTrackingSheet($id_space, $id){
        $sql = "SELECT * FROM se_tracking_sheet WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function delete($id_space, $id){
        $sql = "UPDATE se_tracking_sheet SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
