<?php

require_once 'Framework/Model.php';
require_once 'Framework/TableView.php';
require_once 'Framework/Constants.php';

/**
 * Class defining the ColorCode model
 *
 * @author Sylvain Prigent
 */
class BkColorCode extends Model
{
    public function __construct()
    {
        $this->tableName = "bk_color_codes";
    }

    /**
     * Create the SyColorCode table
     *
     * @return PDOStatement
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `bk_color_codes` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`name` varchar(30) NOT NULL DEFAULT '',
		`color` varchar(7) NOT NULL DEFAULT '',
		`text` varchar(7) NOT NULL DEFAULT '',		
		`display_order` int(11) NOT NULL DEFAULT 0,
        `id_space` int(11) NOT NULL DEFAULT 1,
        `who_can_use` int(11) NOT NULL DEFAULT 1,
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);

        $this->addColumn("bk_color_codes", "who_can_use", "int(11)", 1);
    }

    public function getDefault()
    {
        return array("id" => 0, "name" => "", "color" => Constants::COLOR_WHITE, "text" => Constants::COLOR_BLACK,
            "display_order" => 0, "id_space" => 0, "who_can_use" => 1);
    }

    public function getIdByNameSpace($name, $idSpace)
    {
        $sql = "SELECT id FROM bk_color_codes WHERE name=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $idSpace));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    /**
     * Create the default empty SyColorCode
     * @deprecated
     *
     * @return PDOStatement
     */
    public function createDefaultColorCode()
    {
        if (!$this->isColorCode("default", "b2dfee")) {
            $sql = "INSERT INTO bk_color_codes (name, color, text, id_space) VALUES(?,?,?,0)";
            $this->runRequest($sql, array("default", "b2dfee", "000000"));
        }
    }

    /**
     * get SyColorCodes informations
     *
     * @param string $sortentry Entry that is used to sort the SyColorCodes
     * @return multitype: array
     */
    public function getColorCodes($idSpace, $sortentry = 'id')
    {
        $sql = "select * from bk_color_codes WHERE id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($idSpace));
        return $user->fetchAll();
    }

