<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Espece model
 *
 * @author Sylvain Prigent
 */
class AcOption extends Model
{
    public function __construct()
    {
        $this->tableName = "ac_options";
    }

    /**
     * Create the espece table
     *
     * @return PDOStatement
     */
    public function createTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `ac_options` (
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
        $sql = "SELECT * from ac_options where id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($idSpace));
        return $user->fetchAll();
    }

    /**
     * get especes informations
     *
     * @param string $sortentry Entry that is used to sort the especes
     * @return multitype: array
     */
    public function getOptions($idSpace, $sortentry = 'id')
    {
        $sql = "SELECT * from ac_options WHERE id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
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

        $sql = "SELECT * from ac_options where id=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id, $idSpace));
        if ($unit->rowCount() == 1) {
            return $unit->fetch();
        } else {
            throw new PfmParamException("Cannot find the option using the given id", 404);
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
        $sql = "insert into ac_options(nom, id_space)"
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
        $sql = "UPDATE ac_options set nom=? where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array("" . $name . "", $id, $idSpace));
    }

    public function getIdFromName($name, $idSpace)
    {
        $sql = "SELECT id from ac_options where nom=? AND id_space=? AND deleted=0";
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
        $sql = "SELECT nom from ac_options where id=? AND id_space=? AND deleted=0";
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
        $sql = "UPDATE ac_options SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $idSpace));
    }
}
