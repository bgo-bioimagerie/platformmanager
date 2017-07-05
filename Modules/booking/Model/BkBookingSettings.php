<?php

require_once 'Framework/Model.php';

/**
 * Class defining the booking settings model.
 * It is used to print a booking summary in the calendar table
 *
 * @author Sylvain Prigent
 */
class BkBookingSettings extends Model {

    /**
     * Create the booking settings entry table
     *
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `bk_booking_settings` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
		`tag_name` varchar(100) NOT NULL,
		`is_visible` int(1) NOT NULL,			
		`is_tag_visible` int(1) NOT NULL,
		`display_order` int(5) NOT NULL,
		`font` varchar(20) NOT NULL,
                `id_space` int(11) NOT NULL,
		PRIMARY KEY (`id`)
		);";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    /**
     * Set the default entries
     
    public function defaultEntries() {
        $this->setEntry("User", 1, 1, 1, "normal");
        $this->setEntry("Phone", 1, 1, 2, "normal");
        $this->setEntry("Short desc", 1, 1, 3, "normal");
        $this->setEntry("Desc", 0, 0, 4, "normal");
    }
    */
    
    /**
     * Get the list of all entries
     * @param string $sortEntry
     * @return array List of the entries
     */
    public function entries($id_space, $sortEntry) {

        try {
            $sql = "select * from bk_booking_settings WHERE id_space=? order by " . $sortEntry;
            $req = $this->runRequest($sql, array($id_space));
            if($req->rowCount() > 0){
                return $req->fetchAll();
            }
            else{
                
                echo "---------------------------- <br>";
                echo " reininitlize the booking settings <br/>"; 
                echo "---------------------------- <br>";
                
                $this->setEntry("User", 1, 1, 1, "normal", $id_space);
                $this->setEntry("Phone", 1, 1, 2, "normal", $id_space);
                $this->setEntry("Short desc", 1, 1, 3, "normal", $id_space);
                $this->setEntry("Desc", 0, 0, 4, "normal", $id_space);
                
                $sql = "select * from bk_booking_settings WHERE id_space=? order by " . $sortEntry;
                $req = $this->runRequest($sql, array($id_space));
                return $req->fetchAll();
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
    public function isEntry1($tag_name) {
        $sql = "select id from bk_booking_settings where tag_name=?";
        $data = $this->runRequest($sql, array(
            $tag_name
                ));
        if ($data->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
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
        if (!$this->isEntry1($tag_name)) {
            $this->addEntry($tag_name, $is_visible, $is_tag_visible, $display_order, $font, $id_space);
        } else {
            $id = $this->getEntryID($tag_name);
            $this->updateEntry($id, $tag_name, $is_visible, $is_tag_visible, $display_order, $font, $id_space);
        }
    }

    /**
     * Get the ID of an entry from it name
     * @param string $tag_name
     * @return number
     */
    public function getEntryID($tag_name) {
        $sql = "select id from bk_booking_settings where tag_name=?";
        $req = $this->runRequest($sql, array($tag_name));
        $tmp = $req->fetch();
        return $tmp[0];
    }

    /**
     * Get entry from ID
     * @param number $id
     * @return array entry info
     */
    public function getEntry($id) {
        $sql = "select * from bk_booking_settings where id=?";
        $req = $this->runRequest($sql, array($id));
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
			                 display_order=?, font=?, id_space=?
			                 where id=?";
        $this->runRequest($sql, array($tag_name, $is_visible, $is_tag_visible,
            $display_order, $font, $id_space, $id));
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
    public function getSummary($id_space, $user, $phone, $short_desc, $desc, $displayHorizontal = true) {

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
            if ($entryList[$i]['tag_name'] == "User") {
                $summary = $this->summaryEntry($i, $summary, $entryList, $user, $displayHorizontal, BookingTranslator::User($lang), $last);
            } elseif ($entryList[$i]['tag_name'] == "Phone") {
                $summary = $this->summaryEntry($i, $summary, $entryList, $phone, $displayHorizontal, BookingTranslator::Phone($lang), $last);
            } elseif ($entryList[$i]['tag_name'] == "Short desc") {
                $summary = $this->summaryEntry($i, $summary, $entryList, $short_desc, $displayHorizontal, BookingTranslator::Short_desc($lang), $last);
            } elseif ($entryList[$i]['tag_name'] == "Desc") {
                $summary = $this->summaryEntry($i, $summary, $entryList, $desc, $displayHorizontal, BookingTranslator::Desc($lang), $last);
            }
        }

        return $summary;
    }

    /**
     * Generate an HTML display of a reservation summary
     * @param unknown $i
     * @param unknown $summary
     * @param unknown $entryList
     * @param unknown $content
     * @param unknown $displayHorizontal
     * @param unknown $tagNameTr
     * @param unknown $last
     * @return Ambigous <string, unknown>
     */
    protected function summaryEntry($i, $summary, $entryList, $content, $displayHorizontal, $tagNameTr, $last) {

        if ($entryList[$i]['is_visible'] == 1) {
            if ($entryList[$i]['is_tag_visible'] == 1) {
                $summary .= "<b>" . $tagNameTr . ": </b>";
            }
            if ($entryList[$i]['font'] == "bold") {
                $summary .= "<b>";
            } elseif ($entryList[$i]['font'] == "italic") {
                $summary .= "<i>";
            }
            $summary .= $content;
            if ($entryList[$i]['font'] == "bold") {
                $summary .= "</b>";
            } elseif ($entryList[$i]['font'] == "italic") {
                $summary .= "</i>";
            }
            if ($last == false) {
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
