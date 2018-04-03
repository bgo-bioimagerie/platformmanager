<?php

require_once 'Framework/Model.php';

require_once 'Modules/core/Model/CoreUser.php';

require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClClientUser.php';

require_once 'Modules/resources/Model/ResourceInfo.php';

class UsersPatch extends Model {

    public function __construct() {
        
    }

    public function patch() {


        $sqlcl = "SELECT * FROM cl_clients";
        $client_count = $this->runRequest($sqlcl)->rowCount();

        $sqlresp = "SELECT * FROM ec_j_user_responsible";
        $sqlresp_count = $this->runRequest($sqlresp)->rowCount();

        if ($sqlresp_count > 0 && $client_count == 0) {

            $this->removeEcosystemFromSpaceTools();
            $this->importBelonging();
            $this->copyResponsiblesToClients();
            $this->joinUsersToClients();
            $this->changeRespIdsToClientIdsInBookingAndServices(false);
            $this->copyEcUsersToUsers();
            $this->updateInvoicesRespIDs(false);
        }
        $this->copyPhones();
    }

    public function copyPhones(){
        
        $sql = "SELECT id_core, phone FROM users_info";
        $data = $this->runRequest($sql)->fetchAll();
        
        foreach ($data as $d){
            $sql0 = "SELECT phone FROM core_users WHERE id=?";
            $oldPhone = $this->runRequest($sql0, array($d["id_core"]))->fetch();
            
            //echo 'old phone = ' . $oldPhone . "<br/>";
            
            if ($oldPhone[0] == ""){
                $sql = "UPDATE core_users SET phone=? WHERE id=?";
                $this->runRequest($sql, array($d["phone"], $d["id_core"]));
            }
        }
        
    }
    
    public function removeEcosystemFromSpaceTools() {
        $sql = "DELETE FROM core_space_menus WHERE module=?";
        $this->runRequest($sql, array("ecosystem"));
    }

    public function importBelonging() {

        $modelSpaces = new CoreSpace();
        $spaces = $modelSpaces->getSpaces('id');

        foreach ($spaces as $space) {
            $sqlo = "SELECT * FROM ec_belongings WHERE id_space=?";
            $belongings = $this->runRequest($sqlo, array($space["id"]))->fetchAll();

            foreach ($belongings as $belonging) {
                $sqln = "INSERT INTO cl_pricings (id_space, name, color, type, display_order) VALUES (?,?,?,?,?)";
                $this->runRequest($sqln, array($space["id"], $belonging["name"], 
                    $belonging["color"], $belonging["type"], $belonging["display_order"]));
            }
        }
    }

    public function copyResponsiblesToClients() {

        $sql = "SELECT * FROM ec_users WHERE is_responsible=?";
        $ecusers = $this->runRequest($sql, array(1))->fetchAll();

        $modelClient = new ClClient();
        $modelPricing = new ClPricing();
        $modelSpaces = new CoreSpace();
        $modelUser = new CoreUser();
        $modelCoreUser = new CoreUser();
        $modelAddress = new ClAddress();

        $spaces = $modelSpaces->getSpaces('id');

        echo "resps number = " . count($ecusers) . "<br/>";
        foreach ($ecusers as $ecuser) {

            $name = $modelUser->getUserFUllName($ecuser["id"]);
            $contact_name = $modelUser->getUserFUllName($ecuser["id"]);
            $phone = $ecuser["phone"];

            $coreUserInfo = $modelCoreUser->getUser($ecuser["id"]);
            $email = $coreUserInfo["email"];

            foreach ($spaces as $space) {
                $id_belonging = $this->getEcUnitBelonging($ecuser["id_unit"], $space["id"]);
                $belName = $this->getEcBelongingName($id_belonging);

                $pricing = $modelPricing->getIdFromName($belName, $space["id"]);

                $institution = $this->getEcUnitName($ecuser["id_unit"], false);
                $address = $this->getEcUnitAddress($ecuser["id_unit"]);

                $id_address = $modelAddress->set(0, $institution, "", "", $address, "", "", "");

                $id_client = $modelClient->set(0, $space["id"], $name, $contact_name, $phone, $email, $pricing, 0);
                $modelClient->setAddressDelivery($id_client, $id_address);
                $modelClient->setAddressInvoice($id_client, $id_address);
            }
        }
    }