    public function getColorCodesForListUser($idSpace, $userSPaceRole, $sortentry = 'id')
    {
        $sql = "select * from bk_color_codes WHERE id_space=? AND deleted=0 AND who_can_use<=? order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($idSpace, $userSPaceRole));
        $data = $user->fetchAll();
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["name"];
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    public function getColorCodesForList($idSpace, $sortentry = 'id')
    {
        $sql = "select * from bk_color_codes WHERE id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($idSpace));
        $data = $user->fetchAll();
        $names = array();
        $ids = array();
        foreach ($data as $d) {
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
    public function getColorCode($idSpace, $id)
    {
        $sql = "SELECT * FROM bk_color_codes WHERE id=? AND id_space=?";
        $user = $this->runRequest($sql, array($id, $idSpace));
        return $user->fetch();
    }

    public function getForSpace($idSpace)
    {
        $sql = "SELECT * FROM bk_color_codes WHERE id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($idSpace));
        return $user->fetchAll();
    }

    public function getForList($idSpace)
    {
        $sql = "SELECT * FROM bk_color_codes WHERE id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($idSpace))->fetchAll();
        $names = array();
        $ids = array();
        $names[] = "";
        $ids[] = 0;
        foreach ($data as $d) {
            $names[] = $d['name'];
            $ids[] = $d['id'];
        }
        return array('names' => $names, 'ids' => $ids);
    }

    /**
     * get the names of all the SyColorCodes
     * @deprecated
     *
     * @return multitype: array
     */
    public function colorCodesName($idSpace)
    {
        $sql = "select name from bk_color_codes WHERE deleted=0 AND id_space=?";
        $SyColorCodes = $this->runRequest($sql, array($idSpace));
        return $SyColorCodes->fetchAll();
    }

    /**
     * Get the SyColorCodes ids and names
     * @deprecated
     *
     * @return array
     */
    public function colorCodesIDName($idSpace)
    {
        $sql = "select id, name from bk_color_codes WHERE deleted=0 AND id_space=?";
        $SyColorCodes = $this->runRequest($sql, array($idSpace));
        return $SyColorCodes->fetchAll();
    }

    /**
     * add a SyColorCode to the table
     *
     * @param string $name name of the SyColorCode
     * @param string $address address of the SyColorCode
     */
    public function addColorCode($name, $color, $text_color, $idSpace, $display_order = 0)
    {
        $sql = "insert into bk_color_codes(name, color, text, id_space, display_order)"
                . " values(?, ?, ?, ?, ?)";
        $this->runRequest($sql, array($name, $color, $text_color, $idSpace, $display_order));
        return $this->getDatabase()->lastInsertId();
    }

    public function setColorWhoCanUse($idSpace, $id, $who_can_use)
    {
        $sql = "UPDATE bk_color_codes SET who_can_use=? WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($who_can_use, $id, $idSpace));
    }

    /**
     * update the information of a SyColorCode
     *
     * @param int $id Id of the SyColorCode to update
     * @param string $name New name of the SyColorCode
     * @param string $color New Address of the SyColorCode
     */
    public function updateColorCode($id, $name, $color, $text_color, $idSpace, $display_order)
    {
        $sql = "UPDATE bk_color_codes SET name=?, color=?, text=?, display_order=? WHERE id=? AND deleted=0 AND id_space=?";
        $this->runRequest($sql, array("" . $name . "", "" . $color . "", $text_color, $display_order, $id, $idSpace));
    }

    public function editColorCode($id, $name, $color, $text_color, $idSpace, $display_order)
    {
        if ($this->isColorCodeId($idSpace, $id)) {
            $this->updateColorCode($id, $name, $color, $text_color, $idSpace, $display_order);
            return $id;
        } else {
            return $this->addColorCode($name, $color, $text_color, $idSpace, $display_order);
        }
    }

    /**
     * CHeck if a color code exists from name
     * @param unknown $name
     * @return boolean
     */
    public function isColorCode($idSpace, $name)
    {
        $sql = "SELECT * FROM bk_color_codes WHERE name=? AND deleted=0 AND id_space=?";
        $colorCode = $this->runRequest($sql, array($name, $idSpace));
        return ($colorCode->rowCount() == 1);
    }

    /**
     * Check if a color code exists from id
     * @param unknown $id
     * @return boolean
     */
    public function isColorCodeId($idSpace, $id)
    {
        $sql = "select * from bk_color_codes where id=? AND deleted=0 AND id_space=?";
        $colorCode = $this->runRequest($sql, array($id, $idSpace));
        return ($colorCode->rowCount() == 1);
    }

    /**
     * get the color of a SyColorCode from ID
     *
     * @param int $id Id of the SyColorCode to query
     * @throws Exception id the SyColorCode is not found
     * @return mixed array
     */
    public function getColorCodeValue($idSpace, $id)
    {
        $sql = "select color from bk_color_codes where id=? AND deleted=0 AND id_space=?";
        $colorCode = $this->runRequest($sql, array($id, $idSpace));
        if ($colorCode->rowCount() == 1) {
            $tmp = $colorCode->fetch();
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
    public function getColorCodeText($idSpace, $id)
    {
        $sql = "select text from bk_color_codes where id=? AND deleted=0 AND id_space=?";
        $colorCode = $this->runRequest($sql, array($id, $idSpace));
        if ($colorCode->rowCount() == 1) {
            $tmp = $colorCode->fetch();
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
    public function getColorCodeName($idSpace, $id)
    {
        $sql = "select name from bk_color_codes where id=? AND deleted=0 AND id_space=?";
        $colorCode = $this->runRequest($sql, array($id, $idSpace));
        if ($colorCode->rowCount() == 1) {
            $tmp = $colorCode->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            return "";
        }
    }

    /**
     * get the id of a SyColorCode from it's name
     * @deprecated
     *
     * @param string $name Name of the SyColorCode
     * @throws Exception if the SyColorCode connot be found
     * @return mixed array
     */
    public function getColorCodeId($name, $idSpace)
    {
        $sql = "SELECT id FROM bk_color_codes WHERE name=? AND id_space=? AND deleted=0";
        $colorCode = $this->runRequest($sql, array($name, $idSpace));
        if ($colorCode->rowCount() == 1) {
            $tmp = $colorCode->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            throw new PfmParamException("Cannot find the colorCode using the given name", 404);
        }
    }

    /**
     * Remove a color code
     * @param unknown $id
     */
    public function delete($idSpace, $id)
    {
        $sql = "UPDATE bk_color_codes SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
