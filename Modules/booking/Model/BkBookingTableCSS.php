<?php

require_once 'Framework/Model.php';

/**
 * Class defining the area model
 *
 * @author Sylvain Prigent
 */
class BkBookingTableCSS extends Model {

    public function __construct() {
        $this->tableName = "bk_bookingcss";
    }

    /**
     * Create the area table
     *
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `bk_bookingcss` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`id_area` int(11) NOT NULL,	
		`header_background` varchar(7) NOT NULL DEFAULT '#337ab7',
		`header_color` varchar(7) NOT NULL DEFAULT '#ffffff',
		`header_font_size` int(2) NOT NULL DEFAULT 12,
		`resa_font_size` int(2) NOT NULL DEFAULT 12,
		`header_height` int(3) NOT NULL DEFAULT 70,
		`line_height` int(3) NOT NULL DEFAULT 50,							
		PRIMARY KEY (`id`)
		);";

        return $this->runRequest($sql);
    }

    /**
     * Get the areas list
     * @param string $sortEntry Sort entry
     * @return multitype: tables of areas
     */
    public function areas($id_space, $sortEntry) {
        $sql = "select * from bk_bookingcss WHERE id_space=? AND deleted=0 order by " . $sortEntry . " ASC;";
        $data = $this->runRequest($sql, array($id_space));
        return $data->fetchAll();
    }

    public function getDefault() {
        return array("id" => 0,
            "header_background" => '#337ab7',
            "header_color" => '#ffffff',
            "header_font_size" => 12,
            "resa_font_size" => 12,
            "header_height" => 70,
            "line_height" => 50);
    }

    /**
     * Get the css for a given area
     * @param number $id Area ID
     * @return mixed|string CSS info or error message
     */
    public function getAreaCss($id_space, $id) {

        $sql = "select * from bk_bookingcss where id_area=? AND id_space=? AND deleted=0;";
        $data = $this->runRequest($sql, array($id, $id_space));
        if ($data->rowCount() == 1) {
            return $data->fetch();
        } else {
            return $this->getDefault();
        }
    }

    /**
     * add a Area to the table
     *
     * @param string $name name of the Area
     */
    private function addAreaCss($id_space, $id_area, $header_background, $header_color, $header_font_size, $resa_font_size, $header_height, $line_height) {

        $sql = "insert into bk_bookingcss(id_area, header_background, header_color, header_font_size, 
										  resa_font_size, header_height, line_height, id_space)"
                . " values(?,?,?,?,?,?,?,?)";
        $this->runRequest($sql, array($id_area, $header_background, $header_color, $header_font_size,
            $resa_font_size, $header_height, $line_height, $id_space));
    }

    /**
     * Check if an area exists
     * @param number $id_area
     * @return boolean
     */
    public function isAreaCss($id_space, $id_area) {
        $sql = "select * from bk_bookingcss where id_area=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id_area, $id_space));
        return ($unit->rowCount() == 1);
    }

    public function areaCssId($id_space, $id_area) {
        $sql = "select id from bk_bookingcss where id_area=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id_area, $id_space));
        if ($unit->rowCount() == 1) {
            return $unit->fetch();
        } else {
            return 0;
        }
    }

    /**
     * Add an area if not exists
     * @param string $name
     * @param number $display_order
     * @param number $restricted
     */
    public function setAreaCss($id_space, $id_area, $header_background, $header_color, $header_font_size, $resa_font_size, $header_height, $line_height) {
        if (!$this->isAreaCss($id_space, $id_area)) {
            $this->addAreaCss($id_space, $id_area, $header_background, $header_color, $header_font_size, $resa_font_size, $header_height, $line_height);
        } else {
            $id = $this->areaCssId($id_space, $id_area);
            $this->updateAreaCss($id_space, $id[0], $id_area, $header_background, $header_color, $header_font_size, $resa_font_size, $header_height, $line_height);
        }
    }

    /**
     * Update a area info
     * @param number $id ID of the area to edit 
     * @param string $name New name
     * @param number $display_order New display order
     * @param number $restricted New restriction
     */
    public function updateAreaCss($id_space, $id, $id_area, $header_background, $header_color, $header_font_size, $resa_font_size, $header_height, $line_height) {
        $sql = "update bk_bookingcss set id_area=?, header_background=?, header_color=?, header_font_size=?, 
								resa_font_size=?, header_height=?, line_height=?
									  where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($id_area, $header_background, $header_color, $header_font_size,
            $resa_font_size, $header_height, $line_height, $id, $id_space));
    }

    /**
     * Remove an area
     * @param number $id Area ID
     */
    public function delete($id_space, $id) {
        $sql = "UPDATE bk_bookingcss set deleted=1,deleted_at=NOW() WHERE id = ? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
