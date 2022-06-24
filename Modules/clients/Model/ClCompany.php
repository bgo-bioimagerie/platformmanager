<?php

require_once 'Framework/Model.php';

class ClCompany extends Model {

    public function __construct() {
        $this->tableName = "cl_company";

        /*
        $this->setColumnsInfo("id", "int(11)", "");
        $this->setColumnsInfo("id_space", "int(11)", 0);
        $this->setColumnsInfo("name", "varchar(255)", 0);
        $this->setColumnsInfo("address", "text", ""); 
        $this->setColumnsInfo("zipcode", "varchar(255)", 0);
        $this->setColumnsInfo("city", "varchar(255)", 0);
        $this->setColumnsInfo("county", "varchar(255)", 0);
        $this->setColumnsInfo("country", "varchar(255)", 0);
        $this->setColumnsInfo("tel", "varchar(255)", 0);
        $this->setColumnsInfo("fax", "varchar(255)", 0);
        $this->setColumnsInfo("email", "varchar(255)", 0);
        $this->setColumnsInfo("approval_number", "varchar(255)", 0);
        
        $this->primaryKey = "id";
        */
    }

    public function createTable()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `cl_company` (
            `id` int NOT NULL AUTO_INCREMENT,
            `id_space` int NOT NULL DEFAULT 0,
            `name` varchar(255) NOT NULL DEFAULT "",
            `address` text,
            `zipcode` varchar(255) NOT NULL DEFAULT "",
            `city` varchar(255) NOT NULL DEFAULT "",
            `county` varchar(255) NOT NULL DEFAULT "",
            `country` varchar(255) NOT NULL DEFAULT "",
            `tel` varchar(255) NOT NULL DEFAULT "",
            `fax` varchar(255) NOT NULL DEFAULT "",
            `email` varchar(255) NOT NULL DEFAULT "",
            `approval_number` varchar(255) NOT NULL DEFAULT "",
            PRIMARY KEY (`id`)
        )';
        $this->runRequest($sql);
        $this->baseSchema();
    }

    public function default($id_space) {
        return [
            "id" => 0,
            "id_space" => $id_space,
            "name" => "",
            "address" => "",
            "zipcode" => "",
            "city" => "",
            "county" => "",
            "country" => "",
            "tel" => "",
            "fax" => "",
            "email" => "",
            "approval_number" => ""
        ];
    }

    /**
     * Get the clCompany for this space
     * @param string|int $id_space 
     * 
     * @return array
     */
    public function getForSpace($id_space) {
        $sql = "SELECT * FROM cl_company WHERE id_space=? AND deleted=0";
        $clCompany = $this->runRequest($sql, array($id_space))->fetch();
        if ($clCompany === false) {
            return [];
        }
        return $clCompany;
    }

    public function get($id_space, $id) {
        $sql = "SELECT * FROM cl_company WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }
    
    public function getName($id_space, $id){
        $sql = "SELECT reference FROM cl_company WHERE id=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id, $id_space))->fetch();
        return $data[0];
    }

    public function set($id_space, $name, $address, $zipcode, $city, 
            $county, $country, $tel, $fax, $email, $approval_number) {
        
        $id = $this->exists($id_space);
        if ( !$id ) {
            $sql = 'INSERT INTO cl_company (id_space, name, address, zipcode, city, 
            county, country, tel, fax, email, approval_number) VALUES (?,?,?,?,?,?,?,?,?,?,?)';
            $this->runRequest($sql, array( $id_space, $name, $address, $zipcode, $city, 
            $county, $country, $tel, $fax, $email, $approval_number ));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = 'UPDATE cl_company SET name=?, address=?, zipcode=?, 
                city=?, county=?, country=?, tel=?, fax=?, email=?, approval_number=? WHERE id=? AND id_space=? AND deleted=0';
            $this->runRequest($sql, array($name, $address, $zipcode, $city, 
            $county, $country, $tel, $fax, $email, $approval_number, $id, $id_space));
            return $id;
        }
    }
    
    protected function exists($id_space){
        $sql = "SELECT id FROM cl_company WHERE id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id_space));
        if($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE cl_company SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
