<?php

require_once 'Framework/Model.php';

/**
 * Class defining the database listing
 *
 * @author Sylvain Prigent
 */
class DbQuery extends Model {

    /**
     * Create the site table
     * 
     * @return PDOStatement
     */
    public function __construct() {
        
    }
    
    public function tableView($name){
        $sql = "SELECT * FROM dbc_" . $name;
        $req = $this->runRequest($sql);
        return $req->fetchAll();
    }
    
}
