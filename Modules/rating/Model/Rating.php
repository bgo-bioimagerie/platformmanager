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
        `anon` tinyint(1) NOT NULL DEFAULT 1,
		PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);
    }

    public function get(int $id_space, string $module, int $resource) {
        $sql = "SELECT rating.*, core_users.login as login FROM rating INNER JOIN core_users ON core_users.id=rating.id_user WHERE rating.deleted=0 AND rating.id_space=? AND rating.module=? AND rating.resource=? ORDER BY id DESC";
        $res = $this->runRequest($sql, [$id_space, $module, $resource]);
        if ($res->rowCount() > 0) {
            $data = $res->fetchAll();
            foreach($data as $i => $d) {
                if($d['anon']) {
                    $data[$i]['id_user'] = 0;
                    $data[$i]['login'] = '';
                }
            }
            return $data;
        }
        return null;
    }


    public function evaluated(int $id_space, string $module, int $resource, int $id_user) {
        $sql = "SELECT count(*) as total FROM rating WHERE deleted=0 AND id_user=? AND id_space=? AND module=? AND resource=?";
        $res = $this->runRequest($sql, [$id_user, $id_space, $module, $resource])->fetch();
        if ($res['total'] > 0) {
            return true;
        }
        return false;
    }

    public function stat(int $id_space) {
        $sql = "SELECT module, resourcename,resource,AVG(rate) as rate,count(*) as count FROM rating WHERE id_space=? GROUP BY module,resource";
        return $this->runRequest($sql,[$id_space])->fetchAll();
    }

    public function list(int $id_space, string $module=null, int $from=null) {
        $sql = "SELECT * from rating WHERE deleted=0 AND id_space=?";
        $cond = [$id_space];
        if($module) {
            $sql .= " AND module=?";
            $cond[] = $module;
        }
        if($from == null) {
          $sql .= " AND created_at >= ?";
          $cond[] = $from;
        }
        $sql .= " ORDER BY id DESC";
        $res = $this->runRequest($sql, $cond);
        return $res->fetchAll();
    }

    public function set(int $id_space, int $id_user, string $module, int $resource, string $resourcename, int $rate, string $comment, int $anon=1) {
        $exists = $this->get($id_space, $module, $resource);
        if($exists) {
            $sql = 'UPDATE rating set rate=?,comment=?, anon=? WHERE id_space=? AND id_user=? AND module=? AND resource=?';
            $this->runRequest($sql, [$rate, $comment, $anon, $id_space, $id_user, $module, $resource]);
        } else {
            $sql = 'INSERT INTO rating (rate, comment, id_space, id_user, module, resource, resourcename, anon) VALUES (?, ?, ?, ?, ?, ?, ?, ?)';
            $this->runRequest($sql, [$rate, $comment, $id_space, $id_user, $module, $resource, $resourcename, $anon]);
        }
    }
}

?>