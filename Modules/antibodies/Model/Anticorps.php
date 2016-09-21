<?php

require_once 'Framework/Model.php';
require_once 'Modules/antibodies/Model/Isotype.php';
require_once 'Modules/antibodies/Model/Source.php';
require_once 'Modules/antibodies/Model/Tissus.php';
require_once 'Modules/antibodies/Model/AcStaining.php';
require_once 'Modules/antibodies/Model/AcApplication.php';

/**
 * Class defining the Anticorps model
 *
 * @author Sylvain Prigent
 */
class Anticorps extends Model {

    /**
     * Create the unit table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `ac_anticorps` (
  				`id` int(11) NOT NULL AUTO_INCREMENT,
  				`nom` varchar(30) NOT NULL DEFAULT '',
  				`no_h2p2` int(11) NOT NULL DEFAULT '0',
				`fournisseur` varchar(30) NOT NULL DEFAULT '',
				`id_source` int(11) NOT NULL DEFAULT '0',
				`reference` varchar(30) NOT NULL DEFAULT '',
				`clone` varchar(30) NOT NULL DEFAULT '',
 				`lot` varchar(30) NOT NULL DEFAULT '',
				`id_isotype` int(11) NOT NULL DEFAULT '0',
				`stockage` varchar(30) NOT NULL DEFAULT '',
                                `id_space` INT(11) NOT NULL,
  				PRIMARY KEY (`id`)
				);
				
				CREATE TABLE IF NOT EXISTS `ac_j_user_anticorps` (
  				`id_anticorps` int(11) NOT NULL,
  				`id_utilisateur` int(11) NOT NULL,	
				`disponible` int(2) NOT NULL,		
				`date_recept` DATE NOT NULL,
				`no_dossier` varchar(12) NOT NULL
				);
				";

        $this->runRequest($sql);

        // add new column
        $this->addColumn("ac_anticorps", "id_staining", "float(11)", 1);
        $this->addColumn("ac_anticorps", "id_application", "float(11)", 1);
        $this->addColumn("ac_anticorps", "export_catalog", "int(1)", 1);
        $this->addColumn("ac_anticorps", "image_url", "varchar(250)", "");
        $this->addColumn("ac_anticorps", "image_desc", "varchar(250)", "");
    }

    public function setExportCatalog($id, $exportCatalog) {
        $sql = "UPDATE ac_anticorps SET export_catalog=? WHERE id=?";
        $this->runRequest($sql, array($exportCatalog, $id));
    }

    public function setImageDesc($id, $image_desc) {
        $sql = "UPDATE ac_anticorps SET image_desc=? WHERE id=?";
        $this->runRequest($sql, array($image_desc, $id));
    }

    public function setImageUrl($id, $image_url) {
        $sql = "UPDATE ac_anticorps SET image_url=? WHERE id=?";
        $this->runRequest($sql, array($image_url, $id));
    }

    public function setApplicationStaining($id, $id_staining, $id_application) {
        $sql = "UPDATE ac_anticorps SET id_staining=?, id_application=? WHERE id=?";
        $this->runRequest($sql, array($id_staining, $id_application, $id));
    }

    public function getBySpace($id_space){
        $sql = "select * from ac_anticorps WHERE id_space=?";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }
    
    /**
     * Get the anticorps information
     *
     * @param string $sortentry column used to sort the users
     * @return multitype:
     */
    public function getAnticorps($sortentry = 'id') {
        $sql = "select * from ac_anticorps order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql);
        return $user->fetchAll();
    }

    public function getLargerNoH2P2() {
        $sql = "select no_h2p2 from ac_anticorps order by no_h2p2 DESC;";
        $user = $this->runRequest($sql);
        $tmp = $user->fetch();
        return $tmp[0];
    }

