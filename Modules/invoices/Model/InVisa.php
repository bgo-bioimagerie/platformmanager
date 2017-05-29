<?php

require_once 'Framework/Model.php';

require_once 'Modules/ecosystem/Model/EcUser.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class InVisa extends Model {

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `in_visa` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
                `id_user` int(11) NOT NULL,
                `id_space` int(11) NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);
    }

    /**
     * 
     * @param type $id
     * @param type $name
     */
    public function set($id, $id_user, $id_space) {
        if ($id == 0) {
            $sql = "INSERT INTO in_visa (id_user, id_space) VALUES (?,?)";
            $this->runRequest($sql, array($id_user, $id_space));
        } else {
            $sql = "UPDATE in_visa SET id_user=?, id_space=? WHERE id=?";
            $this->runRequest($sql, array($id_user, $id_space, $id));
        }
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM in_visa WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        
        $modelUser = new CoreUser();
        for($i = 0 ; $i < count($data) ; $i++){
            $data[$i]["user_name"] = $modelUser->getUserFUllName($data[$i]['id_user']);
        }
        return $data;
    }

    public function get($id) {
        $sql = "SELECT * FROM in_visa WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }
    
    public function getForList($id_space){
        $sql = "SELECT * FROM in_visa WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        
        $ids = array();
        $names = array();
        $ids[] = 0;
        $names[] = "";
        $modelUser = new CoreUser();
        foreach($data as $dat){
            $ids[] = $dat['id'];
            $names[] = $modelUser->getUserFUllName($dat['id_user']);
        }
        return array('ids' => $ids, 'names' => $names);
    }
    
    public function getVisaName($id){
        $sql = "SELECT * FROM in_visa WHERE id=?";
        $data = $this->runRequest($sql, array($id))->fetch();
        
        $modelUser = new EcUser();
        return $modelUser->getUserFUllName($data["id_user"]);
    }

    /**
     * Delete a unit
     * @param number $id Unit ID
     */
    public function delete($id) {

        $sql = "DELETE FROM in_visa WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
