<?php

require_once 'Framework/Controller.php';

// ecosystem
require_once 'Modules/ecosystem/Model/EcUser.php';
require_once 'Modules/ecosystem/Model/EcUnit.php';
require_once 'Modules/ecosystem/Model/EcBelonging.php';
require_once 'Modules/ecosystem/Model/EcResponsible.php';

// resources
require_once 'Modules/resources/Model/ReArea.php';
require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Model/ReVisa.php';

// booking
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/booking/Model/BkAuthorization.php';
require_once 'Modules/booking/Model/BkBookingSettings.php';
require_once 'Modules/booking/Model/BkBookingTableCSS.php';
require_once 'Modules/booking/Model/BkCalQuantities.php';
require_once 'Modules/booking/Model/BkCalSupInfo.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/booking/Model/BkColorCode.php';
require_once 'Modules/booking/Model/BkNightWe.php';
require_once 'Modules/booking/Model/BkPrice.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/booking/Model/BkPackage.php';

// services
require_once 'Modules/services/Model/SeOrder.php';
require_once 'Modules/services/Model/SePrice.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/SeService.php';

require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';

require_once 'Modules/core/Model/CoreSpace.php';

class ImportauthorizationsController extends Controller {

    public function indexAction() {

        // ---------- SETTINGS ----------
        $dsn_old = 'mysql:host=localhost;dbname=sygrrif2_h2p2;charset=utf8';
        $login_old = "root";
        $pwd_old = "root";

        $pdo_old = new PDO($dsn_old, $login_old, $pwd_old, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

        $id_space = 2;

        echo "import importVisas <br/>";
        $visasMap = $this->importVisas($pdo_old);
        echo "import importAuthorizations <br/>";
        $this->importAuthorizations($pdo_old);

        echo "end <br/>";
    }

    public function importVisas($pdo_old) {
        $sql = "SELECT * FROM sy_visas";
        $result = $pdo_old->query($sql);
        $visas_old = $result->fetchAll();

        $modelVisas = new ReVisa();
        foreach ($visas_old as $d) {

            // get the resource category
            $id_cat = $this->getCategoryID($pdo_old, $d["id_resource_category"]);
            $instructor_status = $d["instructor_status"];

            // get the user new ID
            $id_instructor = $this->getUserID($pdo_old, $d["id_instructor"]);
            $modelVisas->importVisa($d['id'], $id_cat, $id_instructor, $instructor_status);
        }
    }

    protected function getCategoryID($pdo_old, $id_old) {
        $sql = "SELECT name FROM sy_resourcescategory WHERE id=" . $id_old;
        $resu = $pdo_old->query($sql);
        $catname = $resu->fetch();
        $catname = $catname[0];

        $modelCat = new ReCategory();
        $id_cat = $modelCat->getIdFromName($catname);
        if ($id_cat == "") {
            $id_cat = 0;
        }
        return $id_cat;
    }

    protected function getUserID($pdo_old, $id_old) {
        $sql = "SELECT login FROM core_users WHERE id=" . $id_old;
        $resu = $pdo_old->query($sql);
        $login = $resu->fetch();
        $login = $login[0];
        //echo "login old = " . $login . "<br/>";

        $modelUser = new EcUser();
        $id_user = $modelUser->getIdFromLogin($login);
        if ($id_user == "") {
            $id_user = 0;
        }
        return $id_user;
    }
    
    protected function getUnitID($pdo_old, $id_old){
        $sql = "SELECT name FROM core_units WHERE id=".$id_old;
        $resu = $pdo_old->query($sql);
        $unitName = $resu->fetch();
        $unitName = $unitName[0];
        
        $modelUnit = new EcUnit();
        $newId = $modelUnit->getUnitId($unitName);
        if ($newId == "") {
            $newId = 0;
        }
        return $newId;
    }

    public function importAuthorizations($pdo_old) {

        //print_r($visasMap);

        $sql = "SELECT * FROM sy_authorization";
        $result = $pdo_old->query($sql);
        $auth_old = $result->fetchAll();

        $modelAuth = new BkAuthorization();
        foreach ($auth_old as $d) {
            $date = $d["date"];

            // get user id
            $user_id = $this->getUserID($pdo_old, $d["user_id"]);
            $lab_id = $this->getUnitID($pdo_old, $d["lab_id"]);
            $visa_id = $d["visa_id"];
            $resource_id = $this->getCategoryID($pdo_old, $d["resource_id"]);
            
            $is_active = $d["is_active"];

            $modelAuth->setAuthorization(0, $date, $user_id, $lab_id, $visa_id, $resource_id, $is_active);
        }
    }

}
