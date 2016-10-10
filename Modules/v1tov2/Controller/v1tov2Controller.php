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


class v1tov2Controller extends Controller {
    
    public function indexAction() {
        
        // ---------- SETTINGS ----------
        $dsn_old = 'mysql:host=localhost;dbname=sygrrif2_h2p2;charset=utf8';
	$login_old = "root";
	$pwd_old = "root";
		
	$pdo_old = new PDO($dsn_old, $login_old, $pwd_old,
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
        
        $id_space = 1;
        $importProjects = true;
        $importSupplies = false;
        
        
        // ---------- IMPORT ----------
        // Rennes MRic
        /*
        $belongingMap[1] = 1; // --
        $belongingMap[2] = 2; // Biosit
        $belongingMap[3] = 3; // UR1
        $belongingMap[4] = 4; // Public
        $belongingMap[5] = 5; // Privé
        */
        
        // Nantes
        $belongingMap = $this->importBelongings($pdo_old);
        //print_r($belongingMap);
        
        // ecosystem
        echo "import ecosystem <br/>";
        echo "   import units <br/>";
        $unitsMap = $this->importUnits($pdo_old, $belongingMap);
        //print_r($unitsMap);
        echo "   import users <br/>";
        $usersMap = $this->importUsers($pdo_old, $unitsMap);
        echo "   import responsibles <br/>";
        $this->importResponsibles($pdo_old, $usersMap);
        
        // resources
        echo "import resources <br/>";
        $areasMap = $this->importAreas($pdo_old, $id_space);
        $resourcesCategoriesMap = $this->importResourcesCategories($pdo_old, $id_space);
        $resourcesMap = $this->importResources($pdo_old, $id_space, $resourcesCategoriesMap, $areasMap);
        $visasMap = $this->importVisas($pdo_old, $resourcesCategoriesMap, $usersMap);
        
        // Booking
        echo "import booking <br/>"; 
        $this->importBkAccess($pdo_old, $resourcesMap);
        echo "fn 1 <br/>";
        $this->importAuthorizations($pdo_old, $usersMap, $unitsMap, $visasMap, $resourcesMap);
        echo "fn 2 <br/>";
        $this->importBookingSettings($pdo_old, $id_space);
        echo "fn 3 <br/>";
        $this->importBookingCss($pdo_old, $areasMap);
        echo "fn 4 <br/>";
        $this->importCalQuantities($pdo_old, $resourcesMap);
        echo "fn 5 <br/>";
        $colorMap = $this->importColorCode($pdo_old, $id_space);
        echo "fn 6 <br/>";
        $this->importCalendarEntry($pdo_old, $resourcesMap, $usersMap, $colorMap);
        echo "fn 7 <br/>";
        $this->importNightWe($pdo_old, $id_space, $belongingMap);
        echo "fn 8 <br/>";
        $packagesMap = $this->importPackage($pdo_old, $resourcesMap);
        echo "fn 9 <br/>";
        $this->importBookingPrices($pdo_old);
        echo "fn 10 <br/>";
        $this->importScheduling($pdo_old, $areasMap);
        
        // sprojects
        $serviceTypeMap[1] = 1;
        $serviceTypeMap[2] = 2;
        $serviceTypeMap[3] = 3;
        $serviceTypeMap[4] = 4;
        
        if($importProjects){
            echo "import sprojects <br/>";
            $servicesMap = $this->importProjectServices($pdo_old, $id_space, $serviceTypeMap);
            //echo "fn 1 <br/>";
            $this->importProjectPrices($pdo_old, $servicesMap, $belongingMap);
            //echo "fn 2 <br/>";
            $mapProjectInvoices = $this->importProjectInvoices($pdo_old, $id_space, $unitsMap, $usersMap);
            //echo "fn 3 <br/>";
            $this->importProject($pdo_old, $id_space, $usersMap, $mapProjectInvoices, $servicesMap);
        }
        
        // supplies
        if ($importSupplies){
            echo "import supplies <br/>";
            $servicesOrderMap = $this->importOrderServices($pdo_old, $id_space, $serviceTypeMap);
            echo "fn 1 <br/>";
            $this->importOrderPrices($pdo_old, $servicesOrderMap, $belongingMap);
            echo "fn 2 <br/>";
            $mapOrderInvoices = $this->importOrderInvoices($pdo_old, $id_space, $unitsMap, $usersMap);
            echo "fn 3 <br/>";
            $this->importOrder($pdo_old, $id_space, $usersMap, $servicesOrderMap);
        }
        echo "end <br/>";
    }
    
    protected function importBelongings($pdo_old){
        $sql = "SELECT * FROM core_belongings";
        $result = $pdo_old->query($sql);
	$bel_old = $result->fetchAll();
        
        $model = new EcBelonging();
        $unitMap = array();
        foreach($bel_old as $uo){
            $name = $uo["name"];
            $color = $uo["color"];
            $type = $uo["type"];
            $newID = $model->add($name, $color, $type);
            $unitMap[$uo["id"]] = $newID;
        }
        
        return $unitMap;
    }
    
    protected function importUnits($pdo_old, $belongingMap){
        $sql = "SELECT * FROM core_units";
        $result = $pdo_old->query($sql);
	$units_old = $result->fetchAll();
        
        $modelUnit = new EcUnit();
        $unitMap = array();
        foreach($units_old as $uo){
            $name = $uo["name"];
            $address = $uo["address"];
            $id_belonging = $belongingMap[$uo["id_belonging"]];
            $newID = $modelUnit->importUnit2($this->unitNameMapping($name), $address, $id_belonging);
            $unitMap[$uo["id"]] = $newID;
        }
        
        return $unitMap;
    }
    
    protected function unitNameMapping($name){
        
        $map["name mric"] = "name h2p2";
        
        foreach($map as $key => $value){
            if ($name == $value){
                return $key;
            }
        }
        return $name;
        
    }
    
    protected function importUsers($pdo_old, $unitsMap){
        $sql = "SELECT * FROM core_users";
        $result = $pdo_old->query($sql);
	$users_old = $result->fetchAll();
        
        $modelCoreUser = new CoreUser();
        $modelEcUser = new EcUser();
        $userMap = array();
        foreach($users_old as $uo){
            $sqlR = "SELECT * FROM core_responsibles WHERE id_users=".$uo["id"];
            $result2 = $pdo_old->query($sqlR);
            $is_responsible = 0;
            if($result2->rowCount() == 1){
                $is_responsible = 1;
            }
            
            $login = $uo["login"];
            $pwd = $uo["pwd"];
            $name = $uo["name"];
            $firstname = $uo["firstname"];
            $email = $uo["email"];
            $status_id = 1;
            if ($uo["id_status"] > 3){
                $status_id = 2;
            }
            $date_end_contract = $uo["date_end_contract"];
            $is_active = $uo["is_active"];
            $source = $uo["source"];
            $userNewID = $modelCoreUser->importUser($login, $pwd, $name, $firstname, $email, $status_id, $date_end_contract, $is_active, $source);
            $userMap[$uo["id"]] = $userNewID;
            
            //echo "imported id = " . $userNewID . "<br/>";
            $phone = $uo["tel"];
            $unit = 1;
            if(isset($unitsMap[$uo["id_unit"]])){
                $unit = $unitsMap[$uo["id_unit"]];
            }
            
            $date_convention = $uo["date_convention"];
            $convention_url = "";//$uo["convention_url"];
            $modelEcUser->import2($userNewID, $phone, $unit, $date_convention, $is_responsible, $convention_url);
            //echo "done <br/>";
        }
        
        return $userMap;
    }
    
    public function importResponsibles($pdo_old, $usersMap){
        $sql = "SELECT * FROM core_j_user_responsible";
        $result = $pdo_old->query($sql);
	$j_old = $result->fetchAll();
        
        $model = new EcResponsible();
        foreach($j_old as $d){
            if (isset($usersMap[$d["id_user"]]) && isset($usersMap[$d["id_resp"]])){
                $model->import($usersMap[$d["id_user"]], $usersMap[$d["id_resp"]]);
            }
        }
    }

    public function importAreas($pdo_old, $id_space){
        $sql = "SELECT * FROM sy_areas";
        $result = $pdo_old->query($sql);
	$areas_old = $result->fetchAll();
        
        $modelArea = new ReArea();
        $areasMap = array();
        foreach($areas_old as $ao){
            $newID = $modelArea->set(0, $ao["name"], $id_space);
            $areasMap[$ao["id"]] = $newID; 
        }
        return $areasMap;
    }
    
    public function importResourcesCategories($pdo_old, $id_space){
        $sql = "SELECT * FROM sy_resourcescategory";
        $result = $pdo_old->query($sql);
	$resCat_old = $result->fetchAll();
        
        $modelResCat = new ReCategory();
        $catMap = array();
        foreach($resCat_old as $d){
            
            $newID = $modelResCat->set(0, $d["name"], $id_space);
            $catMap[$d["id"]] = $newID; 
        }
        return $catMap;
    }
    
    public function importResources($pdo_old, $id_space, $resourcesCategoriesMap, $areasMap){
        $sql = "SELECT * FROM sy_resources";
        $result = $pdo_old->query($sql);
	$resources_old = $result->fetchAll();
        
        $modelResources = new ResourceInfo();
        $resourcesMap = array();
        foreach($resources_old as $ro){
            
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
            if (isset($areasMap[$ro["area_id"]])){
                $id_area = $areasMap[$ro["area_id"]];
            }
            $display_order = $ro["display_order"];
            $newID = $modelResources->set(0, $name, $brand, $type, $description, $long_description, $id_category, 
                $id_area, $id_space, $display_order);
            $resourcesMap[$ro["id"]] = $newID;
        }
        return $resourcesMap;
        
    }
    
    public function importVisas($pdo_old, $resourcesCategoryMap, $usersMap){
        $sql = "SELECT * FROM sy_visas";
        $result = $pdo_old->query($sql);
	$visas_old = $result->fetchAll();
        
        $modelVisas = new ReVisa();
        $visasMap = array();
        foreach($visas_old as $d){
            $id_resource_category = 0;
            if (isset($resourcesCategoryMap[$d["id_resource_category"]])){
                $id_resource_category = $resourcesCategoryMap[$d["id_resource_category"]];
            }
            $id_instructor = $usersMap[$d["id_instructor"]]; 
            $instructor_status = $d["instructor_status"]; 
            $id_new = $modelVisas->setVisas(0, $id_resource_category, $id_instructor, $instructor_status);
            $visasMap[$d["id"]] = $id_new;
        }
        return $visasMap;
    }
    
    public function importBkAccess($pdo_old, $resourcesMap){
        $sql = "SELECT * FROM sy_resources";
        $result = $pdo_old->query($sql);
	$res_old = $result->fetchAll();
        
        $modelAccess = new BkAccess();
        foreach($res_old as $d){
            $id_resources = $resourcesMap[$d["id"]];
            $id_access = $d["accessibility_id"];
            $modelAccess->set($id_resources, $id_access);
        }
    }
    
    public function importAuthorizations($pdo_old, $usersMap, $unitsMap, $visasMap, $resourcesMap){
        
        //print_r($visasMap);
        
        $sql = "SELECT * FROM sy_authorization";
        $result = $pdo_old->query($sql);
	$auth_old = $result->fetchAll();
        
        $modelAuth = new BkAuthorization();
        foreach ($auth_old as $d){
            $date = $d["date"];
            $user_id = 0;
            if (isset($usersMap[$d["user_id"]])){
                $user_id = $usersMap[$d["user_id"]];
            }
            $lab_id = 0;
            if( isset($unitsMap[$d["lab_id"]])){
                $lab_id = $unitsMap[$d["lab_id"]];
            }
            $visa_id = 0;
            if(isset($visasMap[$d["visa_id"]])){
                $visa_id = $visasMap[$d["visa_id"]];
            }
            $resource_id = 0;
            if(isset($resourcesMap[$d["resource_id"]])){
                $resource_id = $resourcesMap[$d["resource_id"]];
            }
            $is_active = $d["is_active"];
            
            $modelAuth->setAuthorization(0, $date, $user_id, $lab_id, $visa_id, $resource_id, $is_active);
        }
    }
    
    public function importBookingSettings($pdo_old, $id_space){
        $sql = "SELECT * FROM sy_booking_settings";
        $result = $pdo_old->query($sql);
	$bookingsettings_old = $result->fetchAll();
        
        $modelBkS = new BkBookingSettings();
        foreach($bookingsettings_old as $d){
            $modelBkS->setEntry($d["tag_name"], $d["is_visible"], $d["is_tag_visible"], $d["display_order"], $d["font"], $id_space);
        }
    }
    
    public function importBookingCss($pdo_old, $areasMap){
        $sql = "SELECT * FROM sy_bookingcss";
        $result = $pdo_old->query($sql);
	$bookingCSS_old = $result->fetchAll();
        
        $model = new BkBookingTableCSS();
        foreach($bookingCSS_old as $d){
            
            $id_area = $areasMap[$d["id_area"]];
            $model->setAreaCss($id_area, $d["header_background"], $d["header_color"], $d["header_font_size"], 
                    $d["resa_font_size"], $d["header_height"], $d["line_height"]);
        }
    }
    
    public function importCalQuantities($pdo_old, $resourcesMap){
        $sql = "SELECT * FROM sy_resources_calendar";
        $result = $pdo_old->query($sql);
	$res_old = $result->fetchAll();
        
        $model = new BkCalQuantities();
        foreach($res_old as $d){
            if ($d["quantity_name"] != ""){
                $id_resource = 0;
                if (isset($resourcesMap[$d["id_resource"]])){
                    $id_resource = $resourcesMap[$d["id_resource"]];
                }
                $name = $d["quantity_name"];
                $mandatory = true;
                $model->setCalQuantity(0, $id_resource, $name, $mandatory);
            }
        }
    }
    
    public function importColorCode($pdo_old, $id_space){
        $sql = "SELECT * FROM sy_color_codes";
        $result = $pdo_old->query($sql);
	$color_old = $result->fetchAll(); 
        
        $model = new BkColorCode();
        $colorMap = array();
        foreach ($color_old as $d){
            $d["text_color"] = "#000000";
            $newID = $model->addColorCode($d["name"], $d["color"], $d["text_color"], $id_space, $d["display_order"]);
            $colorMap[$d["id"]] = $newID;
        }
        return $colorMap;
    }
    
    public function importCalendarEntry($pdo_old, $resourcesMap, $usersMap, $colorMap){
        $sql = "SELECT * FROM sy_calendar_entry";
        $result = $pdo_old->query($sql);
	$calentry_old = $result->fetchAll();
        
        $model = new BkCalendarEntry();
        foreach($calentry_old as $d){
            $start_time = $d["start_time"];
            $end_time = $d["end_time"];
            
            $resource_id = 0;
            if(isset($resourcesMap[$d["resource_id"]])){
                $resource_id = $resourcesMap[$d["resource_id"]];
            }
            
            $booked_by_id = 0;
            if(isset($usersMap[$d["booked_by_id"]])){
                $booked_by_id = $usersMap[$d["booked_by_id"]];
            }
            $recipient_id = 0;
            if(isset($usersMap[$d["recipient_id"]])){
                $recipient_id = $usersMap[$d["recipient_id"]];
            }
            $responsible_id = 0;
            if(isset($usersMap[$d["responsible_id"]])){
                $responsible_id = $usersMap[$d["responsible_id"]];
            }
            
            //$package_id = 0;
            //if(isset($usersMap[$d["responsible_id"]])){
                $package_id = $d["package_id"];
            //}
                
            $supplementaries = $d["supplementary"];
            $quantities = $d["quantity"];
            
            $last_update = $d["last_update"];
            $color_type_id = 1;
            if(isset($colorMap[$d["color_type_id"]])){
                $color_type_id = $colorMap[$d["color_type_id"]];
            }
            $short_description = $d["short_description"];
            $full_description = $d["full_description"];
            
            $model->setEntry(0, $start_time, $end_time, $resource_id, $booked_by_id, $recipient_id, 
                    $last_update, $color_type_id, $short_description, $full_description, 
                    $quantities, $supplementaries, $package_id, $responsible_id);
            
        }
    }
    
    public function importNightWe($pdo_old, $id_space, $belongingMap){
        $sql = "SELECT * FROM sy_pricing";
        $result = $pdo_old->query($sql);
	$pricing_old = $result->fetchAll();
        
        $model = new BkNightWE();
        foreach ($pricing_old as $d){
            
            $model->setPricing($belongingMap[$d["id"]], $id_space, $d["tarif_unique"], $d["tarif_night"], $d["night_start"], $d["night_end"], $d["tarif_we"], $d["choice_we"]);
        }
    }
    
    public function importPackage($pdo_old, $resourcesMap){
        $sql = "SELECT * FROM sy_packages";
        $result = $pdo_old->query($sql);
	$package_old = $result->fetchAll();
        
        $model = new BkPackage();
        $packagesMap = array();
        foreach($package_old as $d){
            
            /// chage id if several platform import 
            $idResource = 1;
            if(isset($resourcesMap[$d["id_resource"]])){
                $idResource = $resourcesMap[$d["id_resource"]];
            }
            
            $model->setPackage($d["id_package"], $idResource, $d["name"], $d["duration"]);
            $packagesMap[$d["id_package"]] = $d["id_package"];
        }
        return $packagesMap;
    }
    
    public function importBookingPrices($pdo_old){
        $sql = "SELECT * FROM sy_j_resource_pricing";
        $result = $pdo_old->query($sql);
	$prices_old = $result->fetchAll();
        
        $modelPrices = new BkPrice();
        foreach($prices_old as $d){
            
            $id_resource = $d["id_resource"];
            $id_belongings = $d["id_pricing"];
            $modelPrices->setPriceDay($id_resource, $id_belongings, $d["price_day"]);
            $modelPrices->setPriceNight($id_resource, $id_belongings, $d["price_night"]);
            $modelPrices->setPriceWe($id_resource, $id_belongings, $d["price_we"]);
        }
    }
    
    public function importScheduling($pdo_old, $areasMap){
        $sql = "SELECT * FROM sy_areas";
        $result = $pdo_old->query($sql);
	$resources_old = $result->fetchAll();
        
        $model = new BkScheduling();
        foreach($resources_old as $d){
            $id = $areasMap[$d["id"]];
            $is_monday = 1;
            $is_tuesday = 1;
            $is_wednesday = 1;
            $is_thursday = 1;
            $is_friday = 1;
            $is_saturday = 0;
            $is_sunday = 0;
            $day_begin = 8;
            $day_end = 18;
            $size_bloc_resa = 3600;
            $booking_time_scale = 1;
            $resa_time_setting = 1;
            $default_color_id = 1;
            
            $model->edit($id, $is_monday, $is_tuesday, $is_wednesday, $is_thursday, $is_friday, $is_saturday, 
                    $is_sunday, $day_begin, $day_end, $size_bloc_resa, $booking_time_scale, 
                    $resa_time_setting, $default_color_id);
            
        }
    }
    
    public function importProjectServices($pdo_old, $id_space, $serviceTypeMap){
        $sql = "SELECT * FROM sp_items";
        $result = $pdo_old->query($sql);
	$services_old = $result->fetchAll();
        
        $model = new SeService();
        $servicesMap = array();
        foreach($services_old as $d){
            $newID = $model->setService(0, $id_space, $d["name"], $d["description"], $d["display_order"], $serviceTypeMap[$d["type_id"]]);
            $servicesMap[$d["id"]] = $newID;
        }
        return $servicesMap;
    }
    
    public function importProjectPrices($pdo_old, $servicesMap, $belongingsMap){
        $sql = "SELECT * FROM sp_j_item_pricing";
        $result = $pdo_old->query($sql);
	$prices_old = $result->fetchAll();
        
        $modelPrice = new SePrice();
        foreach($prices_old as $d){
            $id_service = 0;
            if(isset($servicesMap[$d["id_item"]])){
                $id_service = $servicesMap[$d["id_item"]];
            }
            
            $id_belongings = $belongingsMap[$d["id_pricing"]];
            $price = $d["price"];
            $modelPrice->setPrice($id_service, $id_belongings, $price);
        }
    }
    
    public function importProjectInvoices($pdo_old, $id_space, $unitsMap, $usersMap){
        
        $sql = "SELECT * FROM sp_bills";
        $result = $pdo_old->query($sql);
	$bills_old = $result->fetchAll();
        
        $model = new InInvoice();
        $modelItem = new InInvoiceItem();
        $modelUser = new EcUser();
        $invoicesMap = array();
        foreach($bills_old as $d){
            $module = "services";
            $controller = "servicesinvoiceproject";
            $number = $d["number"];
            $date_generated = $d["date_generated"]; 
            
            $id_resp = 0;
            if (isset($usersMap[$d["id_resp"]])){
                $id_resp = $usersMap[$d["id_resp"]];
            }
            
            $id_unit = $modelUser->getUnit($usersMap[$d["id_resp"]]);
            $id_responsible = $id_resp; 
            $total_ht = $d["total_ht"] ;
            $period_begin = ""; 
            $period_end = "";
            $newID = $model->addInvoice($module, $controller, $id_space, $number, $date_generated, $id_unit, $id_responsible, $total_ht, $period_begin, $period_end);
            $invoicesMap[$d["id"]] = $newID;
            
            $content = "";
            $sqlProj = "SELECT name FROM sp_projects WHERE id=".$d["id"];
            $result = $pdo_old->query($sqlProj);
            $name = $result->fetch();
            $details = $name[0] . "=" . "servicesprojectedit/".$id_space."/0". ";";
            $modelItem->setItem(0, $newID, $module, $controller, $content, $details, $total_ht);
            
        }
        return $invoicesMap;
    }
    
    public function importProject($pdo_old, $id_space, $usersMap, $mapInvoices, $servicesMap){
        
        // import project
        $sql = "SELECT * FROM sp_projects";
        $result = $pdo_old->query($sql);
	$projects_old = $result->fetchAll();
        
        $modelProject = new SeProject();
        $projectMap = array();
        foreach($projects_old as $d){
            $name = $d["name"];
            $id_resp = $usersMap[$d["id_resp"]];
            $id_user = $usersMap[$d["id_user"]];
            $date_open = $d["date_open"];
            $date_close = $d["date_close"]; 
            $new_team = $d["new_team"];
            $new_project = $d["new_project"]; 
            $time_limit = $d["time_limit"];
            $newID = $modelProject->setProject(0, $id_space, $name, $id_resp, $id_user, $date_open, $date_close, $new_team, $new_project, $time_limit);
            $projectMap[$d["id"]] = $newID;
        }
        
        // import project services
        $sql2 = "SELECT * FROM sp_projects_entries";
        $result2 = $pdo_old->query($sql2);
	$entries_old = $result2->fetchAll();
        
        foreach($entries_old as $d){
            $id_project = $d["id_proj"];
            $date = $d["date"];
            $id_service = 0;
            if(isset($servicesMap[$d["id_item"]])){
                $id_service = $servicesMap[$d["id_item"]];
            }
            
            $quantity = $d["quantity"];
            $id_invoice = 0;
            if(isset($mapInvoices[$d["invoice_id"]])){
                $id_invoice = $mapInvoices[$d["invoice_id"]];
            }
            $comment = $d["comment"];
            $modelProject->setService($id_project, $id_service, $date, $quantity, $comment, $id_invoice);
        }
    }
    
    // supplies
    public function importOrderServices($pdo_old, $id_space, $serviceTypeMap){
        $sql = "SELECT * FROM su_items";
        $result = $pdo_old->query($sql);
	$services_old = $result->fetchAll();
        
        $model = new SeService();
        $servicesMap = array();
        foreach($services_old as $d){
            $newID = $model->setService(0, $id_space, $d["name"], $d["description"], 1, 1);
            $servicesMap[$d["id"]] = $newID;
        }
        return $servicesMap;
    }
    public function importOrderPrices($pdo_old, $servicesOrderMap, $belongingMap){
        $sql = "SELECT * FROM su_j_item_pricing";
        $result = $pdo_old->query($sql);
	$prices_old = $result->fetchAll();
        
        $modelPrice = new SePrice();
        foreach($prices_old as $d){
            $id_service = 1;
            if(isset($servicesOrderMap[$d["id_item"]])){
                $id_service = $servicesOrderMap[$d["id_item"]];
            }
            $id_belongings = 1;
            if(isset($belongingMap[$d["id_pricing"]])){
                $id_belongings = $belongingMap[$d["id_pricing"]];
            }
            $price = $d["price"];
            $modelPrice->setPrice($id_service, $id_belongings, $price);
        }
    }
    
    public function importOrderInvoices($pdo_old, $id_space, $unitsMap, $usersMap){
               
        $sql = "SELECT * FROM su_bills";
        $result = $pdo_old->query($sql);
	$bills_old = $result->fetchAll();
        
        $model = new InInvoice();
        $invoicesMap = array();
        foreach($bills_old as $d){
            $module = "services";
            $controller = "servicesinvoiceorder";
            $number = $d["number"];
            $date_generated = $d["date_generated"]; 
            $id_unit = $unitsMap[$d["id_unit"]]; 
            $id_responsible = 1;
            if(isset($usersMap[$d["id_resp"]])){
                $id_responsible = $usersMap[$d["id_resp"]]; 
            }
            $total_ht = $d["total_ht"] ;
            $period_begin = ""; 
            $period_end = "";
            $newID = $model->addInvoice($module, $controller, $id_space, $number, $date_generated, $id_unit, $id_responsible, $total_ht, $period_begin, $period_end);
            $invoicesMap[$d["id"]] = $newID;
            
        }
        return $invoicesMap;
    }
    
    public function importOrder($pdo_old, $id_space, $usersMap, $servicesMap){
        // import project
        $sql = "SELECT * FROM su_entries";
        $result = $pdo_old->query($sql);
	$order_old = $result->fetchAll();
        
        $modelOrder = new SeOrder();
        $orderMap = array();
        foreach($order_old as $d){
            
            
            $no_identification = $d["no_identification"];
            //$id_resp = $usersMap[$d["id_resp"]];
            $id_user = 1;
            if(isset($usersMap[$d["id_user"]])){
                $id_user = $usersMap[$d["id_user"]];
            }
            $date_open = $d["date_open"];
            $date_close = $d["date_close"]; 
            $date_last_modified = $d["date_last_modified"];
            $newOrderID = $modelOrder->setOrder(0, $id_space, $id_user, $no_identification, 0, $date_open, $date_last_modified, $date_close);
            $orderMap[$d["id"]] = $newOrderID;
            
            $content = explode(";", $d["content"]);
            foreach($content as $c){
                $item = explode("=", $c);
                if (count($item) == 2){
                    $itm = 1;
                    if (isset($servicesMap[$item[0]])){
                        $itm = $servicesMap[$item[0]];
                    }
                    $modelOrder->setService($newOrderID, $itm, $item[1]);
                }
            }
        }
    }
}
