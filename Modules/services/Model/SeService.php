<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Consomable items model
 *
 * @author Sylvain Prigent
 */
class SeService extends Model {

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `se_services` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
                `id_space` int(11) NOT NULL DEFAULT 0,
                `name` varchar(100) NOT NULL,
		`description` varchar(250) NOT NULL,
		`display_order` int(11) NOT NULL DEFAULT 0,		
		`is_active` int(1) NOT NULL DEFAULT 1,	 
		`type_id` int(11) NOT NULL DEFAULT 1,
                `quantity` varchar(128) NOT NULL DEFAULT '0',
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);
    }

    public function getIdFromName($name, $id_sapce){
        $sql = "SELECT id FROM se_services WHERE name=? AND id_space=?";
        $data = $this->runRequest($sql, array($name, $id_sapce));
        if($data->rowCount() > 0){
            $tmp = $data->fetch();
            return $tmp[0];
        }
        return 0;
    }
    
    public function getItemType($id){
        $sql = "SELECT type_id FROM se_services WHERE id=?";
        $data = $this->runRequest($sql, array($id))->fetch();
        return $data[0];
    }
    
    public function setQuantity($id, $quantity){
        $sql = "UPDATE se_services SET quantity=? WHERE id=?";
        $this->runRequest($sql, array($quantity, $id));
    }
    
    public function editquantity($id, $quantity, $operation = "add"){
        $sql = "SELECT quantity FROM se_services WHERE id=?";
        $q = $this->runRequest($sql, array($id))->fetch();
        
        if ($operation == "add"){
            $this->setQuantity($id, $quantity + $q[0]);
        }
        else{
            $this->setQuantity($id, $q[0] - $quantity);
        }
    }
    
    /**
     * add an item to the table
     *
     * @param string $name name of the unit
     */
    public function addItem($name, $description, $display_order, $type_id = 1) {

        $sql = "insert into se_services(name, description, display_order, type_id)"
                . " values(?, ?, ?, ?)";
        $this->runRequest($sql, array($name, $description, $display_order, $type_id));
        return $this->getDatabase()->lastInsertId();
    }
    
    public function setService($id, $id_space, $name, $description, $display_order, $type_id){
        if($this->isService($id)){
            $sql = "UPDATE se_services SET name=?, id_space=?, description=?, display_order=?, type_id=? WHERE id=?";
            $this->runRequest($sql, array($name, $id_space, $description, $display_order, $type_id, $id));
            return $id;
        }
        else{
            $sql = "INSERT INTO se_services (name, id_space, description, display_order, type_id) VALUES (?,?,?,?,?)";
            $this->runRequest($sql, array($name, $id_space, $description, $display_order, $type_id));
            return $this->getDatabase()->lastInsertId();
        }
    }
    
    public function isService($id){
        $sql = "SELECT id FROM se_services WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1){
            return true;
        }
        return false;
    }

    public function setActive($id, $active) {
        $sql = "update se_services set is_active=? where id=?";
        $this->runRequest($sql, array($active, $id));
    }

    /**
     * get items informations
     *
     * @param string $sortentry Entry that is used to sort the units
     * @return multitype: array
     */
    public function getItems($sortentry = 'id') {

        $sql = "select * from se_services order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql);
        return $user->fetchAll();
    }
    
    public function getBySpace($id_space){
        $sql = "SELECT * FROM se_services WHERE id_space=?";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }
    
    public function getAll($id_space){
        $sql = "SELECT se_services.*, se_service_types.local_name as type "
                . "FROM se_services "
                . "INNER JOIN se_service_types ON se_services.type_id = se_service_types.id "
                . "WHERE se_services.id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }
    
    public function getForList($id_space){
        $sql = "select * from se_services WHERE id_space=? ORDER BY name ASC;";
        $req = $this->runRequest($sql, array($id_space))->fetchAll();
        $ids = array(); $names = array();
        foreach($req as $r){
            $ids[] = $r["id"];
            $names[] = $r["name"];
        }
        return array("names" => $names, "ids" => $ids);
    }

    /**
     * get items informations
     *
     * @param string $sortentry Entry that is used to sort the units
     * @return multitype: array
     */
    public function getActiveItems($sortentry = 'id') {

        $sql = "select * from se_services where is_active=1 order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql);
        return $user->fetchAll();
    }

    /**
     * get the informations of an item
     *
     * @param int $id Id of the item to query
     * @throws Exception id the item is not found
     * @return mixed array
     */
    public function getItem($id) {
        $sql = "select * from se_services where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1){
            return $unit->fetch();  // get the first line of the result
        }
        else{
            throw new Exception("Cannot find the item using the given id = " . $id);
        }
    }

    public function getItemName($id) {
        $sql = "select name from se_services where id=?";
        $unit = $this->runRequest($sql, array($id));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            return "unknown";
            //	throw new Exception("Cannot find the item using the given id = " . $id);
        }
    }

    /**
     * update the information of an item
     *
     * @param int $id Id of the item to update
     * @param string $name New name of the item
     */
    public function editItem($id, $name, $description, $display_order, $type_id) {

        $sql = "update se_services set name=?, description=?, display_order=?, type_id=? where id=?";
        $this->runRequest($sql, array("" . $name . "", $description, $display_order, $type_id, $id));
    }

    /**
     * Remove an item from the database
     * @param number $id item ID
     */
    public function delete($id) {
        $sql = "DELETE FROM se_services WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
