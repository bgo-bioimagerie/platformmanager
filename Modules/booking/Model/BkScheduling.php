<?php

require_once 'Framework/Model.php';

class BkResourceSchedule extends Model {

    public function __construct() {
        $this->tableName = "bk_resource_schedule";
    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `bk_resource_schedule` (
		    `id` int NOT NULL AUTO_INCREMENT,
            `id_space` int NOT NULL,
            `id_bkschedule` int NOT NULL,
            `id_rearea` int NOT NULL DEFAULT 0,
            `id_resource` int NOT NULL DEFAULT 0,
		    PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);
    }

    public function unlinkResource($id_space, $id_resource){
        $sql = 'DELETE FROM bk_resource_schedule WHERE id_space=? AND id_resource=?';
        $this->runRequest($sql, [$id_space, $id_resource]);
    }

    public function unlinkArea($id_space, $id_rearea){
        $sql = 'DELETE FROM bk_resource_schedule WHERE id_space=? AND id_rearea=?';
        $this->runRequest($sql, [$id_space, $id_rearea]);
    }

    public function unlinkAll($id_space, $id_bkschedule){
        $sql = 'DELETE FROM bk_resource_schedule WHERE id_space=? AND id_bkschedule=?';
        $this->runRequest($sql, [$id_space, $id_bkschedule]);
    }

    public function linkResource($id_space, $id_resource, $id_bkschedule){
        $sql = 'INSERT INTO bk_resource_schedule (id_space, id_bkschedule, id_resource) VALUES (?, ?, ?)';
        $this->runRequest($sql, [$id_space, $id_bkschedule, $id_resource]);
    }

    public function all($id_space) {
        $sql = 'SELECT bk_resource_schedule.*, bk_schedulings.name FROM bk_resource_schedule INNER JOIN bk_schedulings ON bk_schedulings.id=bk_resource_schedule.id_bkschedule WHERE id_space=?';
        return $this->runRequest($sql, [$id_space])->fetchAll();
    }

    public function linkedResource($id_space, $id_bkschedule) {
        $sql = 'SELECT * from bk_resource_schedule WHERE id_bkschedule=? AND id_space=? AND id_resource>0';
        return $this->runRequest($sql, [$id_bkschedule, $id_space])->fetchAll();
    }

    public function linkedArea($id_space, $id_bkschedule) {
        $sql = 'SELECT * from bk_resource_schedule WHERE id_bkschedule=? AND id_space=? AND id_rearea>0';
        return $this->runRequest($sql, [$id_bkschedule, $id_space])->fetchAll();
    }

    public function linkArea($id_space, $id_rearea, $id_bkschedule){
        $sql = 'INSERT INTO bk_resource_schedule (id_space, id_bkschedule, id_rearea) VALUES (?, ?, ?)';
        $this->runRequest($sql, [$id_space, $id_bkschedule, $id_rearea]);
    }

    public function setDefault($id_space, $id_bkschedule){
        $sql = 'INSERT INTO bk_resource_schedule (id_space, id_bkschedule) VALUES (?, ?)';
        $this->runRequest($sql, [$id_space, $id_bkschedule]);
    }

    public function getDefault($id_space){
        $sql = 'SELECT * from bk_resource_schedule WHERE id_space=? AND id_resource=0 AND id_rearea=0';
        $res = $this->runRequest($sql, [$id_space]);
        if($res->rowCount() == 1) {
            return $res->fetch();
        }
        return null;
    }

    /**
     * Get bkscheduling id for resource
     * If none, get the one defined for resource area
     * If none, get default one
     * Else return null
     */
    public function getResourceScheduling($id_space, $id_resource) {
        $sql = 'SELECT * from bk_resource_schedule WHERE id_space=? AND id_resource=?';
        $res = $this->runRequest($sql, [$id_space, $id_resource]);
        if($res->rowCount() == 1) {
            return $res->fetch();
        }

        $sql = 'SELECT id_area FROM re_info WHERE deleted=0 AND id_space=? AND id=?';
        $res = $this->runRequest($sql, [$id_space, $id_resource]);
        if($res->rowCount() == 1) {
            $area = $res->fetch();
            $id_rearea = $area['id_area'];
            $sql = 'SELECT * from bk_resource_schedule WHERE id_space=? AND id_rearea=?';
            $res = $this->runRequest($sql, [$id_space, $id_rearea]);
            if($res->rowCount() == 1) {
                return $res->fetch();
            }
        }

        $sql = 'SELECT * from bk_resource_schedule WHERE id_space=? AND id_rearea=0 AND id_resource=0';
        $res = $this->runRequest($sql, [$id_space]);
        if($res->rowCount() == 1) {
            return $res->fetch();
        }
        return null;
        
    }
}


/**
 * Class defining the booking scheduling model
 *
 * @author Sylvain Prigent
 */
class BkScheduling extends Model {