    public function isAnticorps($no_h2p2) {
        $sql = "select * from ac_anticorps where no_h2p2=?";
        $user = $this->runRequest($sql, array($no_h2p2));
        if ($user->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    public function isAnticorpsID($id) {
        $sql = "select * from ac_anticorps where id=?";
        $user = $this->runRequest($sql, array($id));
        if ($user->rowCount() == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add an antibody to the database
     * 
     * @param unknown $nom
     * @param unknown $no_h2p2
     * @param unknown $fournisseur
     * @param unknown $id_source
     * @param unknown $reference
     * @param unknown $clone
     * @param unknown $lot
     * @param unknown $id_isotype
     * @param unknown $stockage
     * @param unknown $disponible
     * @param unknown $date_recept
     * @return string
     */
    public function addAnticorps($id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reference, $clone, $lot, $id_isotype, $stockage) {


        $sql = "insert into ac_anticorps(id_space, nom, no_h2p2, fournisseur, id_source, reference, 
										 clone, lot, id_isotype, stockage)"
                . " values(?, ?, ?, ?, ?, ?, ?, ?, ?,?)";
        $this->runRequest($sql, array($id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reference, $clone,
            $lot, $id_isotype, $stockage));

        return $this->getDatabase()->lastInsertId();
    }

    public function importAnticorps($id, $id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reference, $clone, $lot, $id_isotype, $stockage) {


        $sql = "insert into ac_anticorps(id, id_space, nom, no_h2p2, fournisseur, id_source, reference,
										 clone, lot, id_isotype, stockage)"
                . " values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->runRequest($sql, array($id, $id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reference, $clone,
            $lot, $id_isotype, $stockage));

        return $this->getDatabase()->lastInsertId();
    }

    public function updateAnticorps($id, $id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reference, $clone, $lot, $id_isotype, $stockage) {
        $sql = "UPDATE ac_anticorps SET id_space=?, nom=?, no_h2p2=?, fournisseur=?, id_source=?, reference=?, 
										 clone=?, lot=?, id_isotype=?, stockage=?
									WHERE id=?";
        $this->runRequest($sql, array($id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reference, $clone,
            $lot, $id_isotype, $stockage, $id));
    }

    /**
     * Get the antibody info by changing the ids by names
     *
     * @param string $sortentry column used to sort the users
     * @return Ambigous <multitype:, boolean>
     */
    public function getAnticorpsInfo($sortentry = 'id') {
        $ac = $this->getAnticorps($sortentry);

        return $this->anticorpsInfo($ac);
    }

    public function getAnticorpsInfoCatalog() {
        $sql = "select * from ac_anticorps WHERE export_catalog=1 ORDER BY no_h2p2 ASC;";
        $user = $this->runRequest($sql);
        $ac = $user->fetchAll();

        return $this->anticorpsInfo($ac, true);
    }

    private function anticorpsInfo($ac, $catalog = false) {
        $isotypeModel = new Isotype();
        $sourceModel = new Source();
        $tissusModel = new Tissus();
        $stainingModel = new AcStaining();
        $applicationModel = new AcApplication();
        for ($i = 0; $i < count($ac); $i++) {
            $tmp = $isotypeModel->get($ac[$i]['id_isotype']);
            $ac[$i]['isotype'] = $tmp['nom'];
            $tmp = $sourceModel->get($ac[$i]['id_source']);
            $ac[$i]['source'] = $tmp['nom'];
            $ac[$i]['tissus'] = $tissusModel->getTissus($ac[$i]['id'], $catalog);
            $ac[$i]['proprietaire'] = $this->getOwners($ac[$i]['id']);

            $ac[$i]['staining'] = $stainingModel->getNameFromId($ac[$i]['id_staining']);
            $ac[$i]['application'] = $applicationModel->getNameFromId($ac[$i]['id_application']);
            //print_r($ac[$i]['tissus']);
        }
        return $ac;
    }

    public function getAnticorpsInfoSearch($columnName, $searchTxt) {
        $sql = "select * from ac_anticorps where " . $columnName . " LIKE '%$searchTxt%'";
        $user = $this->runRequest($sql, array($searchTxt));
        $ac = $user->fetchAll();

        return $this->anticorpsInfo($ac);
    }

    public function getAnticorpsProprioSearch($columnName, $searchTxt) {

        $acs = $this->getAnticorpsInfo();

        if ($columnName == "nom_proprio") {
            $anticorps = array();
            foreach ($acs as $as) {

                foreach ($as["proprietaire"] as $proprio) {
                    if (strstr($proprio["name"], $searchTxt)) {
                        $anticorps[] = $as;
                        break;
                    }
                }
            }
            return $anticorps;
        } elseif ($columnName == "disponibilite") {
            $anticorps = array();
            foreach ($acs as $as) {

                foreach ($as["proprietaire"] as $proprio) {
                    if (strstr($proprio["disponible"], $searchTxt)) {
                        $anticorps[] = $as;
                        break;
                    }
                }
            }
            return $anticorps;
        } elseif ($columnName == "date_recept") {
            $anticorps = array();
            foreach ($acs as $as) {
                //print_r($as);
                foreach ($as["proprietaire"] as $proprio) {
                    if (strstr($proprio["date_recept"], $searchTxt)) {
                        $anticorps[] = $as;
                        break;
                    }
                }
            }
            return $anticorps;
        }
    }

