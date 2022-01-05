<?php

require_once 'Framework/Model.php';


require_once 'Framework/Events.php';

/**
 * Class defining the Rating model
 *
 */
class Rating extends Model {

    public function __construct() {
        $this->tableName = 'rating';
    }

    /**
     * Create the status table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `rating` (
		`id` int NOT NULL AUTO_INCREMENT,
        `id_space` int NOT NULL,
		`rate` int NOT NULL,
        `comment` VARCHAR(255),
        `module` VARCHAR(100) NOT NULL,
        `resource` int NOT NULL,
        `id_user` int NOT NULL,
		PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);
    }

    public function get(int $id_space, string $module, int $resource) {
        $sql = "SELECT * from rating WHERE deleted=0 AND id_space=? AND module=? AND resource=?";
        $res = $this->runRequest($sql, [$id_space, $module, $resource]);
        if ($res->rowCount() > 0) {
            return $res->fetch();
        }
        return null;
    }

    public function list(int $id_space, string $module=null) {
        $sql = "SELECT * from rating WHERE deleted=0 AND id_space=?";
        $cond = [$id_space];
        if($module) {
            $sql .= " AND module=?";
            $cond[] = $module;
        }
        $res = $this->runRequest($sql, $cond);
        return $res->fetchAll();
    }

    public function set(int $id_space, int $id_user, string $module, int $resource, int $rate, string $comment) {
        $exists = $this->get($id_space, $module, $resource);
        if($exists) {
            $sql = 'UPDATE rating set rate=?,comment=? WHERE id_space=? AND id_user=? AND module=? AND resource=?';
            $this->runRequest($sql, [$rate, $comment, $id_space, $id_user, $module, $resource]);
        } else {
            $sql = 'INSERT INTO rating (rate, comment, id_space, id_user, module, resource) VALUES (?, ?, ?, ?, ?, ?)';
            $this->runRequest($sql, [$rate, $comment, $id_space, $id_user, $module, $resource]);
        }
    }
}

?>