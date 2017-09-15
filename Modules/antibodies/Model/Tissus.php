<?php

require_once 'Framework/Model.php';
require_once 'Modules/antibodies/Model/AcProtocol.php';

/**
 * Class defining the Tissus model
 *
 * @author Sylvain Prigent
 */
class Tissus extends Model {

    /**
     * Create the isotype table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `ac_j_tissu_anticorps` (
  				`id` int(11) NOT NULL AUTO_INCREMENT,
				`id_anticorps` int(11) NOT NULL,
  				`espece` int(11) NOT NULL,
  				`organe` int(11) NOT NULL, 
  				`status` int(1) NOT NULL,  
  				`ref_bloc` varchar(30) NOT NULL,
				`dilution` varchar(30) NOT NULL,
				`temps_incubation` varchar(30) NOT NULL,
  				`ref_protocol` varchar(11) NOT NULL,
				`prelevement` int(1) NOT NULL,
				`comment` text NOT NULL,
                                `image_url` varchar(512) NOT NULL,
  				PRIMARY KEY (`id`)
				);";

        $this->runRequest($sql);

        $this->addColumn("ac_j_tissu_anticorps", "image_url", "varchar(512)", "");
    }

    public function getTissusById($id) {
        $sql = "SELECT * FROM ac_j_tissu_anticorps WHERE id=?";
        return $this->runRequest($sql, array($id))->fetch();
    }

    public function setImageUrl($id, $url) {
        $sql = "UPDATE ac_j_tissu_anticorps SET image_url=? WHERE id=?";
        $this->runRequest($sql, array($url, $id));
    }

    public function setTissus($id, $id_anticorps, $espece, $organe, $status, $ref_bloc, $dilution, $temps_incubation, $ref_protocol, $prelevement, $comment = "") {

        if ($id == 0) {
            $sql = "insert into ac_j_tissu_anticorps(id_anticorps, espece, 
                                                     organe, status, ref_bloc,
                                                     dilution, temps_incubation, 
                                                     ref_protocol, prelevement,
                                                     comment)"
                    . " values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
            $this->runRequest($sql, array($id_anticorps, $espece, $organe, $status, $ref_bloc, $dilution, $temps_incubation, $ref_protocol, $prelevement, $comment));
            return $this->getDatabase()->lastInsertId();
        } else {
            $sql = "UPDATE ac_j_tissu_anticorps SET id_anticorps=?, espece=?, organe=?, status=?, "
                    . "ref_bloc=?, dilution=?, temps_incubation=?, ref_protocol=?, prelevement=?, "
                    . "comment=? WHERE id=?";
            $this->runRequest($sql, array($id_anticorps, $espece, $organe, $status, $ref_bloc, $dilution, $temps_incubation, $ref_protocol, $prelevement, $comment, $id));
            return $id;
        }
    }

    public function addTissus($id_anticorps, $espece, $organe, $status, $ref_bloc, $dilution, $temps_incubation, $ref_protocol, $prelevement, $comment = "") {
        $sql = "insert into ac_j_tissu_anticorps(id_anticorps, espece, 
				                                    organe, status, ref_bloc,
													dilution, temps_incubation, 
													ref_protocol, prelevement,
													comment)"
                . " values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->runRequest($sql, array($id_anticorps, $espece, $organe, $status, $ref_bloc, $dilution, $temps_incubation, $ref_protocol, $prelevement, $comment));
        return $this->getDatabase()->lastInsertId();
    }

    public function importTissus($id, $id_space, $id_anticorps, $espece, $organe, $status, $ref_bloc, $dilution, $temps_incubation, $ref_protocol, $prelevement, $comment = "") {
        $sql = "insert into ac_j_tissu_anticorps(id, id_space, id_anticorps, espece,
				                                    organe, status, ref_bloc,
													dilution, temps_incubation, 
				                                    ref_protocol, prelevement, comment)"
                . " values(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $this->runRequest($sql, array($id, $id_space, $id_anticorps, $espece, $organe, $status, $ref_bloc, $dilution, $temps_incubation, $ref_protocol, $prelevement, $comment));
    }

    public function getTissusCatalog($id_anticorps) {

        $sql = "SELECT DISTINCT ac_j_tissu_anticorps.status AS status,
				    ac_especes.nom AS espece,
                                    ac_prelevements.nom AS prelevement			
				FROM ac_j_tissu_anticorps
				INNER JOIN ac_especes on ac_j_tissu_anticorps.espece = ac_especes.id
				INNER JOIN ac_organes on ac_j_tissu_anticorps.organe = ac_organes.id
				INNER JOIN ac_prelevements on ac_j_tissu_anticorps.prelevement = ac_prelevements.id
				WHERE ac_j_tissu_anticorps.id_anticorps=?";

        //$sql = "select * from ac_j_tissu_anticorps where id_anticorps=?";
        $res = $this->runRequest($sql, array($id_anticorps));
        return $res->fetchAll();
    }

    public function getInfoForAntibody($id_anticorps) {
        if($id_anticorps == 0){
            return array();
        }
        $sql = "SELECT DISTINCT 
                    ac_j_tissu_anticorps.ref_protocol AS ref_protocol,
                    ac_j_tissu_anticorps.dilution AS dilution,    
                    ac_j_tissu_anticorps.comment AS comment, 
                    ac_j_tissu_anticorps.ref_bloc AS ref_bloc, 
                    ac_j_tissu_anticorps.image_url AS image_url,
                    ac_j_tissu_anticorps.id AS id, 
				    ac_especes.nom AS espece,
                                    ac_organes.nom AS organe,
                                    ac_prelevements.nom AS prelevement,
                                    ac_status.nom AS status
				FROM ac_j_tissu_anticorps
				INNER JOIN ac_especes on ac_j_tissu_anticorps.espece = ac_especes.id
				INNER JOIN ac_organes on ac_j_tissu_anticorps.organe = ac_organes.id
				INNER JOIN ac_prelevements on ac_j_tissu_anticorps.prelevement = ac_prelevements.id
				INNER JOIN ac_status on ac_j_tissu_anticorps.status = ac_status.id
                                WHERE ac_j_tissu_anticorps.id_anticorps=?";

        //$sql = "select * from ac_j_tissu_anticorps where id_anticorps=?";
        $res = $this->runRequest($sql, array($id_anticorps));
        return $res->fetchAll();
    }

    public function getTissus($id_anticorps, $catalog = false) {

        $sql = "SELECT ac_j_tissu_anticorps.id AS id, 
					   ac_j_tissu_anticorps.id_anticorps AS id_anticorps, 	
				       ac_j_tissu_anticorps.status AS status,
				       ac_j_tissu_anticorps.ref_bloc AS ref_bloc,
				       ac_j_tissu_anticorps.dilution AS dilution,
				       ac_j_tissu_anticorps.temps_incubation AS temps_incubation,
					   ac_j_tissu_anticorps.ref_protocol AS ref_protocol,
					   ac_j_tissu_anticorps.comment AS comment,	
                                           ac_j_tissu_anticorps.image_url AS image_url,
					   ac_especes.nom AS espece, ac_especes.id AS espece_id,
					   ac_organes.nom AS organe, ac_organes.id AS organe_id,
					   ac_prelevements.nom AS prelevement, ac_prelevements.id AS prelevement_id 			
				FROM ac_j_tissu_anticorps
				INNER JOIN ac_especes on ac_j_tissu_anticorps.espece = ac_especes.id
				INNER JOIN ac_organes on ac_j_tissu_anticorps.organe = ac_organes.id
				INNER JOIN ac_prelevements on ac_j_tissu_anticorps.prelevement = ac_prelevements.id
				WHERE ac_j_tissu_anticorps.id_anticorps=?";
        
        if($catalog){
            $sql .= " AND ac_j_tissu_anticorps.status=1";
        }

        //$sql = "select * from ac_j_tissu_anticorps where id_anticorps=?";
        $res = $this->runRequest($sql, array($id_anticorps));
        $tissuss = $res->fetchAll();
        $modelProtocol = new AcProtocol();

        for ($i = 0 ; $i < count($tissuss) ; $i++) {
            $proto = $modelProtocol->getProtocolsByRef($tissuss[$i]["ref_protocol"]);
            if(isset($proto[0])){
                $tissuss[$i]["id_protocol"] = $proto[0]["id"];
            }
            else{
                $tissuss[$i]["id_protocol"] = "";
            }
        }

        if ($catalog) {
            $tissuscp = array();
            foreach ($tissuss as $tissus) {

                // try to find the redondance
                $found = false;
                foreach ($tissuscp as $tcp) {
                    if ($tcp["espece_id"] == $tissus["espece_id"] && $tcp["prelevement_id"] == $tissus["prelevement_id"]) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $tissuscp[] = $tissus;
                }
            }
            return $tissuscp;
        }
        return $tissuss;
    }

    public function removeTissus($id) {
        $sql = "DELETE FROM ac_j_tissu_anticorps WHERE id_anticorps = ?";
        $this->runRequest($sql, array($id));
    }

    public function delete($id) {
        $sql = "DELETE FROM ac_j_tissu_anticorps WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

}