    public function getAnticorpsTissusSearch($columnName, $searchTxt) {

        /*
          `espece` int(11) NOT NULL,
          `organe` int(11) NOT NULL,
          `valide` enum('oui','non') NOT NULL,
          `ref_bloc` varchar(30) NOT NULL,
         */

        $acs = $this->getAnticorpsInfo();

        if ($columnName == "dilution") {
            $anticorps = array();
            foreach ($acs as $as) {

                foreach ($as["tissus"] as $proprio) {
                    if (strstr($proprio["dilution"], $searchTxt)) {
                        $anticorps[] = $as;
                        break;
                    }
                }
            }
            return $anticorps;
        } elseif ($columnName == "temps_incubation") {
            $anticorps = array();
            foreach ($acs as $as) {

                foreach ($as["tissus"] as $proprio) {
                    if (strstr($proprio["temps_incubation"], $searchTxt)) {
                        $anticorps[] = $as;
                        break;
                    }
                }
            }
            return $anticorps;
        } elseif ($columnName == "ref_protocol") {
            $anticorps = array();
            foreach ($acs as $as) {
                //print_r($as);
                foreach ($as["tissus"] as $proprio) {
                    if (strstr($proprio["ref_protocol"], $searchTxt)) {
                        $anticorps[] = $as;
                        break;
                    }
                }
            }
            return $anticorps;
        } elseif ($columnName == "espece") {
            $anticorps = array();
            foreach ($acs as $as) {
                //print_r($as);
                foreach ($as["tissus"] as $proprio) {
                    if (strstr($proprio["espece"], $searchTxt)) {
                        $anticorps[] = $as;
                        break;
                    }
                }
            }
            return $anticorps;
        } elseif ($columnName == "organe") {
            $anticorps = array();
            foreach ($acs as $as) {
                //print_r($as);
                foreach ($as["tissus"] as $proprio) {
                    if (strstr($proprio["organe"], $searchTxt)) {
                        $anticorps[] = $as;
                        break;
                    }
                }
            }
            return $anticorps;
        } elseif ($columnName == "valide") {
            $anticorps = array();
            foreach ($acs as $as) {
                //print_r($as);
                foreach ($as["tissus"] as $proprio) {
                    if (strstr($proprio["valide"], $searchTxt)) {
                        $anticorps[] = $as;
                        break;
                    }
                }
            }
            return $anticorps;
        } elseif ($columnName == "ref_bloc") {
            $anticorps = array();
            foreach ($acs as $as) {
                //print_r($as);
                foreach ($as["tissus"] as $proprio) {
                    if (strstr($proprio["ref_bloc"], $searchTxt)) {
                        $anticorps[] = $as;
                        break;
                    }
                }
            }
            return $anticorps;
        }
    }

    public function getOwners($acId) {

        $sql = "SELECT ac_j_user_anticorps.id_utilisateur AS id_user, ac_j_user_anticorps.date_recept AS date_recept, 
					   ac_j_user_anticorps.disponible AS disponible, ac_j_user_anticorps.no_dossier AS no_dossier, 
					   core_users.name AS name, core_users.firstname AS firstname
					FROM ac_j_user_anticorps
					     INNER JOIN core_users on core_users.id = ac_j_user_anticorps.id_utilisateur
				WHERE ac_j_user_anticorps.id_anticorps=?
				ORDER BY core_users.name";

        $user = $this->runRequest($sql, array($acId));
        return $user->fetchAll();
    }

    public function addOwner($id_user, $id_anticorps, $date, $disponible, $no_dossier) {

        $sql = "insert into ac_j_user_anticorps(id_utilisateur, id_anticorps, date_recept, disponible, no_dossier)"
                . " values(?, ?, ?, ?, ?)";
        $pdo = $this->runRequest($sql, array($id_user, $id_anticorps, $date, $disponible, $no_dossier));

        return $this->getDatabase()->lastInsertId();
    }

    public function removeOwners($id) {
        $sql = "DELETE FROM ac_j_user_anticorps WHERE id_anticorps = ?";
        $req = $this->runRequest($sql, array($id));
    }

    public function getAnticorpsFromId($id) {
        $sql = "SELECT * FROM ac_anticorps WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        $anticorps = $req->fetch();

        // get owners
        $anticorps["proprietaire"] = $this->getOwners($id);

        // get tissus
        $tissusModel = new Tissus();
        $anticorps['tissus'] = $tissusModel->getTissus($id);

        return $anticorps;
    }

