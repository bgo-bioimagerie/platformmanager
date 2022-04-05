<?php

require_once 'Framework/Model.php';
require_once 'Modules/core/Model/CoreSpace.php';

/**
 * Class defining the booking settings model.
 * It is used to print a booking summary in the calendar table
 *
 * @author Sylvain Prigent
 */
class BkBookingSettings extends Model {

    public function __construct() {
        $this->tableName = "bk_booking_settings";
    }

    /**
     * Create the booking settings entry table
     *
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `bk_booking_settings` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`tag_name` varchar(100) NOT NULL,
		`is_visible` int NOT NULL,			
		`is_tag_visible` int(1) NOT NULL,
		`display_order` int(5) NOT NULL,
		`font` varchar(20) NOT NULL,
        `id_space` int(11) NOT NULL,
		PRIMARY KEY (`id`)
		);";

        return $this->runRequest($sql);
    }
    
    /**
     * Get the list of all entries
     * @param string $sortEntry
     * @return array List of the entries
     */
    public function entries($id_space, $sortEntry) {

        try {
            $sql = "select * from bk_booking_settings WHERE id_space=? AND deleted=0 order by " . $sortEntry;
            $req = $this->runRequest($sql, array($id_space));
            if($req->rowCount() > 0){
                return $req->fetchAll();
            }
            else{
                return array();
            }
        } 
        catch (Exception $e) {
            return "";
        }
    }

    /**
     * Add en entry
     * @param string $tag_name
     * @param Number $is_visible
     * @param Number $is_tag_visible
     * @param Number $display_order
     * @param string $font
     * @return string
     */
    public function addEntry($tag_name, $is_visible, $is_tag_visible, $display_order, $font, $id_space) {
        $sql = "insert into bk_booking_settings(tag_name, is_visible, is_tag_visible, 
			                                    display_order, font, id_space)"
                . " values(?,?,?,?,?,?)";
        $this->runRequest($sql, array($tag_name, $is_visible, $is_tag_visible,
            $display_order, $font, $id_space));
        return $this->getDatabase()->lastInsertId();
    }

    /**
     * Check if an entry exists
     * @param string $tag_name
     * @return boolean
     */
    public function isEntry1($tag_name, $id_space) {
        $sql = "select id from bk_booking_settings where tag_name=? and id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array(
            $tag_name, $id_space
                ));
        return ($data->rowCount() == 1);
    }

    /**
     * Set en entry (add if not axists, otherwise update)
     * @param string $tag_name
     * @param number $is_visible
     * @param number $is_tag_visible
     * @param number $display_order
     * @param string $font
     */
    public function setEntry($tag_name, $is_visible, $is_tag_visible, $display_order, $font, $id_space) {
        if (!$this->isEntry1($tag_name, $id_space)) {
            $this->addEntry($tag_name, $is_visible, $is_tag_visible, $display_order, $font, $id_space);
        } else {
            $id = $this->getEntryID($tag_name, $id_space);
            $this->updateEntry($id, $tag_name, $is_visible, $is_tag_visible, $display_order, $font, $id_space);
        }
    }

    /**
     * Get the ID of an entry from it name
     * @param string $tag_name
     * @return number
     */
    public function getEntryID($tag_name, $id_space) {
        $sql = "select id from bk_booking_settings where tag_name=? and id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($tag_name, $id_space));
        $tmp = $req->fetch();
        return $tmp? $tmp[0]: null;
    }

    /**
     * Get entry from ID
     * @param number $id
     * @return array entry info
     */
    public function getEntry($id_space, $id) {
        $sql = "select * from bk_booking_settings where id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        return $req->fetch();
    }

    /**
     * Update the informations of an entry
     * @param number $id
     * @param string $tag_name
     * @param number $is_visible
     * @param number $is_tag_visible
     * @param number $display_order
     * @param string $font
     */
    public function updateEntry($id, $tag_name, $is_visible, $is_tag_visible, $display_order, $font, $id_space) {
        $sql = "update bk_booking_settings set tag_name=?, is_visible=?, is_tag_visible=?, 
			                 display_order=?, font=?
			                 where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($tag_name, $is_visible, $is_tag_visible,
            $display_order, $font, $id, $id_space));
    }

    /**
     * Get the summary of a reservation 
     * @param string $user User name
     * @param string $phone User phone
     * @param string $short_desc Reservation short description
     * @param string $desc Reservation log description
     * @param boolean $displayHorizontal
     * @return string Summmary in HTML
     */
    public function getSummary($id_space, $user, $phone, $short_desc, $desc, $displayHorizontal = true, $role=0) {
        $lang = "En";
        if (isset($_SESSION["user_settings"]["language"])) {
            $lang = $_SESSION["user_settings"]["language"];
        }

        $entryList = $this->entries($id_space, "display_order");
        $summary = "";
        // user
        for ($i = 0; $i < count($entryList); $i++) {
            $last = false;
            if ($i == count($entryList) - 1) {
                $last = true;
            }

            if($entryList[$i]['is_visible'] == 2){
                $entryList[$i]['is_visible'] = 0;
                if($role >= CoreSpace::$MANAGER){
                    $entryList[$i]['is_visible'] = 1;
                }
            }

            if ($entryList[$i]['tag_name'] == "User") {
                $summary .= $this->summaryEntry($i, $entryList, $user, $displayHorizontal, BookingTranslator::User($lang), $last);
            } elseif ($entryList[$i]['tag_name'] == "Phone") {
                $summary .= $this->summaryEntry($i, $entryList, $phone, $displayHorizontal, BookingTranslator::Phone($lang), $last);
            } elseif ($entryList[$i]['tag_name'] == "Short desc") {
                $summary .= $this->summaryEntry($i, $entryList, $short_desc, $displayHorizontal, BookingTranslator::Short_desc($lang), $last);
            } elseif ($entryList[$i]['tag_name'] == "Desc") {
                $summary .= $this->summaryEntry($i, $entryList, $desc, $displayHorizontal, BookingTranslator::Desc($lang), $last);
            }
        }

        return $summary;
    }

    /**
     * Generate an HTML display of a reservation summary
     * @param int $i index in entryList
     * @param array $entryList
     * @param string $content
     * @param bool $displayHorizontal
     * @param string $tagNameTr  Name of the tag (user, desc, ..)
     * @param unknown $last
     * @return Ambigous <string, unknown>
     */
    protected function summaryEntry($i, $entryList, $content, $displayHorizontal, $tagNameTr, $last) {
        $summary = "" ;
        if ($entryList[$i]['is_visible'] == 1) {
            if ($entryList[$i]['is_tag_visible'] == 1) {
                $summary .= "<strong>" . $tagNameTr . ": </strong>";
            }
            if ($entryList[$i]['font'] == "bold") {
                $summary .= "<strong>";
            } elseif ($entryList[$i]['font'] == "italic") {
                $summary .= "<em>";
            }
            $summary .= $content;
            if ($entryList[$i]['font'] == "bold") {
                $summary .= "</strong>";
            } elseif ($entryList[$i]['font'] == "italic") {
                $summary .= "</em>";
            }
            if ($summary && $last == false) {
                if ($displayHorizontal) {
                    $summary .= " ";
                } else {
                    $summary .= "<br/>";
                }
            }
        }
        return $summary;
    }

}
