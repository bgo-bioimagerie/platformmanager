<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Unit model for consomable module
 *
 * @author Sylvain Prigent
 */
class SeOrigin extends Model
{
    public function __construct()
    {
        $this->tableName = "se_origin";
    }

    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `se_origin` (
		`id` int(11) NOT NULL AUTO_INCREMENT,
                `name` varchar(255) NOT NULL DEFAULT '',
                `display_order` int(11) NOT NULL DEFAULT 0,
                `id_space` int(11) NOT NULL DEFAULT 0,
		PRIMARY KEY (`id`)
		);";
        $this->runRequest($sql);
        $this->addColumn('se_origin', 'display_order', 'int(11)', 0);
    }

    public function getName($idSpace, $id)
    {
        $sql = "SELECT name FROM se_origin WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $idSpace));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return "";
    }

    public function getIdFromName($name, $idSpace)
    {
        $sql = "SELECT id FROM se_origin WHERE name=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($name, $idSpace));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }

    /**
     *
     * @param type $id
     * @param type $name
     */
    public function set($id, $name, $display_order, $idSpace)
    {
        if (!$id) {
            $sql = "INSERT INTO se_origin (name, display_order, id_space) VALUES (?,?,?)";
            $this->runRequest($sql, array($name, $display_order, $idSpace));
            $id = $this->getDatabase()->lastInsertId();
        } else {
            $sql = "UPDATE se_origin SET name=?, display_order=? WHERE id=? AND id_space=? AND deleted=0";
            $this->runRequest($sql, array($name, $display_order, $id, $idSpace));
        }
        return $id;
    }

    public function getAll($idSpace)
    {
        $sql = "SELECT * FROM se_origin WHERE id_space=? AND deleted=0";
        return $this->runRequest($sql, array($idSpace))->fetchAll();
    }

    public function get($idSpace, $id)
    {
        $sql = "SELECT * FROM se_origin WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $idSpace))->fetch();
    }

    public function getForList($idSpace)
    {
        $sql = "SELECT * FROM se_origin WHERE id_space=? AND deleted=0 ORDER BY display_order";
        $data = $this->runRequest($sql, array($idSpace))->fetchAll();
        $ids = array();
        $names = array();
        $ids[] = "";
        $names[] = "";
        foreach ($data as $dat) {
            $ids[] = $dat['id'];
            $names[] = $dat['name'];
        }
        return array('ids' => $ids, 'names' => $names);
    }

    /**
     * Delete a unit
     * @param number $id Unit ID
     */
    public function delete($idSpace, $id)
    {
        $sql = "UPDATE se_origin SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
