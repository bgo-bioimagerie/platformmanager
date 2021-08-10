<?php

require_once 'Framework/Model.php';

require_once 'Modules/antibodies/Model/Kit.php';
require_once 'Modules/antibodies/Model/Proto.php';
require_once 'Modules/antibodies/Model/Fixative.php';
require_once 'Modules/antibodies/Model/AcOption.php';
require_once 'Modules/antibodies/Model/Enzyme.php';
require_once 'Modules/antibodies/Model/Dem.php';
require_once 'Modules/antibodies/Model/Aciinc.php';
require_once 'Modules/antibodies/Model/Linker.php';
require_once 'Modules/antibodies/Model/Inc.php';
require_once 'Modules/antibodies/Model/Acii.php';

/**
 * Class defining the Isotype model
 *
 * @author Sylvain Prigent
 */
class AcProtocol extends Model {

    /**
     * Create the protocols table
     * 
     * @return PDOStatement
     */
    public function createTable() {

        $sql = "CREATE TABLE IF NOT EXISTS `ac_protocol` (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`kit` int(11) NOT NULL,
				`no_proto` varchar(11) NOT NULL,
				`proto` int(11) NOT NULL,
				`fixative` int(11) NOT NULL,
				`option_` int(11) NOT NULL,
				`enzyme` int(11) NOT NULL,
				`dem` int(11) NOT NULL,
				`acl_inc` int(11) NOT NULL,
				`linker` int(11) NOT NULL,
				`inc` int(11) NOT NULL,
				`acll` int(11) NOT NULL,
				`inc2` int(11) NOT NULL,
				`associe` int(1) NOT NULL,
                `id_space` int(11) NOT NULL,
				PRIMARY KEY (`id`)
				);";

