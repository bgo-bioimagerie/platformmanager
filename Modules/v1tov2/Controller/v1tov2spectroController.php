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

require_once 'Modules/antibodies/Model/Anticorps.php';

class v1tov2temController extends Controller {

    public function indexAction() {

        // ---------- SETTINGS ----------
        $dsn_old = 'mysql:host=localhost;dbname=sygrrif2_spectro;charset=utf8';
        $login_old = "root";
        $pwd_old = "root";

        $pdo_old = new PDO($dsn_old, $login_old, $pwd_old, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));

        $id_space = 3;

        // ---------- IMPORT ----------
        // Rennes MRic
        $this->importUsers(); // onicolle -> doriann
        // resources
        echo "import resources <br/>";
        echo "fn 1 <br/>";
        $areasMap = $this->importAreas($pdo_old, $id_space);
        echo "fn 2 <br/>";
        $resourcesCategoriesMap = $this->importResourcesCategories($pdo_old, $id_space);
        echo "fn 3 <br/>";
        $resourcesMap = $this->importResources($pdo_old, $id_space, $resourcesCategoriesMap, $areasMap);

        // Booking
        echo "import booking <br/>";
        $this->importBkAccess($pdo_old, $resourcesMap);
        echo "fn 1 <br/>";
        $this->importBookingSettings($pdo_old, $id_space);
        echo "fn 3 <br/>";
        $this->importBookingCss($pdo_old, $areasMap);
        echo "fn 4 <br/>";
        $this->importCalQuantities($pdo_old, $resourcesMap);
        echo "fn 5 <br/>";
        $colorMap = $this->importColorCode($pdo_old, $id_space);
        echo "fn 6 <br/>";
        $this->importCalendarEntry($pdo_old, $resourcesMap, $colorMap);
    }

    public function importUsers(){
        
        $modelUser = new EcUser();
        $modelUser->add("Lavault", "MThé", 
                "angot", "", "marie-therese.lavault@univ-rennes1.fr",
                "4744", 1, 1, 
                "1", "", 
                "");
        
    }
    
    public function importAreas($pdo_old, $id_space) {
        $sql = "SELECT * FROM sy_areas";
        $result = $pdo_old->query($sql);
        $areas_old = $result->fetchAll();

        $modelArea = new ReArea();
        $areasMap = array();
        foreach ($areas_old as $ao) {
            $newID = $modelArea->set(0, $ao["name"], $ao["restricted"], $id_space);
            $areasMap[$ao["id"]] = $newID;
        }
        return $areasMap;
    }

    public function importResourcesCategories($pdo_old, $id_space) {
        $sql = "SELECT * FROM sy_resourcescategory";
        $result = $pdo_old->query($sql);
        $resCat_old = $result->fetchAll();

        $modelResCat = new ReCategory();
        $catMap = array();
        foreach ($resCat_old as $d) {

            $newID = $modelResCat->set(0, $d["name"], $id_space);
            $catMap[$d["id"]] = $newID;
        }
        return $catMap;
    }

    public function importResources($pdo_old, $id_space, $resourcesCategoriesMap, $areasMap) {
        $sql = "SELECT * FROM sy_resources";
        $result = $pdo_old->query($sql);
        $resources_old = $result->fetchAll();

        $modelResources = new ResourceInfo();
        $resourcesMap = array();
        foreach ($resources_old as $ro) {

            $name = $ro["name"];
            $brand = "";
            $type = 0;
            $description = $ro["description"];
            $long_description = "";
            $id_category = $resourcesCategoriesMap[$ro["category_id"]];
            //print_r($areasMap);
            //echo ' re name = ' . $ro["name"] . "<br/>";
            //echo ' re area = ' . $ro["area_id"] . "<br/>";
            $id_area = 0;
            if (isset($areasMap[$ro["area_id"]])) {
                $id_area = $areasMap[$ro["area_id"]];
            }
            $display_order = $ro["display_order"];
            $newID = $modelResources->set(0, $name, $brand, $type, $description, $long_description, $id_category, $id_area, $id_space, $display_order);
            $resourcesMap[$ro["id"]] = $newID;
        }
        return $resourcesMap;
    }

    public function importBkAccess($pdo_old, $resourcesMap) {
        $sql = "SELECT * FROM sy_resources";
        $result = $pdo_old->query($sql);
        $res_old = $result->fetchAll();

        $modelAccess = new BkAccess();
        foreach ($res_old as $d) {
            $id_resources = $resourcesMap[$d["id"]];
            $id_access = $d["accessibility_id"];
            $modelAccess->set($id_resources, $id_access);
        }
    }

    public function importBookingSettings($pdo_old, $id_space) {
        $sql = "SELECT * FROM sy_booking_settings";
        $result = $pdo_old->query($sql);
        $bookingsettings_old = $result->fetchAll();

        $modelBkS = new BkBookingSettings();
        foreach ($bookingsettings_old as $d) {
            $modelBkS->setEntry($d["tag_name"], $d["is_visible"], $d["is_tag_visible"], $d["display_order"], $d["font"], $id_space);
        }
    }

    public function importBookingCss($pdo_old, $areasMap) {
        $sql = "SELECT * FROM sy_bookingcss";
        $result = $pdo_old->query($sql);
        $bookingCSS_old = $result->fetchAll();

        $model = new BkBookingTableCSS();
        foreach ($bookingCSS_old as $d) {

            $id_area = $areasMap[$d["id_area"]];
            $model->setAreaCss($id_area, $d["header_background"], $d["header_color"], $d["header_font_size"], $d["resa_font_size"], $d["header_height"], $d["line_height"]);
        }
    }

    public function importCalQuantities($pdo_old, $resourcesMap) {
        $sql = "SELECT * FROM sy_resources_calendar";
        $result = $pdo_old->query($sql);
        $res_old = $result->fetchAll();

        $model = new BkCalQuantities();
        foreach ($res_old as $d) {
            if ($d["quantity_name"] != "") {
                $id_resource = 0;
                if (isset($resourcesMap[$d["id_resource"]])) {
                    $id_resource = $resourcesMap[$d["id_resource"]];
                }
                $name = $d["quantity_name"];
                $mandatory = true;
                $model->setCalQuantity(0, $id_resource, $name, $mandatory);
            }
        }
    }

    public function importColorCode($pdo_old, $id_space) {
        $sql = "SELECT * FROM sy_color_codes";
        $result = $pdo_old->query($sql);
        $color_old = $result->fetchAll();

        $model = new BkColorCode();
        $colorMap = array();
        foreach ($color_old as $d) {
            $d["text_color"] = "#000000";
            $newID = $model->addColorCode($d["name"], $d["color"], $d["text_color"], $id_space, $d["display_order"]);
            $colorMap[$d["id"]] = $newID;
        }
        return $colorMap;
    }

    public function importCalendarEntry($pdo_old, $resourcesMap, $colorMap) {
        $sql = "SELECT * FROM sy_calendar_entry";
        $result = $pdo_old->query($sql);
        $calentry_old = $result->fetchAll();

        $model = new BkCalendarEntry();
        foreach ($calentry_old as $d) {
            $start_time = $d["start_time"];
            $end_time = $d["end_time"];

            $resource_id = 0;
            if (isset($resourcesMap[$d["resource_id"]])) {
                $resource_id = $resourcesMap[$d["resource_id"]];
            }

            $booked_by_id = $this->getUserID($pdo_old, $d["booked_by_id"]);
            $recipient_id = $this->getUserID($pdo_old, $d["recipient_id"]);
            $responsible_id = $this->getUserID($pdo_old, $d["responsible_id"]);

            //$package_id = 0;
            //if(isset($usersMap[$d["responsible_id"]])){
            $package_id = $d["package_id"];
            //}

            $supplementaries = $d["supplementary"];
            $quantities = $d["quantity"];

            $last_update = $d["last_update"];
            $color_type_id = 1;
            if (isset($colorMap[$d["color_type_id"]])) {
                $color_type_id = $colorMap[$d["color_type_id"]];
            }
            $short_description = $d["short_description"];
            $full_description = $d["full_description"];

            $model->setEntry(0, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, $last_update, $color_type_id, $short_description, $full_description, $quantities, $supplementaries, $package_id, $responsible_id);
        }
    }

    protected function getUserID($pdo_old, $id_old) {
        $sql = "SELECT login FROM core_users WHERE id=" . $id_old;
        $resu = $pdo_old->query($sql);
        $login = $resu->fetch();
        $login = $login[0];
        //echo "login old = " . $login . "<br/>";
        
        if ($login == "onicolle"){
            $login = "doriann";
        }

        $modelUser = new EcUser();
        $id_user = $modelUser->getIdFromLogin($login);
        if ($id_user == "") {
            $id_user = 0;
        }
        return $id_user;
    }

}
