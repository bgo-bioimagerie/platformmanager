<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Espece model
 *
 * @author Sylvain Prigent
 */
class Kit extends Model
{
    public function __construct()
    {
        $this->tableName = "ac_kits";
    }

    /**
     * Create the espece table
     *
     * @return PDOStatement
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `ac_kits` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`nom` varchar(30) NOT NULL,
                `id_space` int(11) NOT NULL,
 				PRIMARY KEY (`id`)
				);";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    public function getBySpace($idSpace)
    {
        $sql = "select * from ac_kits WHERE id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($idSpace));
        return $user->fetchAll();
    }

    /**
     * get especes informations
     *
     * @param string $sortentry Entry that is used to sort the especes
     * @return multitype: array
     */
    public function getKits($idSpace, $sortentry = 'id')
    {
        $sql = "select * from ac_kits WHERE id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($idSpace));
        return $user->fetchAll();
    }

    /**
     * get the informations of an espece
     *
     * @param int $id Id of the espece to query
     * @throws Exception id the espece is not found
     * @return mixed array
     */
    public function get($idSpace, $id)
    {
        if (!$id) {
            return array("nom" => "");
        }

        $sql = "select * from ac_kits where id=? AND id_space=?";
        $unit = $this->runRequest($sql, array($id, $idSpace));
        if ($unit->rowCount() == 1) {
            return $unit->fetch();
        } else {
            throw new PfmParamException("Cannot find the kit using the given id", 404);
        }
    }

    /**
     * add an espece to the table
     *
     * @param string $name name of the espece
     *
     */
    public function add($name, $idSpace)
    {
        $sql = "insert into ac_kits(nom, id_space)"
                . " values(?,?)";
        $this->runRequest($sql, array($name, $idSpace));
        return $this->getDatabase()->lastInsertId();
    }

    /**
     * update the information of a
     *
     * @param int $id Id of the  to update
     * @param string $name New name of the
     */
    public function edit($id, $name, $idSpace)
    {
        $sql = "update ac_kits set nom=? where id=? AND id_space=?";
        $this->runRequest($sql, array("" . $name . "", $id, $idSpace));
    }

    public function getIdFromName($name, $idSpace)
    {
        $sql = "select id from ac_kits where nom=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($name, $idSpace));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    public function getNameFromId($idSpace, $id)
    {
        $sql = "select nom from ac_kits where id=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id, $idSpace));
        if ($unit->rowCount() == 1) {
            $tmp = $unit->fetch();
            return $tmp[0];
        } else {
            return "";
        }
    }

    public function delete($idSpace, $id)
    {
        $sql = "UPDATE ac_kits SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
