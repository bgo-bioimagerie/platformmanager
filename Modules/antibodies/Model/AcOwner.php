<?php

require_once 'Framework/Model.php';
require_once 'Modules/ecosystem/Model/EcUser.php';

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

    public function get($id) {
        $sql = "SELECT * FROM ac_j_user_anticorps WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function setOwner($id, $id_antibody, $id_utilisateur, $disponible, $date_recept, $no_dossier) {
        if ($id == 0) {
            $sql = "INSERT INTO ac_j_user_anticorps (id_anticorps, id_utilisateur, disponible, date_recept, no_dossier) VALUES (?,?,?,?,?);";
            $this->runRequest($sql, array($id_antibody, $id_utilisateur, $disponible, $date_recept, $no_dossier));
        } else {
            $sql = "UPDATE ac_j_user_anticorps SET id_anticorps=?, id_utilisateur=?, disponible=?, date_recept=?, no_dossier=? WHERE id=?";
            $this->runRequest($sql, array($id_antibody, $id_utilisateur, $disponible, $date_recept, $no_dossier, $id));
        }
    }

    public function getInfoForAntibody($id_antibody) {
        if ($id_antibody == 0) {
            return array();
        }
        $sql = "SELECT * FROM ac_j_user_anticorps WHERE id_anticorps=?";
        $data = $this->runRequest($sql, array($id_antibody))->fetchAll();
        $modelUser = new EcUser();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["user"] = $modelUser->getUserFUllName($data[$i]["id_utilisateur"]);
        }
        return $data;
    }

    public function delete($id) {
        $sql = "DELETE FROM ac_j_user_anticorps WHERE id=?";
        $this->runRequest($sql, array($id));
    }

}