    public function __construct() {

        $this->tableName = "bk_schedulings";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("is_monday", "int(1)", 1);
        $this->setColumnsInfo("is_tuesday", "int(1)", 1);
        $this->setColumnsInfo("is_wednesday", "int(1)", 1);
        $this->setColumnsInfo("is_thursday", "int(1)", 1);
        $this->setColumnsInfo("is_friday", "int(1)", 1);
        $this->setColumnsInfo("is_saturday", "int(1)", 1);
        $this->setColumnsInfo("is_sunday", "int(1)", 1);
        $this->setColumnsInfo("day_begin", "int(2)", 8);
        $this->setColumnsInfo("day_end", "int(2)", 18);
        $this->setColumnsInfo("size_bloc_resa", "int(4)", 3600);
        $this->setColumnsInfo("booking_time_scale", "int(5)", 1);
        $this->setColumnsInfo("resa_time_setting", "int(1)", 1);
        $this->setColumnsInfo("default_color_id", "int(11)", 1);
        // $this->setColumnsInfo("id_rearea", "int(11)", 0);
        $this->setColumnsInfo("force_packages", "tinyint", 0);
        $this->setColumnsInfo('shared', 'tinyint', 0);
        $this->setColumnsInfo("name", "varchar(100)", "");
        $this->primaryKey = "id";
    }

    public function getDefault($id_space=0) {
        if($id_space > 0) {
            $bks = new BkResourceSchedule();
            $def = $bks->getDefault($id_space);
            if($def) {
                return $def;
            }
        }
        return array(
            "id" => 0,
            "id_space" => $id_space,
            "name" => "default",
            "is_monday" => 1,
            "is_tuesday" => 1,
            "is_wednesday" => 1,
            "is_thursday" => 1,
            "is_friday" => 1,
            "is_saturday" => 0,
            "is_sunday" => 0,
            "day_begin" => 8,
            "day_end" => 18,
            "size_bloc_resa" => 3600,
            "booking_time_scale" => 2,
            "resa_time_setting" => 2,
            "default_color_id" => 1,
            // "id_rearea" => 0,
            "force_packages" => 0,
            "shared" => 0
        );
    }

    
    protected function getClosest($search, $arr) {
        $closest = null;
        foreach ($arr as $item) {
            if ($closest === null || abs($search - $closest) > abs($item - $search)) {
                $closest = $item;
            }
        }
        return $closest;
    }

    public function getClosestMinutes($id_space, $id_resource, $minutes){
        if($minutes == "") {
            $minutes = 0;
        }
        $bks = new BkResourceSchedule();
        $sched = $bks->getResourceScheduling($id_space, $id_resource);
        if ($sched == null) {
            return $this->getDefault()['size_bloc_resa'];
        }
        $sql = "SELECT size_bloc_resa FROM bk_schedulings WHERE id=? AND id_space=?";
        // $sql = "SELECT size_bloc_resa FROM bk_schedulings WHERE id_rearea=(SELECT id_area FROM re_info WHERE id=? AND deleted=0 AND id_space=? )";
        $req = $this->runRequest($sql, array($sched['id'], $id_space));
        if ($req->rowCount() > 0){
            $d = $req->fetch();
            $step = $d[0];
            
            $values = array();
            $values[] = 0;
            $last = 0;
            while($last < 3600){
                $last += $step;
                $values[] = $last;
            }
            $m = $this->getClosest($minutes*60, $values);
            return $m/60;
        }
        return $minutes;
        
    }
    