        $pdo = $this->runRequest($sql);
        return $pdo;
    }

    public function addManualProtocol($id_space) {

        $kit = "Manuel";
        $no_proto = 0;
        $proto = "";
        $fixative = "";
        $option = "";
        $enzyme = "";
        $dem = "";
        $acl_inc = "";
        $linker = "";
        $inc = "";
        $acll = "";
        $inc2 = "";
        $associe = "";
        $sql = "insert into ac_protocol(kit, no_proto, proto, fixative, option_, enzyme, dem, acl_inc, linker, inc, acll, inc2, associe, id_space)"
                . " values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $this->runRequest($sql, array($kit, $no_proto, $proto, $fixative, $option, $enzyme, $dem, $acl_inc, $linker, $inc, $acll, $inc2, $associe, $id_space));
    }

    public function getBySpace($id_space) {
        $sql = "select * from ac_protocol WHERE id_space=? AND deleted=0";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    public function getForList($id_space) {
        $data = $this->getBySpace($id_space);
        $names = array();
        $ids = array();
        foreach ($data as $d) {
            $names[] = $d["no_proto"];
            $ids[] = $d["no_proto"];
        }
        return array("names" => $names, "ids" => $ids);
    }
    
    /**
     * get protocols informations
     *
     * @param string $sortentry Entry that is used to sort the isotypes
     * @return multitype: array
     */
    public function getProtocols($id_space, $sortentry = 'id') {

        $sql = "select * from ac_protocol WHERE id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    public function getProtocols2($id_space, $sortentry = 'id') {

        $sql = "select * from ac_protocol WHERE id_space=? AND deleted=0 order by " . $sortentry . " ASC;";
        $req = $this->runRequest($sql, array($id_space));
        $protos = $req->fetchAll();

        return $this->getAssociateAnticorpsInfo($id_space, $protos);
    }

    private function getAssociateAnticorpsInfo($id_space, $protos) {
        for ($i = 0; $i < count($protos); $i++) {

            // no to names
            $model = new Kit();
            $protos[$i]["kit"] = $model->getNameFromId($id_space, $protos[$i]["kit"]);

            $model = new Proto();
            $protos[$i]["proto"] = $model->getNameFromId($id_space, $protos[$i]["proto"]);

            $model = new Fixative();
            $protos[$i]["fixative"] = $model->getNameFromId($id_space, $protos[$i]["fixative"]);

            $model = new AcOption();
            $protos[$i]["option_"] = $model->getNameFromId($id_space, $protos[$i]["option_"]);

            $model = new Enzyme();
            $protos[$i]["enzyme"] = $model->getNameFromId($id_space, $protos[$i]["enzyme"]);

            $model = new Dem();
            $protos[$i]["dem"] = $model->getNameFromId($id_space, $protos[$i]["dem"]);

            $model = new Aciinc();
            $protos[$i]["acl_inc"] = $model->getNameFromId($id_space, $protos[$i]["acl_inc"]);

            $model = new Linker();
            $protos[$i]["linker"] = $model->getNameFromId($id_space, $protos[$i]["linker"]);

            $model = new Inc();
            $protos[$i]["inc"] = $model->getNameFromId($id_space ,$protos[$i]["inc"]);

            $model = new Acii();
            $protos[$i]["acll"] = $model->getNameFromId($id_space, $protos[$i]["acll"]);

            $model = new Inc();
            $protos[$i]["inc2"] = $model->getNameFromId($id_space, $protos[$i]["inc2"]);


            if ($protos[$i]["associe"] == 1) {

                $sql = "select id_anticorps from ac_j_tissu_anticorps where ref_protocol=? AND id_space=? AND deleted=0";
                $req = $this->runRequest($sql, array($protos[$i]["no_proto"], $id_space));
                $ac = $req->fetchAll();

                //print("ref protocol = " + $protos[$i]["id"] + "<br />");
                //print_r($ac);

                if ($req->rowCount() > 0) {

                    $sql = "select nom, no_h2p2 from ac_anticorps where id=?";
                    $req = $this->runRequest($sql, array($ac[0]["id_anticorps"]));
                    $acInfo = $req->fetch();

                    $protos[$i]["anticorps"] = $acInfo["nom"];
                    $protos[$i]["no_h2p2"] = $acInfo["no_h2p2"];
                } else {
                    $protos[$i]["anticorps"] = "not found";
                    $protos[$i]["no_h2p2"] = "not found";
                }
            } else {
                $protos[$i]["anticorps"] = "general";
                $protos[$i]["no_h2p2"] = 0;
            }
        }
        return $protos;
    }

    public function getProtocolsByRef($id_space, $protocolRef) {
        $sql = "select * from ac_protocol where no_proto=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($protocolRef, $id_space));
        $protos = $req->fetchAll();

        return $this->getAssociateAnticorpsInfo($id_space, $protos);
    }

    public function getProtocolsByAnticorps($id_space, $anticorpsId) {

        $sql = "SELECT * FROM ac_protocol WHERE no_proto IN (SELECT ref_protocol		 			
				FROM ac_j_tissu_anticorps
				WHERE id_anticorps=? AND id_space=? AND deleted=0)";
        $req = $this->runRequest($sql, array($anticorpsId, $id_space));
        $protos = $req->fetchAll();

        return $this->getAssociateAnticorpsInfo($id_space, $protos);
    }

    public function getProtocolsNo($id_space) {

        $sql = "select id, no_proto from ac_protocol WHERE id_space=? AND deleted=0 order by no_proto ASC;";
        $user = $this->runRequest($sql, array($id_space));
        return $user->fetchAll();
    }

    /**
     * get the informations of an protocol
     *
     * @param int $id Id of the isotype to query
     * @throws Exception id the isotype is not found
     * @return mixed array
     */
    public function getProtocol($id_space, $id) {
        $sql = "select * from ac_protocol where id=? AND id_space=? AND deleted=0";
        $unit = $this->runRequest($sql, array($id, $id_space));
        if ($unit->rowCount() == 1){
            return $unit->fetch();
        }
        else{
            throw new PfmException("Cannot find the protocol using the given id", 404);
        }
    }

    /**
     * add a protocol to the table
     *
     * 
     */
    public function addProtocol($id_space, $kit, $no_proto, $proto, $fixative, $option, $enzyme, $dem, $acl_inc, $linker, $inc, $acll, $inc2, $associe = "") {

        //, `no_proto`, proto, fixative, option, enzyme, dem, `acl_inc`, linker, inc, acll
        // ,?,?,?,?,?,?,?,?,?,?,?
        $sql = "insert into ac_protocol(id_space, kit, no_proto, proto, fixative, option_, enzyme, dem, acl_inc, linker, inc, acll, inc2, associe)"
                . " values(?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $this->runRequest($sql, array($id_space, $kit, $no_proto, $proto, $fixative, $option, $enzyme, $dem, $acl_inc, $linker, $inc, $acll, $inc2, $associe));
    }

    public function importProtocol($id, $id_space, $kit, $no_proto, $proto, $fixative, $option, $enzyme, $dem, $acl_inc, $linker, $inc, $acll, $inc2, $associe = "") {

        //, `no_proto`, proto, fixative, option, enzyme, dem, `acl_inc`, linker, inc, acll
        // ,?,?,?,?,?,?,?,?,?,?,?
        $sql = "insert into ac_protocol(id, id_space, kit, no_proto, proto, fixative, option_, enzyme, dem, acl_inc, linker, inc, acll, inc2, associe)"
                . " values(?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

        $this->runRequest($sql, array($id, $id_space, $kit, $no_proto, $proto, $fixative, $option, $enzyme, $dem, $acl_inc, $linker, $inc, $acll, $inc2, $associe));
    }

    /**
     * update the information of a isotype
     *
     * @param int $id Id of the isotype to update
     * @param string $name New name of the isotype
     */
    public function editProtocol($id, $id_space, $kit, $no_proto, $proto, $fixative, $option, $enzyme, $dem, $acl_inc, $linker, $inc, $acll, $inc2, $associe = "") {

        $sql = "update ac_protocol set kit=?, no_proto=?, proto=?, fixative=?, option_=?, enzyme=?, dem=?, acl_inc=?, linker=?, inc=?, acll=?, inc2=?, associe=? where id=? AND id_space=? AND deleted=0";
        $this->runRequest($sql, array($kit, $no_proto, $proto, $fixative, $option, $enzyme, $dem, $acl_inc, $linker, $inc, $acll, $inc2, $associe, $id, $id_space));
    }

    public function delete($id_space, $id) {
        $sql = "UPDATE ac_protocol SET deleted=1,deleted_at=NOW() WHERE id=? AND id_space=?";
        //$sql = "DELETE FROM ac_protocol WHERE id = ?";
        $this->runRequest($sql, array($id));
    }

    public function isProtocolOfID($id_space, $id) {
        $sql = "select * from ac_protocol where id=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($id, $id_space));
        if ($req->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function existsProtocol($id_space, $kit, $no_proto) {
        $sql = "select * from ac_protocol where kit=? and no_proto=? AND id_space=? AND deleted=0";
        $req = $this->runRequest($sql, array($kit, $no_proto, $id_space));
        if ($req->rowCount() > 0) {
            return true;
        } else {
            return false;
        }
    }

}
