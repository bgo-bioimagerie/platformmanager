<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class StockBatchShelf extends Model {

    public function __construct() {
        $this->tableName = "stock_j_batch_shelf";
        $this->setColumnsInfo("id", "int(11)", 0);
        $this->setColumnsInfo("id_batch", "int(11)", 0);
        $this->setColumnsInfo("id_shelf", "int(11)", 0);
        $this->setColumnsInfo("date", "date", "0000-00-00");
        $this->setColumnsInfo("id_cabinet", "int(11)", 0);
        $this->primaryKey = "id";
    }

    public function getForBatch($id_batch){
        $sql = "SELECT * FROM stock_j_batch_shelf WHERE id_batch=?";
        return $this->runRequest($sql, array($id_batch))->fetchAll();
    }
    
    
    public function getOne($id){
        $sql = "SELECT * FROM stock_j_batch_shelf WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function set($id, $id_batch, $id_shelf, $date, $id_cabinet){
        
        if ($id > 0){
            $sql = "UPDATE stock_j_batch_shelf SET id_batch=?, id_shelf=?, date=?, id_cabinet=? WHERE id=?";
            $this->runRequest($sql, array(
                $id_batch, $id_shelf, $date, $id_cabinet, $id
            ));
            return $id;
        }
        else{
            $sql = "INSERT INTO stock_j_batch_shelf (id_batch, id_shelf, date, id_cabinet) VALUES (?,?,?,?)";
            $this->runRequest($sql, array($id_batch, $id_shelf, $date, $id_cabinet));
            return $this->getDatabase()->lastInsertId();
        }
    }
    
    public function delete($id) {

        $sql = "DELETE FROM stock_j_batch_shelf WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
