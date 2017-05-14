<?php

require_once 'Framework/Model.php';

require_once 'Modules/ecosystem/Model/EcUser.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class SeResaAssay extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        $this->tableName = "se_resaassay";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_resa", "int(11)", 0);
        $this->setColumnsInfo("id_assay", "int(11)", 0);
        $this->setColumnsInfo("dataurl", "varchar(255)", "");
        $this->primaryKey = "id";
    }

    /**
     * 
     * @param type $id
     * @param type $name
     */
    public function set($id_resa, $id_assay, $id_user) {
        $dataurl = $this->getFolderUrl($id_user, $id_assay);
        
        if (!$this->isResaAssay($id_resa)) {
            $sql = "INSERT INTO se_resaassay (id_resa, id_assay, dataurl) VALUES (?,?,?)";
            $this->runRequest($sql, array($id_resa, $id_assay, $dataurl));
        } else {
            $sql = "UPDATE se_resaassay SET id_assay=?, dataurl=? WHERE id_resa=?";
            $this->runRequest($sql, array($id_assay, $dataurl, $id_resa));
        }
    }
    
    public function isResaAssay($id_resa){
        $sql = "SELECT * FROM se_resaassay WHERE id_resa=?";
        $req = $this->runRequest($sql, array($id_resa));
        if($req->rowCount() > 0){
            return true;
        }
        return false;
    }

    private function getFolderUrl($id_user, $id_assay){
        $modelUser = new CoreUser();
        $userInfo = $modelUser->getUser($id_user);
        return "data/".$userInfo['login']."/assay_".$id_assay;
    }
    

    public function getResaAssay($id_resa) {
        $sql = "SELECT * FROM se_resaassay WHERE id_resa=?";
        return $this->runRequest($sql, array($id_resa))->fetch();
    }

    /**
     * Delete a unit
     * @param number $id Unit ID
     */
    public function delete($id) {

        $sql = "DELETE FROM se_resaassay WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