    protected function getEcUnitAddress($id) {
        $sql = "select address from ec_units where id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            return "";
        }
    }

    protected function getEcBelongingName($id) {
        $sql = "select name from ec_belongings where id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];  // get the first line of the result
        } else {
            return "";
        }
    }

    protected function getEcUnitBelonging($id_unit, $id_space) {
        $sql = "SELECT id_belonging FROM ec_j_belonging_units WHERE id_unit=? AND id_space=?";
        $req = $this->runRequest($sql, array($id_unit, $id_space));
        if ($req->rowCount() == 1) {
            $tmp = $req->fetch();
            return $tmp[0];
        } else {
            return 0;
        }
    }

    protected function getEcUnitName($id, $warning = true) {
        $sql = "SELECT name FROM ec_units WHERE id=?";
        $req = $this->runRequest($sql, array($id));
        if ($req->rowCount() > 0) {
            $tmp = $req->fetch();
            return $tmp[0];
        }
        if ($warning) {
            echo 'Warning unit name not found for ' . $id . "<br/>";
        }
        return "";
    }

    public function joinUsersToClients() {

        $modelCoreUsers = new CoreUser();
        $modelClientUSer = new ClClientUser();
        $modelSpaces = new CoreSpace();

        $users = $modelCoreUsers->getAll();
        $spaces = $modelSpaces->getSpaces('id');

        foreach ($users as $user) {
            $responsibles = $this->getEcResponsibles($user['id']);
            foreach ($responsibles as $resp) {
                foreach ($spaces as $space) {
                    $id_client = $this->getResponsibleNewClientId($resp["fullname"], $space["id"]);
                    $modelClientUSer->set($id_client, $user['id']);
                }
            }
        }

        echo 'joint user to client done -------------------------------- <br/>';
    }

    protected function getEcResponsibles($id_user) {
        $sql = "SELECT id_resp FROM ec_j_user_responsible WHERE id_user = ?";
        $req = $this->runRequest($sql, array($id_user));
        $userr = $req->fetchAll();

        $modelUser = new CoreUser();

        for ($i = 0; $i < count($userr); $i++) {
            $userr[$i]["id"] = $userr[$i]["id_resp"];
            $userr[$i]["fullname"] = $modelUser->getUserFUllName($userr[$i]["id_resp"]);
        }
        return $userr;
    }

    protected function getResponsibleNewClientId($resp_fullname, $id_space) {

        if ($resp_fullname != "" && $id_space != 0) {

            $sql = "SELECT * FROM cl_clients WHERE name=? AND id_space=?";

            $req = $this->runRequest($sql, array($resp_fullname, $id_space));
            if ($req->rowCount() > 0) {
                $tmp = $req->fetch();
                return $tmp[0];
            }
            echo "Warning getResponsibleNewClientId: no id for " . $resp_fullname . " and space " . $id_space . "<br/>";
            return 0;
        }
        return 0;
    }

    protected function changeRespIdsToClientIdsInBookingAndServices($warning = true) {

        $modelUser = new CoreUser();
        $modelResource = new ResourceInfo();

        // booking
        $sql = "SELECT * FROM bk_calendar_entry";
        $reservations = $this->runRequest($sql)->fetchAll();
        foreach ($reservations as $res) {
            $resp_name = $modelUser->getUserFUllName($res["responsible_id"]);
            $resource = $modelResource->get($res["resource_id"]);

            if ($resource) {
                $idClient = $this->getResponsibleNewClientId($resp_name, $resource["id_space"]);

                $sql = "UPDATE bk_calendar_entry SET responsible_id=? WHERE id=?";
                $this->runRequest($sql, array($idClient, $res["id"]));
            } else {
                if ($warning) {
                    echo "resource info not found for resource_id=" . $res["resource_id"] . "<br/>";
                }
            }
        }

        // order
        $sqlo = "SELECT * FROM se_order";
        $orders = $this->runRequest($sqlo)->fetchAll();
        foreach ($orders as $order) {
            $resp_name = $modelUser->getUserFUllName($order["id_resp"]);
            $idClient = $this->getResponsibleNewClientId($resp_name, $order["id_space"]);

            $sql = "UPDATE se_order SET id_resp=? WHERE id=?";
            $this->runRequest($sql, array($idClient, $order["id"]));
        }

        // project
        $sqlp = "SELECT * FROM se_project";
        $projects = $this->runRequest($sqlp)->fetchAll();
        foreach ($projects as $project) {
            $resp_name = $modelUser->getUserFUllName($project["id_resp"]);
            $idClient = $this->getResponsibleNewClientId($resp_name, $project["id_space"]);

            $sql = "UPDATE se_project SET id_resp=? WHERE id=?";
            $this->runRequest($sql, array($idClient, $project["id"]));
        }

        echo 'changeRespIdsToClientIdsInBookingAndServices done ---------<br/>';
    }

    protected function copyEcUsersToUsers() {

        $modelUserInfo = new UsersInfo();

        $sql = "SELECT * FROM ec_users";
        $ecusers = $this->runRequest($sql)->fetchAll();
        foreach ($ecusers as $ecuser) {

            $unit = $this->getEcUnitName($ecuser["id_unit"]);
            $modelUserInfo->set($ecuser["id"], $ecuser["phone"], $unit);
        }

        echo 'copyEcUsersToUsers done -----------------------------------<br/>';
    }

    protected function updateInvoicesRespIDs($warning = false){
        
        // invoices
        $sql = "SELECT * FROM in_invoice";
        $invoices = $this->runRequest($sql)->fetchAll();
        $modelUser = new CoreUser();
        foreach($invoices as $invoice){
            $id_space = $invoice["id_space"];
            $resp_fullname = $modelUser->getUserFUllName( $invoice["id_responsible"] );
            $newRespID = $this->getResponsibleNewClientId($resp_fullname, $id_space);
            
            $sql = "UPDATE in_invoice SET id_responsible=? WHERE id=?";
            $this->runRequest($sql, array($newRespID, $invoice["id"]));
        }
        
        $sqlnwes = "SELECT * FROM bk_nightwe";
        $nwes = $this->runRequest($sqlnwes)->fetchAll();
        foreach($nwes as $nwe){
            $id_space = $nwe["id_space"];
            $belongingName = $this->getEcBelongingName($nwe["id_belonging"]);
            $newBelID = $this->getNewBelongingID($belongingName, $id_space);
            
            $sql = "UPDATE bk_nightwe SET id_belonging=? WHERE id=?";
            $this->runRequest($sql, array($newBelID, $nwe["id"]));
            
        }
        
        // change bk_prices belongings
        $sqlbk = "SELECT * FROM bk_prices";
        $bk_prices = $this->runRequest($sqlbk)->fetchAll();
        foreach($bk_prices as $price){
            $belongingName = $this->getEcBelongingName($price["id_belonging"]);
            
            // get id_space
            $sql = "SELECT id_space FROM re_info WHERE id=?";
            $id_space = $this->runRequest($sql, array($price["id_resource"]))->fetch();
            
            $newBelID = $this->getNewBelongingID($belongingName, $id_space[0]);
            
            $sql2 = "UPDATE bk_prices SET id_belonging=? WHERE id=?";
            $this->runRequest($sql2, array($newBelID, $price["id"]));
        }
        
        // change se_prices belongings
        $sqlse = "SELECT * FROM se_prices";
        $se_prices = $this->runRequest($sqlse)->fetchAll();
        foreach($se_prices as $price){
            $belongingName = $this->getEcBelongingName($price["id_belonging"]);
            
            // get id_space
            $sql = "SELECT id_space FROM se_services WHERE id=?";
            $id_space = $this->runRequest($sql, array($price["id_service"]))->fetch();
            
            $newBelID = $this->getNewBelongingID($belongingName, $id_space[0]);
            
            $sql2 = "UPDATE se_prices SET id_belonging=? WHERE id=?";
            $this->runRequest($sql2, array($newBelID, $price["id"]));
        }
        
    }
    
    protected function getNewBelongingID($name, $id_space){
        $sql = "SELECT id FROM cl_pricings WHERE name=? AND id_space=?";
        $req = $this->runRequest($sql, array($name, $id_space));
        if ($req->rowCount() > 0){
            $tmp = $req->fetch();
            return $tmp[0];
        }
        return 0;
    }
    
}
