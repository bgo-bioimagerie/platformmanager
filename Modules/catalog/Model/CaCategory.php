<?php

require_once 'Framework/Model.php';

/**
 * Class defining the catalog category table model. 
 *
 * @author Sylvain Prigent
 */
class CaCategory extends Model {

    public function __construct() {
        $this->tableName = "ca_categories";
    }

    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `ca_categories` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(50) NOT NULL,	
        `id_space` int(11) NOT NULL DEFAULT 0,
        `display_order` int(4) NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`)
		);";

        $this->runRequest($sql);

        $this->addColumn("ca_categories", "display_order", "int(4)", 0);
        $this->addColumn("ca_categories", "id_space", "int(11)", 0);
        
    }

    public function add($id_space, $name, $displayOrder = 0) {
        $sql = "INSERT INTO ca_categories(id_space, name, display_order) VALUES(?, ?,?)";
        $this->runRequest($sql, array($id_space, $name, $displayOrder));
    }

    public function edit($id, $id_space, $name, $displayOrder = 0) {
        $sql = "update ca_categories set name=?, display_order=? where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($name, $displayOrder, $id, $id_space));
    }

    public function getAll($id_space) {
        $sql = "SELECT * FROM ca_categories WHERE id_space=? AND deleted=0 ORDER BY display_order ASC;";
        $req = $this->runRequest($sql, array($id_space));
        return $req->fetchAll();
    }

    public function getName($id_space, $id) {
        $sql = "SELECT name FROM ca_categories WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        $inter = $req->fetch();
        return $inter[0];
    }

    public function getDisplayOrder($id_space, $id) {
        $sql = "SELECT display_order FROM ca_categories WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        $inter = $req->fetch();
        return $inter[0];
    }

    /**
     * Delete a category
     * @param number $id Category ID
     */
    public function delete($id_space, $id) {
        $sql = "DELETE FROM ca_categories WHERE id =? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
