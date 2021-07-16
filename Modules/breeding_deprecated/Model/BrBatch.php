<?php

require_once 'Framework/Model.php';

class BrBatch extends Model {

    public function __construct() {
        $this->tableName = "br_batchs";
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("reference", "varchar(255)", "");
        $this->setColumnsInfo("created", "date", "");
        $this->setColumnsInfo("id_male_spawner", "int(11)", ""); // ref to a batch
        $this->setColumnsInfo("id_female_spawner", "int(11)", ""); // ref to a batch
        $this->setColumnsInfo("id_destination", "int(11)", ""); // 1 sale, 2 Lab
        $this->setColumnsInfo("id_product", "int(11)", "");
        $this->setColumnsInfo("quantity", "int(11)", 0);
        $this->setColumnsInfo("quantity_start", "int(11)", 0);
        $this->setColumnsInfo("quantity_losse", "int(11)", 0);
        $this->setColumnsInfo("quantity_sale", "int(11)", 0);
        $this->setColumnsInfo("sexing_female_num", "int(11)", 0);
        $this->setColumnsInfo("sexing_male_num", "int(11)", 0);
        $this->setColumnsInfo("chipped", "int(1)", ""); // 0 vs 1
        $this->setColumnsInfo("comment", "text", "");
        $this->setColumnsInfo("sexing_date", "date", "0000-00-00");
        $this->setColumnsInfo("sexing_f_batch_id", "int(11)", 0);
        $this->setColumnsInfo("sexing_m_batch_id", "int(11)", 0);

        $this->primaryKey = "id";
    }
    
    public function getProductID($id_batch){
        $sql = "SELECT id_product FROM br_batchs WHERE id=?";
        $tmp = $this->runRequest($sql, array($id_batch))->fetch();
        return $tmp[0];
    }    
    
