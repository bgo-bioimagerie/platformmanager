<?php

require_once 'Framework/Model.php';

/**
 * Class defining the tracking sheet for se_projects
 *
 * @author Sylvain Prigent
 */
class SeTaskCategory extends Model {

        public function __construct() {
        $this->tableName = "se_task_category";
    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `se_task_category` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_space` int NOT NULL,
            `id_project` int NOT NULL,
            `name` varchar(120) NOT NULL DEFAULT '',
            `position` int NOT NULL,
            `color` varchar(50) NOT NULL DEFAULT '',
            PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
    }

    public function getForSpace($id_space) {
        $sql = "SELECT * FROM se_task_category WHERE id_space=? AND deleted=0;";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }

    public function getByProject($id_project, $id_space) {
        $sql = "SELECT * FROM se_task_category WHERE id_space=? AND id_project=? AND deleted=0 ORDER BY position;";
        $req = $this->runRequest($sql, array($id_space, $id_project));
        $categories = $req->fetchAll();
        if (empty($categories)) {
            $categories = $this->createDefaultCategories($id_space, $id_project);
        }
        return $categories;
    }

    public function getDefaultCategories() {
        $cat1 = ["name" => "Backlog", "position" => 0, "color" => "#292b2c"];
        $cat2 = ["name" => "In Progress", "position" => 1, "color" => "#0275d8 "];
        $cat3 = ["name" => "Done", "position" => 2, "color" => "#5cb85c "];
        return array($cat1, $cat2, $cat3);
    }

    public function createDefaultCategories($id_space, $id_project) {
        $categories = $this->getDefaultCategories();
        for($i=0; $i < count($categories); $i++) {
            $sql = "INSERT INTO se_task_category (id_project, name, position, color, id_space) VALUES (?, ?, ?, ?, ?)";
            $this->runRequest($sql, array($id_project, $categories[$i]['name'], $categories[$i]['position'], $categories[$i]['color'], $id_space));
            $categories[$i]['id'] = $this->getDatabase()->lastInsertId(); 
        }
        return $categories;
    }
    
    public function set($id, $id_space, $id_project, $name, $position, $color) {
        if ($this->isTaskCategory($id_space, $id)) {
            $sql = "UPDATE se_task_category SET id_project=?, name=?, position=?, color=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($id_project, $name, $position, $color, $id, $id_space));
            return $id;
        } else {
            $sql = "INSERT INTO se_task_category (id_project, name, position, color, id_space) VALUES (?, ?, ?, ?, ?)";
            $this->runRequest($sql, array($id_project, $name, $position, $color, $id_space));
            return $this->getDatabase()->lastInsertId();
        }
    }
    
    public function isTaskCategory($id_space, $id) {
        $sql = "SELECT * FROM se_task_category WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() == 1) {
            return true;
        }
        return false;
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE se_task_category SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
