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
        `resourcename` VARCHAR(255) NOT NULL,
        `resource` int NOT NULL,
        `id_user` int NOT NULL,
		PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);
    }

    public function get(int $id_space, string $module, int $resource) {
        $sql = "SELECT * FROM rating WHERE deleted=0 AND id_space=? AND module=? AND resource=? ORDER BY id DESC";
        $res = $this->runRequest($sql, [$id_space, $module, $resource]);
        if ($res->rowCount() > 0) {
            return $res->fetch();
        }
        return null;
    }

    public function stat(int $id_space) {
        $sql = "SELECT module, resourcename,resource,AVG(rate) as rate,count(*) as count FROM rating WHERE id_space=? GROUP BY module,resource";
        return $this->runRequest($sql,[$id_space])->fetchAll();
    }

    public function list(int $id_space, string $module=null) {
        $sql = "SELECT * from rating WHERE deleted=0 AND id_space=? ORDER BY id DESC";
        $cond = [$id_space];
        if($module) {
            $sql .= " AND module=?";
            $cond[] = $module;
        }
        $res = $this->runRequest($sql, $cond);
        return $res->fetchAll();
    }

    public function set(int $id_space, int $id_user, string $module, int $resource, int $resourcename, int $rate, string $comment) {
        $exists = $this->get($id_space, $module, $resource);
        if($exists) {
            $sql = 'UPDATE rating set rate=?,comment=? WHERE id_space=? AND id_user=? AND module=? AND resource=?';
            $this->runRequest($sql, [$rate, $comment, $id_space, $id_user, $module, $resource]);
        } else {
            $sql = 'INSERT INTO rating (rate, comment, id_space, id_user, module, resource, resourcename) VALUES (?, ?, ?, ?, ?, ?, ?)';
            $this->runRequest($sql, [$rate, $comment, $id_space, $id_user, $module, $resource, $resourcename]);
        }
    }
}

?>