    /**
     * get all calendars
     * 
     * @param string $sortentry Entry that is used to sort
     * @return multitype: array
     */
    public function getAll($id_space, $sortentry = 'id') {

        $sql = "SELECT * FROM bk_schedulings WHERE deleted=0 AND id_space=? order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    /**
     * Get color code info from ID
     * @param unknown $id
     * @return mixed
     */
    public function get($id_space, $id) {

        if (!$this->exists($id_space, $id)){
            return $this->getDefault();
        }
        
        $sql = "SELECT * FROM bk_schedulings WHERE id=? AND deleted=0 AND id_space=?";
        $user = $this->runRequest($sql, array($id, $id_space));
        return $user->fetch();
    }

    public function getByResource($id_space, $id_resource) {
        $bks = new BkResourceSchedule();
        $sched = $bks->getResourceScheduling($id_space, $id_resource);
        if ($sched == null) {
            return $this->getDefault();
        }
        $sql = "SELECT * FROM bk_schedulings WHERE id=? AND deleted=0 AND id_space=?";
        $res = $this->runRequest($sql, array($sched['id'], $id_space));
        $scheduling = $res->fetch();
        if (!$scheduling) {
            $scheduling = $this->getDefault($id_space);
        }
        return $scheduling;
    }

    /*
    public function getByReArea($id_space, $id_rearea) {
        $sql = "SELECT * FROM bk_schedulings WHERE id_rearea=? AND deleted=0 AND id_space=?";
        $res = $this->runRequest($sql, array($id_rearea, $id_space));
        $scheduling = $res->fetch();
        if (!$scheduling) {
            $scheduling = $this->getDefault();
        }
        return $scheduling;
    }
    */

    /**
     * add a schedule to the table
     *
     */
    public function add($id_space, $name, $is_monday, $is_tuesday, $is_wednesday, $is_thursday, $is_friday, $is_saturday, $is_sunday, $day_begin, $day_end, $size_bloc_resa, $booking_time_scale, $resa_time_setting, $default_color_id, $force_packages=0, $shared=0) {

        $sql = "insert into bk_schedulings(name, is_monday, is_tuesday, "
                . " is_wednesday, is_thursday, is_friday, is_saturday, is_sunday, day_begin,"
                . " day_end, size_bloc_resa, booking_time_scale,"
                . " resa_time_setting, default_color_id, id_space, force_packages, shared)"
                . " values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->runRequest($sql, array($name, $is_monday, $is_tuesday,
            $is_wednesday, $is_thursday, $is_friday, $is_saturday, $is_sunday, $day_begin,
            $day_end, $size_bloc_resa, $booking_time_scale,
            $resa_time_setting, $default_color_id, $id_space, $force_packages, $shared));
        return $this->getDatabase()->lastInsertId();
    }

    public function bkupdate($id_space, $id, $name, $is_monday, $is_tuesday, $is_wednesday, $is_thursday, $is_friday, $is_saturday, $is_sunday, $day_begin, $day_end, $size_bloc_resa, $booking_time_scale, $resa_time_setting, $default_color_id, $force_packages=0, $shared=0) {

        $sql = "UPDATE bk_schedulings SET name=?, is_monday=?, is_tuesday=?, is_wednesday=?, is_thursday=?, is_friday=?, "
                . "is_saturday=?, is_sunday=?, day_begin=?, day_end=?, size_bloc_resa=?, booking_time_scale=?, "
                . "resa_time_setting=?, default_color_id=?, force_packages=?, shared=? "
                . "WHERE id=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($name, $is_monday, $is_tuesday, $is_wednesday, $is_thursday, $is_friday,
            $is_saturday, $is_sunday, $day_begin, $day_end, $size_bloc_resa,
            $booking_time_scale, $resa_time_setting, $default_color_id, $force_packages, $shared, $id, $id_space));
    }

    protected function onToBool($on){
        if ($on == ""){
            return 0;
        }
        if ($on == "on"){
            return 1;
        }
        return $on;
    }
    
    public function edit(
        $id_space,
        $id,
        $name,
        $is_monday,
        $is_tuesday,
        $is_wednesday,
        $is_thursday,
        $is_friday,
        $is_saturday,
        $is_sunday,
        $day_begin,
        $day_end,
        $size_bloc_resa,
        $booking_time_scale,
        $resa_time_setting,
        $default_color_id,
        $force_packages,
        $shared
    ) {
        if ($id > 0) {
            $this->bkupdate(
                $id_space,
                $id,
                $name,
                $this->onToBool($is_monday),
                $this->onToBool($is_tuesday),
                $this->onToBool($is_wednesday),
                $this->onToBool($is_thursday),
                $this->onToBool($is_friday),
                $this->onToBool($is_saturday),
                $this->onToBool($is_sunday),
                $day_begin,
                $day_end,
                $size_bloc_resa,
                $booking_time_scale,
                $resa_time_setting,
                $default_color_id,
                $this->onToBool($force_packages),
                $shared
            );
        } else {
            $id = $this->add(
                $id_space,
                $name,
                $this->onToBool($is_monday),
                $this->onToBool($is_tuesday),
                $this->onToBool($is_wednesday),
                $this->onToBool($is_thursday),
                $this->onToBool($is_friday),
                $this->onToBool($is_saturday),
                $this->onToBool($is_sunday),
                $day_begin,
                $day_end,
                $size_bloc_resa,
                $booking_time_scale,
                $resa_time_setting,
                $default_color_id,
                $this->onToBool($force_packages),
                $shared
            );
        }
        return $id;
    }

    /**
     * Check if a bkScheduling code exists from id
     * @param unknown $id
     * @return boolean
     */
    public function exists($id_space, $id_bkScheduling) {
        $sql = "SELECT * from bk_schedulings WHERE id=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id_bkScheduling, $id_space));
        return ($req->rowCount() == 1);
    }

    public function nameExists($id_space, $name){
        $sql = "SELECT id FROM bk_schedulings WHERE id_space=? AND name=?";
        $res = $this->runRequest($sql, [$id_space, $name]);
        if($res->rowCount()>0) {
            return true;
        }
        return false;
    }

    public function getForSpace($id_space) {
        $sql = "SELECT * from bk_schedulings WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    /**
     * Remove a schedule
     * @param unknown $id
     */
    public function delete($id_space, $id) {
        $sql = "UPDATE bk_schedulings SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
        $bks = new BkResourceSchedule();
        $bks->unlinkAll($id_space, $id);
    }

}
