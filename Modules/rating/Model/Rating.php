<?php

require_once 'Framework/Model.php';


require_once 'Framework/Events.php';


class RatingCampaign extends Model {

    public function __construct() {
        $this->tableName = 'rating_campaign';
    }

    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `rating_campaign` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_space` int NOT NULL,
            `from_date` int NOT NULL,
            `to_date` int NOT NULL,
            `limit_date` int NOT NULL,
            `message` varchar(255) NOT NULL DEFAULT '',
            `mails` int NOT NULL DEFAULT 0,
            PRIMARY KEY (`id`)
        );";
        $this->runRequest($sql);
    }

    public function list(int $id_space, bool $open=false) {
        if($open) {
            $sql = "SELECT * from rating_campaign WHERE deleted=0 AND id_space=? AND limit_date > ? ORDER BY from_date DESC";
            $res = $this->runRequest($sql, [$id_space, time()]);

        } else {
            $sql = "SELECT * from rating_campaign WHERE deleted=0 AND id_space=? ORDER BY from_date DESC";
            $res = $this->runRequest($sql, [$id_space]);
        }
        return $res->fetchAll();
    }

    public function set(int $id_space, int $id, int $from_date, int $to_date, int $limit_date, string $message, int $mails=0) {
        $exists = $this->get($id_space, $id);
        if($exists) {
            $sql = 'UPDATE rating_campaign set from_date=?, to_date=?, limit_date=?, message=?, mails=? WHERE id_space=? AND id=?';
            $this->runRequest($sql, [$from_date, $to_date, $limit_date, $message, $mails, $id_space, $id]);
        } else {
            $sql = 'INSERT INTO rating_campaign (id_space, from_date, to_date, limit_date, message) VALUES (?, ?, ?, ?, ?)';
            $this->runRequest($sql, [$id_space, $from_date, $to_date, $limit_date, $message]);
            $id = $this->getDatabase()->lastInsertId();
        }
        return $id;
    }

    public function get(int $id_space, int $id) {
        $sql = "SELECT * from rating_campaign WHERE deleted=0 AND id_space=? AND id=?";
        $res = $this->runRequest($sql, [$id_space, $id]);
        if ($res->rowCount() > 0) {
            return $res->fetch();
        }
        return null;
    }

    public function anwers(int $id_space, int $id_campaign) {
        $sql = 'SELECT DISTINCT id_user FROM rating WHERE id_space=? AND campaign=?';
        $res = $this->runRequest($sql, [$id_space, $id_campaign]);
        return $res->fetchAll();
    }
}


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
        `campaign`int NOT NULL,
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

    public function get(int $id_space, int $id) {
        $params = [$id_space, $id];
        $sql = "SELECT rating.*, core_users.login as login FROM rating INNER JOIN core_users ON core_users.id=rating.id_user WHERE rating.deleted=0 AND rating.id_space=? AND rating.id=?";
        $sql .= " ORDER BY id DESC";
        $res = $this->runRequest($sql, $params);
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


    public function evaluated(int $id_space, string $module, int $resource, int $id_user, int $campaign=0) {
        $sql = "SELECT count(*) as total FROM rating WHERE deleted=0 AND id_user=? AND id_space=? AND module=? AND resource=?";
        $params = [$id_user, $id_space, $module, $resource];
        if($campaign) {
            $params[] = $campaign;
            $sql .= " AND campaign=? ";
        }
        $res = $this->runRequest($sql, $params)->fetch();
        if ($res['total'] > 0) {
            return true;
        }
        return false;
    }

    public function stat(int $id_space, $campaign=0) {
        $params = [$id_space];
        $sql = "SELECT module, resourcename,AVG(rate) as rate,count(*) as count FROM rating WHERE id_space=?";
        if($campaign) {
            $params[] = $campaign;
            $sql .= " AND campaign=? ";
        }
        $sql .= " GROUP BY module,resourcename";
        return $this->runRequest($sql,$params)->fetchAll();
    }

    public function statGlobal(int $id_space, $campaign=0) {
        $params = [$id_space];
        $sql = "SELECT module,AVG(rate) as rate,count(*) as count FROM rating WHERE id_space=?";
        if($campaign) {
            $params[] = $campaign;
            $sql .= " AND campaign=? ";
        }
        $sql .= " GROUP BY module";
        return $this->runRequest($sql,$params)->fetchAll();
    }

    public function list(int $id_space, string $module=null, int $from=null, int $campaign=0) {
        $sql = "SELECT rating.*, core_users.login as login from rating LEFT JOIN core_users on core_users.id=rating.id_user WHERE rating.deleted=0 AND rating.id_space=?";
        $cond = [$id_space];
        if($module) {
            $sql .= " AND module=?";
            $cond[] = $module;
        }
        if($from != null) {
          $sql .= " AND created_at >= ?";
          $cond[] = $from;
        }
        if($campaign) {
            $sql .= " AND campaign=?";
            $cond[] = $campaign;
        }
        $sql .= " ORDER BY id DESC";
        $res = $this->runRequest($sql, $cond);
        return $res->fetchAll();
    }

    public function set(int $id_space, int $campaign, int $id_user, int $id, string $module, int $resource, string $resourcename, int $rate, string $comment, int $anon=1) {
        $exists = $this->get($id_space, $id);
        if($exists) {
            $sql = 'UPDATE rating set campaign=?, rate=?,comment=?, anon=? WHERE id_space=? AND id_user=? AND id=?';
            $this->runRequest($sql, [$campaign, $rate, $comment, $anon, $id_space, $id_user, $id]);
        } else {
            $sql = 'INSERT INTO rating (campaign, rate, comment, id_space, id_user, module, resource, resourcename, anon) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)';
            $this->runRequest($sql, [$campaign, $rate, $comment, $id_space, $id_user, $module, $resource, $resourcename, $anon]);
            $id = $this->getDatabase()->lastInsertId();
        }
        return $id;
    }

}

?>