<?php

require_once 'Framework/Model.php';

require_once 'Modules/core/Model/CoreVirtual.php';
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

    public function __construct() {
        $this->tableName = "ac_anticorps";
    }

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
                `reactivity` varchar(30) NOT NULL DEFAULT '',
				`reference` varchar(30) NOT NULL DEFAULT '',
				`clone` varchar(30) NOT NULL DEFAULT '',
 				`lot` varchar(30) NOT NULL DEFAULT '',
				`id_isotype` int(11) NOT NULL DEFAULT '0',
				`stockage` varchar(30) NOT NULL DEFAULT '',
                `id_staining` FLOAT(11) NOT NULL DEFAULT 1,
                `id_application` FLOAT(11) NOT NULL DEFAULT 1,
                `export_calatog` int(1) NOT NULL DEFAULT 0,
                `image_url` varchar(250) NOT NULL DEFAULT '',
                `image_desc` varchar(250) NOT NULL DEFAULT '',
				`id_space` int(11) NOT NULL DEFAULT 0,
  				PRIMARY KEY (`id`)
				)";
        $this->runRequest($sql);
		
        /*
		$sql = "CREATE TABLE IF NOT EXISTS `ac_j_user_anticorps` (
  				`id_anticorps` int(11) NOT NULL,
  				`id_utilisateur` int(11) NOT NULL,	
				`disponible` int(2) NOT NULL,		
				`date_recept` DATE NOT NULL,
				`no_dossier` varchar(12) NOT NULL,
				`id_space` int(11) NOT NULL DEFAULT 0
                )";
        $this->runRequest($sql);
        */

        // add new column
        $this->addColumn("ac_anticorps", "id_staining", "float(11)", 1);
        $this->addColumn("ac_anticorps", "id_application", "float(11)", 1);
        $this->addColumn("ac_anticorps", "export_catalog", "int(1)", 0);
        $this->addColumn("ac_anticorps", "image_url", "varchar(250)", "");
        $this->addColumn("ac_anticorps", "image_desc", "varchar(250)", "");
        $this->addColumn("ac_anticorps", "id_space", "INT(11)", 0);

    }

    public function mergeUsers($users){
        for($i = 1 ; $i < count($users) ; $i++){
            $sql = "UPDATE ac_j_user_anticorps SET id_utilisateur=? WHERE id_utilisateur=?";
            $this->runRequest($sql, array($users[0], $users[$i]));
        }
    } 
    
    public function getIdFromNoH2P2($no_h2p2, $id_space){
        $sql = "SELECT id FROM ac_anticorps WHERE no_h2p2=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($no_h2p2, $id_space));
        if($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }
    
    public function setExportCatalog($id_space, $id, $exportCatalog) {
        $sql = "UPDATE ac_anticorps SET export_catalog=? WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($exportCatalog, $id, $id_space));
    }

    public function setImageDesc($id_space, $id, $image_desc) {
        $sql = "UPDATE ac_anticorps SET image_desc=? WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($image_desc, $id, $id_space));
    }

    public function setImageUrl($id_space, $id, $image_url) {
        $sql = "UPDATE ac_anticorps SET image_url=? WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($image_url, $id, $id_space));
    }

    public function setApplicationStaining($id_space ,$id, $id_staining, $id_application) {
        $sql = "UPDATE ac_anticorps SET id_staining=?, id_application=? WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id_staining, $id_application, $id, $id_space));
    }

    public function getBySpace($id_space){
        $sql = "select * from ac_anticorps WHERE id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }
    
    /**
     * Get the anticorps information
     *
     * @param string $sortentry column used to sort the users
     * @return multitype:
     */
    public function getAnticorps($id_space ,$letter = 'All', $sortentry = 'id') {
        $sql = "SELECT * FROM ac_anticorps WHERE id_space=? AND deleted=0";
        
        if($letter != 'All' && $letter != ''){
            $sql .= " AND nom LIKE '".$letter."%'";
        }
        $sql .= " ORDER BY " . $sortentry . " ASC;";
        
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    public function getLargerNoH2P2($id_space) {
        $sql = "SELECT no_h2p2 from ac_anticorps WHERE id_space=? AND deleted=0 order by no_h2p2 DESC;";
        $user = $this->runRequest($sql, array($id_space));
        $tmp = $user->fetch();
        return $tmp? $tmp[0] : null;
    }

    public function isAnticorps($id_space ,$no_h2p2) {
        $sql = "SELECT * from ac_anticorps where no_h2p2=? AND id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($no_h2p2, $id_space));
        return ($user->rowCount() == 1);
    }

    public function isAnticorpsID($id_space, $id) {
        $sql = "SELECT * from ac_anticorps where id=? AND id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($id, $id_space));
        return ($user->rowCount() == 1);
    }

    /**
     * Add an antibody to the database
     * 
     * @param unknown $nom
     * @param unknown $no_h2p2
     * @param unknown $fournisseur
     * @param unknown $id_source
     * * @param unknown $reactivity
     * @param unknown $reference
     * @param unknown $clone
     * @param unknown $lot
     * @param unknown $id_isotype
     * @param unknown $stockage
     * @param unknown $disponible
     * @param unknown $date_recept
     * @return string
     */
    public function addAnticorps($id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reactivity, $reference, $clone, $lot, $id_isotype, $stockage) {
        //$cvm = new CoreVirtual();
        $new_no_h2p2 = $no_h2p2;
        if(!$no_h2p2) {
            $redis = new Redis();
            $redis->pconnect(Configuration::get('redis_host', 'redis'), Configuration::get('redis_port', 6379));
            $new_no_h2p2 = $redis->incr("pfm:$id_space:antibodies");
            $redis->close();
            //$new_no_h2p2 = $cvm->new('anticorps');
        }

        $sql = "insert into ac_anticorps(id_space, nom, no_h2p2, fournisseur, id_source, reactivity, reference, 
										 clone, lot, id_isotype, stockage)"
                . " values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->runRequest($sql, array($id_space, $nom, $new_no_h2p2, $fournisseur, $id_source, $reactivity, $reference, $clone,
            $lot, $id_isotype, $stockage));

        return $this->getDatabase()->lastInsertId();
    }
    
    public function setAntibody($id, $id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reactivity, $reference, $clone, $lot, $id_isotype, $stockage){
        if($this->isAnticorpsID($id_space ,$id)){
            $this->updateAnticorps($id, $id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reactivity, $reference, $clone, $lot, $id_isotype, $stockage);
            return $id;
        }
        else{
            return $this->addAnticorps($id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reactivity, $reference, $clone, $lot, $id_isotype, $stockage);
        }
    }

    public function importAnticorps($id, $id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reactivity, $reference, $clone, $lot, $id_isotype, $stockage) {


        $sql = "insert into ac_anticorps(id, id_space, nom, no_h2p2, fournisseur, id_source, reactivity, reference,
										 clone, lot, id_isotype, stockage)"
                . " values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->runRequest($sql, array($id, $id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reactivity, $reference, $clone,
            $lot, $id_isotype, $stockage));

        return $this->getDatabase()->lastInsertId();
    }

    public function updateAnticorps($id, $id_space, $nom, $no_h2p2, $fournisseur, $id_source, $reactivity, $reference, $clone, $lot, $id_isotype, $stockage) {
        $sql = "UPDATE ac_anticorps SET nom=?, no_h2p2=?, fournisseur=?, id_source=?, reactivity=?, reference=?, 
										 clone=?, lot=?, id_isotype=?, stockage=?
									WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($nom, $no_h2p2, $fournisseur, $id_source, $reactivity, $reference, $clone,
            $lot, $id_isotype, $stockage, $id, $id_space));
    }

    /**
     * Get the antibody info by changing the ids by names
     *
     * @param string $sortentry column used to sort the users
     * @return Ambigous <multitype:, boolean>
     */
    public function getAnticorpsInfo($id_space, $letter="") {
        $ac = $this->getAnticorps($id_space, $letter, 'no_h2p2');
        return $this->anticorpsInfo($id_space ,$ac);
    }

    public function getAnticorpsInfoCatalog($id_space) {
        $sql = "select * from ac_anticorps WHERE export_catalog=1 AND id_space=? AND deleted=0 ORDER BY no_h2p2 ASC;";
        $user = $this->runRequest($sql, array($id_space));
        $ac = $user->fetchAll();

        return $this->anticorpsInfo($id_space, $ac, true);
    }

    private function anticorpsInfo($id_space, $ac, $catalog = false) {
        $isotypeModel = new Isotype();
        $sourceModel = new Source();
        $tissusModel = new Tissus();
        $stainingModel = new AcStaining();
        $applicationModel = new AcApplication();
        for ($i = 0; $i < count($ac); $i++) {
            $tmp = $isotypeModel->get($id_space, $ac[$i]['id_isotype']);
            $ac[$i]['isotype'] = $tmp['nom'];
            $tmp = $sourceModel->get($id_space ,$ac[$i]['id_source']);
            $ac[$i]['source'] = $tmp['nom'];
            $ac[$i]['tissus'] = $tissusModel->getTissus($id_space, $ac[$i]['id'], $catalog);
            $ac[$i]['proprietaire'] = $this->getOwners($id_space ,$ac[$i]['id']);

            $ac[$i]['staining'] = $stainingModel->getNameFromId($id_space ,$ac[$i]['id_staining']);
            $ac[$i]['application'] = $applicationModel->getNameFromId($id_space, $ac[$i]['id_application']);
            //print_r($ac[$i]['tissus']);
        }
        return $ac;
    }

    public function getAnticorpsInfoSearch($id_space, $columnName, $searchTxt) {
        $sql = "select * from ac_anticorps where id_space=? AND deleted=0 AND " . $columnName . " LIKE '%$searchTxt%'";
        $user = $this->runRequest($sql, array($id_space, $searchTxt));
        $ac = $user->fetchAll();

        return $this->anticorpsInfo($id_space, $ac);
    }

    public function getAnticorpsProprioSearch($id_space, $columnName, $searchTxt) {

        $acs = $this->getAnticorpsInfo($id_space);

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

    public function getAnticorpsTissusSearch($id_space, $columnName, $searchTxt) {

        /*
          `espece` int(11) NOT NULL,
          `organe` int(11) NOT NULL,
          `valide` enum('oui','non') NOT NULL,
          `ref_bloc` varchar(30) NOT NULL,
         */

        $acs = $this->getAnticorpsInfo($id_space);

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

    public function getOwners($id_space, $acId) {

        $sql = "SELECT ac_j_user_anticorps.id_utilisateur AS id_user, ac_j_user_anticorps.date_recept AS date_recept, 
					   ac_j_user_anticorps.disponible AS disponible, ac_j_user_anticorps.no_dossier AS no_dossier, 
					   core_users.name AS name, core_users.firstname AS firstname
				FROM ac_j_user_anticorps
				INNER JOIN core_users on core_users.id = ac_j_user_anticorps.id_utilisateur
				WHERE ac_j_user_anticorps.id_anticorps=? AND ac_j_user_anticorps.id_space=? AND ac_j_user_anticorps.deleted=0
				ORDER BY core_users.name";

        $user = $this->runRequest($sql, array($acId, $id_space));
        return $user->fetchAll();
    }

    public function addOwner($id_space ,$id_user, $id_anticorps, $date, $disponible, $no_dossier) {

        $sql = "insert into ac_j_user_anticorps(id_space, id_utilisateur, id_anticorps, date_recept, disponible, no_dossier)"
                . " values(?, ?, ?, ?, ?, ?)";
        $pdo = $this->runRequest($sql, array($id_space, $id_user, $id_anticorps, $date, $disponible, $no_dossier));

        return $this->getDatabase()->lastInsertId();
    }
    
    public function updateOwnerId($id_space, $old_id, $anticorps_id, $new_id){
        $sql = "UPDATE ac_j_user_anticorps SET id_utilisateur=? WHERE id_utilisateur=? AND id_anticorps=? AND id_space=?";
        $this->runRequest($sql, array($new_id, $old_id, $anticorps_id, $id_space));
    }

    public function removeOwners($id_space, $id) {
        $sql = "DELETE FROM ac_j_user_anticorps WHERE id_anticorps = ? AND id_space=?";
        $req = $this->runRequest($sql, array($id, $id_space));
    }

    public function getAnticorpsFromId($id_space, $id) {
        $sql = "SELECT * FROM ac_anticorps WHERE id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        $anticorps = $req->fetch();
        if(!$anticorps){
            return null;
        }

        // get owners
        $anticorps["proprietaire"] = $this->getOwners($id_space, $id);

        // get tissus
        $tissusModel = new Tissus();
        $anticorps['tissus'] = $tissusModel->getTissus($id_space ,$id);

        return $anticorps;
    }

    public function getAnticorpsIdFromNoH2p2($id_space, $no_h2p2) {
        $sql = "SELECT id FROM ac_anticorps WHERE no_h2p2=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($no_h2p2, $id_space));
        $anticorps = $req->fetch();

        return $anticorps;
    }

    public function getDefaultAnticorps() {

        $anticorps["id"] = "";
        $anticorps["id_space"] = 0;
        $anticorps["nom"] = "";
        $anticorps["no_h2p2"] = 0;
        $anticorps["fournisseur"] = "";
        $anticorps["id_source"] = "";
        $anticorps["reactivity"] = "";
        $anticorps["reference"] = "";
        $anticorps["clone"] = "";
        $anticorps["lot"] = "";
        $anticorps["id_isotype"] = "";
        $anticorps["stockage"] = "";
        $anticorps["proprietaire"] = array();
        $anticorps['tissus'] = array();

        $anticorps['id_staining'] = 1;
        $anticorps['id_application'] = 1;
        $anticorps['export_catalog'] = 0;
        $anticorps['image_url'] = "";
        $anticorps['image_desc'] = "";

        return $anticorps;
    }

    private function endsWith($haystack, $needle) {
        $length = strlen($needle);
        if ($length == 0) {
            return true;
        }
        return (substr($haystack, -$length) === $needle);
    }

    private function compare($str, $serach) {

        if ($this->endsWith($serach, "*")) {
            $search2 = substr($serach, 0, -1);

            if ($str === $search2) {
                return true;
            }
            return false;
        } else {
            return stristr($str, $serach);
        }
    }

    public function searchAdv($id_space ,$searchName, $searchNoH2P2, $searchSource, $searchCible, $searchValide, $searchResp) {

        $acs = $this->getAnticorpsInfo($id_space, "");

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
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE ac_anticorps SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        $this->runRequest($sql, array($id, $id_space));
    }

}
