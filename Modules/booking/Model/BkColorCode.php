<?php

require_once 'Framework/Model.php';
require_once 'Framework/TableView.php';

/**
 * Class defining the SyColorCode model
 *
 * @author Sylvain Prigent
 */
class BkColorCode extends Model {

    /**
     * Create the SyColorCode table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `bk_color_codes` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(30) NOT NULL DEFAULT '',
		`color` varchar(7) NOT NULL DEFAULT '',
		`text` varchar(7) NOT NULL DEFAULT '',		
		`display_order` int(11) NOT NULL DEFAULT 0,
                `id_space` int(11) NOT NULL DEFAULT 1,
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);
    }

    public function getDefault() {
        return array("id" => 0, "name" => "", "color" => "#ffffff", "text" => "#000000", "display_order" => 0, "id_space" => 1);
    }

    /**
     * Create the default empty SyColorCode
     * 
     * @return PDOStatement
     */
    public function createDefaultColorCode() {

        if (!$this->isColorCode("default", "b2dfee")) {
            $sql = "INSERT INTO bk_color_codes (name, color, text) VALUES(?,?,?)";
            $this->runRequest($sql, array("default", "b2dfee", "000000"));
        }
    }

    /**
     * get SyColorCodes informations
     * 
     * @param string $sortentry Entry that is used to sort the SyColorCodes
     * @return multitype: array
     */
    public function getColorCodes($id_space, $sortentry = 'id') {

        $sql = "select * from bk_color_codes WHERE id_space=? order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }
    
    public function getColorCodesForList($id_space, $sortentry = 'id') {

        $sql = "select * from bk_color_codes WHERE id_space=? order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($id_space));
        $data = $user->fetchAll();
        $names = array(); $ids = array();
        foreach($data as $d){
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }
    

    /**
     * Get color code info from ID
     * @param unknown $id
     * @return mixed
     */
    public function getColorCode($id) {

        $sql = "SELECT * FROM bk_color_codes WHERE id=?";
        $user = $this->runRequest($sql, array($id));
        return $user->fetch();
    }

    public function getForSpace($id_space) {
        $sql = "SELECT * FROM bk_color_codes WHERE id_space=?";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    /**
     * get the names of all the SyColorCodes
     *
     * @return multitype: array
     */
    public function colorCodesName() {

        $sql = "select name from bk_color_codes";
        $SyColorCodes = $this->runRequest($sql);
        return $SyColorCodes->fetchAll();
    }

    /**
     * Get the SyColorCodes ids and names
     *
     * @return array
     */
    public function colorCodesIDName() {

        $sql = "select id, name from bk_color_codes";
        $SyColorCodes = $this->runRequest($sql);
        return $SyColorCodes->fetchAll();
    }

    /**
     * add a SyColorCode to the table
     *
     * @param string $name name of the SyColorCode
     * @param string $address address of the SyColorCode
     */
    public function addColorCode($name, $color, $text_color, $id_space, $display_order = 0) {

        $sql = "insert into bk_color_codes(name, color, text, id_space, display_order)"
                . " values(?, ?, ?, ?, ?)";
        $this->runRequest($sql, array($name, $color, $text_color, $id_space, $display_order));
        return $this->getDatabase()->lastInsertId();
    }

    /**
     * update the information of a SyColorCode
     *
     * @param int $id Id of the SyColorCode to update
     * @param string $name New name of the SyColorCode
     * @param string $color New Address of the SyColorCode
     */
    public function updateColorCode($id, $name, $color, $text_color, $id_space, $display_order) {

        $sql = "update bk_color_codes set name=?, color=?, text=?, id_space=?, display_order=? where id=?";
        $this->runRequest($sql, array("" . $name . "", "" . $color . "", $text_color, $id_space, $display_order, $id));
    }

    public function editColorCode($id, $name, $color, $text_color, $id_space, $display_order) {
        if ($this->isColorCodeId($id)) {
            $this->updateColorCode($id, $name, $color, $text_color, $id_space, $display_order);
        } else {
            $this->addColorCode($name, $color, $text_color, $id_space, $display_order);
        }
    }

    /**
     * CHeck if a color code exists from name
     * @param unknown $name
     * @return boolean
     */
    public function isColorCode($name) {
        $sql = "select * from bk_color_codes where name=?";
        $SyColorCode = $this->runRequest($sql, array($name));
        if ($SyColorCode->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if a color code exists from id
     * @param unknown $id
     * @return boolean
     */
    public function isColorCodeId($id) {
        $sql = "select * from bk_color_codes where id=?";
        $SyColorCode = $this->runRequest($sql, array($id));
        if ($SyColorCode->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * get the color of a SyColorCode from ID
     *
     * @param int $id Id of the SyColorCode to query
     * @throws Exception id the SyColorCode is not found
     * @return mixed array
     */
    public function getColorCodeValue($id) {
        $sql = "select color from bk_color_codes where id=?";
        $SyColorCode = $this->runRequest($sql, array($id));
        if ($SyColorCode->rowCount() == 1) {
            $tmp = $SyColorCode->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            return "aaaaaa";
        }
    }

    /**
     * get the text color of a SyColorCode from ID
     *
     * @param int $id Id of the SyColorCode to query
     * @throws Exception id the SyColorCode is not found
     * @return mixed array
     */
    public function getColorCodeText($id) {
        $sql = "select text from bk_color_codes where id=?";
        $SyColorCode = $this->runRequest($sql, array($id));
        if ($SyColorCode->rowCount() == 1) {
            $tmp = $SyColorCode->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            return "000000";
        }
    }

    /**
     * get the name of a SyColorCode
     *
     * @param int $id Id of the SyColorCode to query
     * @throws Exception if the SyColorCode is not found
     * @return mixed array
     */
    public function getColorCodeName($id) {
        $sql = "select name from bk_color_codes where id=?";
        $SyColorCode = $this->runRequest($sql, array($id));
        if ($SyColorCode->rowCount() == 1) {
            $tmp = $SyColorCode->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            return "";
        }
    }

    /**
     * get the id of a SyColorCode from it's name
     * 
     * @param string $name Name of the SyColorCode
     * @throws Exception if the SyColorCode connot be found
     * @return mixed array
     */
    public function getColorCodeId($name) {
        $sql = "select id from bk_color_codes where name=?";
        $SyColorCode = $this->runRequest($sql, array($name));
        if ($SyColorCode->rowCount() == 1) {
            $tmp = $SyColorCode->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            throw new Exception("Cannot find the SyColorCode using the given name");
        }
    }

    /**
     * Remove a color code
     * @param unknown $id
     */
    public function delete($id) {
        $sql = "DELETE FROM bk_color_codes WHERE id = ?";
        $req = $this->runRequest($sql, array($id));
    }

}