    public function findByName($name){
        $sql = "SELECT id FROM br_batchs WHERE reference=?";
        $req = $this->runRequest($sql, array($name));
        if ( $req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }
    
    public function getQantityStart($id){
        $sql = "SELECT quantity_start FROM br_batchs WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ( $req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }
    
    public function setSexage($id_batch, $num_f, $num_m, $id_m_batch, $id_f_batch){
        $sql = "UPDATE br_batchs SET sexing_male_num=?, sexing_female_num=?, "
                . "sexing_f_batch_id=?, sexing_m_batch_id=? WHERE id=?";
        $this->runRequest($sql, array($num_m, $num_f, $id_f_batch, $id_m_batch, $id_batch));
    }

    public function updateQuantity($id) {

        // losse
        $sql = "SELECT * FROM br_losses WHERE id_batch=?";
        $losses = $this->runRequest($sql, array($id))->fetchAll();
        $quantity_losse = 0;
        foreach ($losses as $l) {
            $quantity_losse += $l["quantity"];
        }

        // sales
        $sql2 = "SELECT * FROM br_sale_items WHERE id_batch=?";
        $salesitems = $this->runRequest($sql2, array($id))->fetchAll();
        $quantity_sold = 0;
        foreach ($salesitems as $s) {
            $quantity_sold += $s["quantity"];
        }

        // start
        $sql3 = "SELECT quantity_start, sexing_female_num, sexing_male_num FROM br_batchs WHERE id=?";
        $quantity_startarray = $this->runRequest($sql3, array($id))->fetch();
        $quantity_start = $quantity_startarray[0];
        $quantity_female = $quantity_startarray[1];
        $quantity_male = $quantity_startarray[2];
        
        // update quantity
        $quantity = $quantity_start - $quantity_losse - $quantity_sold - $quantity_female - $quantity_male;
        $sql4 = "UPDATE br_batchs SET quantity=?, quantity_sale=?, quantity_losse=? WHERE id=?";
        $this->runRequest($sql4, array($quantity, $quantity_sold, $quantity_losse, $id));
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM br_batchs WHERE id_space=?";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getInProgress($id_space) {
        $sql = "SELECT * FROM br_batchs WHERE id_space=? AND quantity>0 ORDER BY created DESC;";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function getArchives($id_space) {
        $sql = "SELECT * FROM br_batchs WHERE id_space=? AND quantity=0";
        return $this->runRequest($sql, array($id_space))->fetchAll();
    }

    public function get($id) {
        $sql = "SELECT * FROM br_batchs WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function getName($id) {
        $sql = "SELECT reference FROM br_batchs WHERE id=?";
        $data = $this->runRequest($sql, array($id))->fetch();
        return $data[0];
    }
    
    
    public function getIdFromName($name) {
        $sql = "SELECT id FROM br_batchs WHERE reference=?";
        $data = $this->runRequest($sql, array($name))->fetch();
        return $data[0];
    }

    public function set($id, $id_space, $reference, $created, $id_male_spawner, $id_female_spawner, $id_destination, $id_product, $chipped, $comment) {
        if (!$id) {
            $sql = 'INSERT INTO br_batchs (id_space, reference, created, id_male_spawner, id_female_spawner, 
            id_destination, id_product,  chipped, comment) VALUES (?,?,?,?,?,?,?,?,?)';
            $this->runRequest($sql, array($id_space, $reference, $created, $id_male_spawner, $id_female_spawner,
                $id_destination, $id_product, $chipped, $comment));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE br_batchs SET id_space=?, reference=?, created=?, id_male_spawner=?, id_female_spawner=?, 
            id_destination=?, id_product=?, chipped=?, comment=? WHERE id=?';
            $this->runRequest($sql, array($id_space, $reference, $created, $id_male_spawner, $id_female_spawner,
                $id_destination, $id_product, $chipped, $comment, $id));
            return $id;
        }
    }

    public function setQuantity($id, $quantity) {
        $sql = "UPDATE br_batchs SET quantity=? WHERE id=?";
        $this->runRequest($sql, array($quantity, $id));
    }

    public function setQuantityStart($id, $quantity) {
        $sql = "UPDATE br_batchs SET quantity_start=? WHERE id=?";
        $this->runRequest($sql, array($quantity, $id));
    }

    public function getForList($id_space) {
        $sql = "SELECT * FROM br_batchs WHERE id_space=?";
        $data = $this->runRequest($sql, array($id_space))->fetchAll();
        $names = array();
        $ids = array();
        $names[] = "";
        $ids[] = 0;
        foreach ($data as $d) {
            $names[] = $d["reference"] . " (" . $d["quantity"] . ")";
            $ids[] = $d["id"];
        }
        return array("names" => $names, "ids" => $ids);
    }
    
    public function sexing($id_space, $id_batch, $num_females, $num_males){
        
        // create new batchs
        $sql = "SELECT * FROM br_batchs WHERE id=?";
        $batchInfo = $this->runRequest($sql, array($id_batch))->fetch();
        
        // female batch
        $id_female_batch = $this->set(0, $id_space, 
                $batchInfo["reference"] . 'f', 
                $batchInfo["created"], 
                $batchInfo["id_male_spawner"], 
                $batchInfo["id_female_spawner"], 
                $batchInfo["id_destination"], 
                $batchInfo["id_product"], 
                $batchInfo["chipped"], 
                ""
            );
        $this->setQuantityStart($id_female_batch, $num_females);
        $this->updateQuantity($id_female_batch);
        
        // male batch
        $id_male_batch = $this->set(0, $id_space, 
                $batchInfo["reference"] . 'm', 
                $batchInfo["created"], 
                $batchInfo["id_male_spawner"], 
                $batchInfo["id_female_spawner"], 
                $batchInfo["id_destination"], 
                $batchInfo["id_product"], 
                $batchInfo["chipped"], 
                ""
            );
        $this->setQuantityStart($id_male_batch, $num_males);
        $this->updateQuantity($id_male_batch);
        
        
        // decrease the batch count
        $sql1 = "UPDATE br_batchs SET sexing_female_num=?, sexing_male_num=?, "
                . "sexing_date=?, sexing_f_batch_id=?, sexing_m_batch_id=? WHERE id=?";
        $this->runRequest($sql1, array($num_females, $num_males, 
            date("Y-m-d", time()), 
            $id_female_batch, $id_male_batch, $id_batch));
        $this->updateQuantity($id_batch);
        
    }
    

    public function delete($id) {
        $sql = "DELETE FROM br_batchs WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
