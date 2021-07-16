<?php

require_once 'Framework/Model.php';

/**
 * Class defining the Tissus model
 *
 * @author Sylvain Prigent
 */
class AcOwner extends Model {

    /**
     * Create the isotype table
     * 
     * @return PDOStatement
     */
    public function createTable() {
        $sql = "CREATE TABLE IF NOT EXISTS `ac_j_user_anticorps` (
                    `id` int(11) NOT NULL,
                    `id_anticorps` int(11) NOT NULL,
                    `id_utilisateur` int(11) NOT NULL,	
                    `disponible` int(2) NOT NULL,		
                    `date_recept` DATE NOT NULL,
                    `no_dossier` varchar(12) NOT NULL,
                    PRIMARY KEY (`id`)
                    );
                    ";

        $this->runRequest($sql);
    }

    public function get($id_space, $id) {
        $sql = "SELECT * FROM ac_j_user_anticorps WHERE id=? AND id_space=? AND deleted=0";
        return $this->runRequest($sql, array($id, $id_space))->fetch();
    }

    public function setOwner($id_space ,$id, $id_antibody, $id_utilisateur, $disponible, $date_recept, $no_dossier) {
        if (!$id) {
            $sql = "INSERT INTO ac_j_user_anticorps (id_anticorps, id_utilisateur, disponible, date_recept, no_dossier, id_space) VALUES (?,?,?,?,?, ?);";
            $this->runRequest($sql, array($id_antibody, $id_utilisateur, $disponible, $date_recept, $no_dossier, $id_space));
        } else {
            $sql = "UPDATE ac_j_user_anticorps SET id_anticorps=?, id_utilisateur=?, disponible=?, date_recept=?, no_dossier=? WHERE id=? AND id_space=?";
            $this->runRequest($sql, array($id_antibody, $id_utilisateur, $disponible, $date_recept, $no_dossier, $id, $id_space));
        }
    }

    public function getInfoForAntibody($id_space, $id_antibody) {
        if ($id_antibody == 0) {
            return array();
        }
        $sql = "SELECT * FROM ac_j_user_anticorps WHERE id_anticorps=? AND id_space=? AND deleted=0";
        $data = $this->runRequest($sql, array($id_antibody, $id_space))->fetchAll();
        $modelUser = new CoreUser();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["user"] = $modelUser->getUserFUllName($data[$i]["id_utilisateur"]);
        }
        return $data;
    }

    public function delete($id_space, $id) {
        $sql = "DELETE FROM ac_j_user_anticorps WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