    public function getAnticorpsIdFromNoH2p2($no_h2p2) {
        $sql = "SELECT id FROM ac_anticorps WHERE no_h2p2=?";
        $req = $this->runRequest($sql, array($no_h2p2));
        $anticorps = $req->fetch();

        return $anticorps;
    }

    public function getDefaultAnticorps() {

        $anticorps["id"] = "";
        $anticorps["id_space"] = 0;
        $anticorps["nom"] = "";
        $anticorps["no_h2p2"] = $this->getLargerNoH2P2() + 1;
        $anticorps["fournisseur"] = "";
        $anticorps["id_source"] = "";
        $anticorps["reference"] = "";
        $anticorps["clone"] = "";
        $anticorps["lot"] = "";
        $anticorps["id_isotype"] = "";
        $anticorps["stockage"] = "";
        $anticorps["proprietaire"] = array();
        $anticorps['tissus'] = array();

        $anticorps['id_staining'] = 1;
        $anticorps['id_application'] = 1;
        $anticorps['export_catalog'] = 1;
        $anticorps['image_url'] = "";
        $anticorps['image_desc'] = "";

        return $anticorps;
    }

    private function endsWith($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }

        //echo "last char = " . substr($haystack, -$length) . "<br/>";
        return (substr($haystack, -$length) === $needle);
    }

    private function compare($str, $serach) {

        //echo "compare <br/>";

        if ($this->endsWith($serach, "*")) {
            $search2 = substr($serach, 0, -1);

            //echo "sub string = " . $search2 . "<br/>";

            if ($str === $search2) {
                return true;
            }
            return false;
        } else {
            return stristr($str, $serach);
        }
    }

    public function searchAdv($searchName, $searchNoH2P2, $searchSource, $searchCible, $searchValide, $searchResp) {

        $acs = $this->getAnticorpsInfo();

        if ($searchName != "") {
            $anticorps = array();
            foreach ($acs as $as) {
                if ($this->compare($as["nom"], $searchName)) {
                    $anticorps[] = $as;
                }
            }
            $acs = $anticorps;
        }
        if ($searchNoH2P2 != "") {
            $anticorps = array();
            foreach ($acs as $as) {
                if ($as["no_h2p2"] == $searchNoH2P2) {
                    $anticorps[] = $as;
                }
            }
            $acs = $anticorps;
        }
        if ($searchSource != "") {
            $anticorps = array();
            foreach ($acs as $as) {
                if ($as["source"] == $searchSource) {
                    $anticorps[] = $as;
                }
            }
            $acs = $anticorps;
        }

        if ($searchCible != "") {
            $anticorps = array();
            foreach ($acs as $as) {
                foreach ($as["tissus"] as $tissus) {
                    if ($this->compare($tissus["organe"], $searchCible)) {
                        $anticorps[] = $as;
                    }
                }
            }
            $acs = $anticorps;
        }

        if ($searchValide != 0) {
            $anticorps = array();
            foreach ($acs as $as) {
                foreach ($as["tissus"] as $tissus) {
                    if ($tissus["status"] == $searchValide) {
                        $anticorps[] = $as;
                    }
                }
            }
            $acs = $anticorps;
        }
        if ($searchResp != "") {
            $anticorps = array();
            foreach ($acs as $as) {
                foreach ($as["proprietaire"] as $proprio) {
                    if ($this->compare($proprio["name"], $searchResp)) {
                        $anticorps[] = $as;
                        break;
                    }
                }
            }
            $acs = $anticorps;
        }
        return $acs;

        /*
          $sql = "SELECT ac_anticorps.id AS id
          FROM ac_anticorps
          INNER JOIN ac_j_tissu_anticorps on ac_anticorps.id = ac_j_tissu_anticorps.id_anticops
          INNER JOIN ac_j_user_anticorps on ac_organes.id = ac_j_user_anticorps.id_anticops
          INNER JOIN ac_sources on ac_anticorps.id_source = ac_source.id
          INNER JOIN core_user on ac_j_user_anticorps.id_utilisateur = core_user.id
          WHERE ac_anticorps.nom LIKE ?
          AND   ac_anticorps.no_h2p2 LIKE ?
          AND   ac_source.name LIKE ?
          AND   ac_j_tissu_anticorps.

          ";

          //$sql = "select * from ac_j_tissu_anticorps where id_anticorps=?";
          $res = $this->runRequest($sql, array($searchName, $searchNoH2P2, $searchSource, $searchCible, $searchValide, $searchResp));
          return $res->fetchAll();
         */
    }

    public function delete($id) {
        $sql = "DELETE FROM ac_anticorps WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
