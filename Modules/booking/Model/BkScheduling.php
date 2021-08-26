<?php

require_once 'Framework/Model.php';

/**
 * Class defining the SyColorCode model
 *
 * @author Sylvain Prigent
 */
class BkScheduling extends Model {

    /**
     * Create the SyColorCode table
     * 
     * @return PDOStatement
     */
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
        $this->setColumnsInfo("id_rearea", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function getDefault() {
        return array("id" => 0, "is_monday" => 1, "is_tuesday" => 1,
            "is_wednesday" => 1, "is_thursday" => 1, "is_friday" => 1,
            "is_saturday" => 1, "is_sunday" => 1, "day_begin" => 8,
            "day_end" => 18, "size_bloc_resa" => 3600, "booking_time_scale" => 1,
            "resa_time_setting" => 1, "default_color_id" => 1);
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
        $sql = "SELECT size_bloc_resa FROM bk_schedulings WHERE id=(SELECT id_area FROM re_info WHERE id=? AND deleted=0 AND id_space=? )";
        $req = $this->runRequest($sql, array($id_resource, $id_space));
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
     * get SyColorCodes informations
     * 
     * @param string $sortentry Entry that is used to sort the SyColorCodes
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

    public function getByReArea($id_space, $id_rearea) {
        $sql = "SELECT * FROM bk_schedulings WHERE id_rearea=? AND deleted=0 AND id_space=?";
        $user = $this->runRequest($sql, array($id_rearea, $id_space));
        return $user->fetch();        
    }

    /**
     * add a SyColorCode to the table
     *
     * @param string $name name of the SyColorCode
     * @param string $address address of the SyColorCode
     */
    public function add($id_space, $is_monday, $is_tuesday, $is_wednesday, $is_thursday, $is_friday, $is_saturday, $is_sunday, $day_begin, $day_end, $size_bloc_resa, $booking_time_scale, $resa_time_setting, $default_color_id) {

        $sql = "insert into bk_schedulings(is_monday, is_tuesday, "
                . " is_wednesday, is_thursday, is_friday, is_saturday, is_sunday, day_begin,"
                . " day_end, size_bloc_resa, booking_time_scale,"
                . " resa_time_setting, default_color_id, id_space)"
                . " values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->runRequest($sql, array($is_monday, $is_tuesday,
            $is_wednesday, $is_thursday, $is_friday, $is_saturday, $is_sunday, $day_begin,
            $day_end, $size_bloc_resa, $booking_time_scale,
            $resa_time_setting, $default_color_id, $id_space));
    }

    /**
     * update the information of a SyColorCode
     *
     * @param int $id Id of the SyColorCode to update
     * @param string $name New name of the SyColorCode
     * @param string $color New Address of the SyColorCode
     */
    public function update2($id_space, $id, $is_monday, $is_tuesday, $is_wednesday, $is_thursday, $is_friday, $is_saturday, $is_sunday, $day_begin, $day_end, $size_bloc_resa, $booking_time_scale, $resa_time_setting, $default_color_id) {

        $sql = "UPDATE bk_schedulings SET is_monday=?, is_tuesday=?, is_wednesday=?, is_thursday=?, is_friday=?, "
                . "is_saturday=?, is_sunday=?, day_begin=?, day_end=?, size_bloc_resa=?, booking_time_scale=?, "
                . "resa_time_setting=?, default_color_id=? "
                . "WHERE id=? AND deleted=0 AND id_space=?";
        //echo "request = " . $sql . "<br/>";
        $this->runRequest($sql, array($is_monday, $is_tuesday, $is_wednesday, $is_thursday, $is_friday,
            $is_saturday, $is_sunday, $day_begin, $day_end, $size_bloc_resa,
            $booking_time_scale, $resa_time_setting, $default_color_id, $id, $id_space));
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
    
    public function edit($id_space, $id, $is_monday, $is_tuesday, $is_wednesday, $is_thursday, $is_friday, $is_saturday, $is_sunday, $day_begin, $day_end, $size_bloc_resa, $booking_time_scale, $resa_time_setting, $default_color_id) {
        if ($this->exists($id_space, $id)) {
            //echo "edit id=" . $id . "<br/>";
            $this->update2($id_space, $id, $this->onToBool($is_monday), $this->onToBool($is_tuesday), $this->onToBool($is_wednesday), $this->onToBool($is_thursday), $this->onToBool($is_friday), $this->onToBool($is_saturday), $this->onToBool($is_sunday), $day_begin, $day_end, $size_bloc_resa, $booking_time_scale, $resa_time_setting, $default_color_id);
        } else {
            $this->add($id_space, $this->onToBool($is_monday), $this->onToBool($is_tuesday), $this->onToBool($is_wednesday), $this->onToBool($is_thursday), $this->onToBool($is_friday), $this->onToBool($is_saturday), $this->onToBool($is_sunday), $day_begin, $day_end, $size_bloc_resa, $booking_time_scale, $resa_time_setting, $default_color_id);
        }
    }

    /**
     * CHeck if a color code exists from name
     * @param unknown $id
     * @return boolean
     */
    public function exists($id_space, $id) {
        $sql = "SELECT * from bk_schedulings WHERE id=? AND deleted=0 AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space));
        return ($req->rowCount() == 1);
    }

    /**
     * Remove a color code
     * @param unknown $id
     */
    public function delete($id_space, $id) {
        $sql = "UPDATE bk_schedulings SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        //$sql = "DELETE FROM bk_schedulings WHERE id = ? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

    public function setReArea($id_space, $id, $id_rearea) {
        $sql = "UPDATE bk_schedulings SET id_rearea=? WHERE id=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array($id_rearea, $id, $id_space));
    }

}
