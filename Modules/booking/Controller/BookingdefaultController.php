<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/booking/Model/BookingTranslator.php';
require_once 'Modules/booking/Model/BkBookingTableCSS.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';
require_once 'Modules/booking/Model/BkCalSupInfo.php';
require_once 'Modules/booking/Model/BkScheduling.php';
require_once 'Modules/booking/Model/BkAccess.php';
require_once 'Modules/booking/Model/BkPackage.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ReArea.php';

require_once 'Modules/core/Model/CoreUserSettings.php';
require_once 'Modules/core/Model/CoreProject.php';

require_once 'Modules/ecosystem/Model/EcSite.php';
require_once 'Modules/ecosystem/Model/EcUser.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingdefaultController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $this->checkAuthorizationMenu("booking");
    }
    
    public function indexAction(){
        
    }

    public function editreservationdefault($param){
     
        if ($this->isNew($param)){
            $this->addReservation($param);
        }
        else{
            $this->editReservation($param);
        }
    }
    
    private function addReservation($param){
        
        $lang = $this->getLanguage();
        
        // get the parameters
        $paramVect = explode("_", $param);
        $date = $paramVect[1];
        $hour = $paramVect[2];
        $id_resource = $paramVect[3];
        
        $modelResource = new ResourceInfo();
        $resources = $modelResource->getAllForSelect("name");
        
        $modelUser = new EcUser();
        $users = $modelUser->getAcivesForSelect("name");
        
        $id_user = $_SESSION["id_user"];
        
        $form = new Form($this->request, "editReservationDefault");
        $form->setTitle(BookingTranslator::Edit_Reservation($lang));
        $form->addSelect("Resource", ResourcesTranslator::resource($lang), $resources["names"], $resources["ids"], $id_resource);
        if($this->canBookForOthers($id_user)){
            $form->addSelect("id_user", CoreTranslator::User($lang), $users["names"], $users["ids"], $id_user);
        }
        else{
            $form->addHidden("id_user", $id_user);
        }
        
        // description
        $modelCoreConfig = new CoreConfig();
        $BkDescriptionFields = $modelCoreConfig->getParam("BkDescriptionFields");
        if ($BkDescriptionFields == 1 || $BkDescriptionFields == 3){
            $form->addText("short_desc", BookingTranslator::Short_desc($lang), false, "value");
        }
        if ($BkDescriptionFields == 2 || $BkDescriptionFields == 3){
            $form->addTextArea("long_desc", BookingTranslator::Short_desc($lang), false, "value");
        }
        
        // supplemetaries informations
        $modelSupInfo = new BkCalSupInfo();
        $supInfos = $modelSupInfo->getForResource($id_resource);
        foreach($supInfos as $sup){
            $name = $sup["name"];
            if ($sup["mandatory"] == 1){
                $name .= "*";
            }
            $form->addText("sup".$sup["id"], $sup["name"], $sup["mandatory"]);
        }
        
        $form->addDate("resa_start", BookingTranslator::Beginning_of_the_reservation($lang));
        $form->addHour("hour_start", BookingTranslator::time($lang));
        /// todo add here conditional on the pachages
        
        $modelColors = new BkColorCode();
        $colors = $modelColors->getColorCodes("display_order");
        $form->addSelect("color_code", BookingTranslator::color_code($lang), $colors["names"], $colors["ids"]);
        
        /// todo add here the quantities
        $modelQuantities = new BkCalQuantities();
        $quantitiesInfo = $modelQuantities->calQuantitiesByResource($id_resource);
        foreach($quantitiesInfo as $q){
            $name = $q["name"];
            if ($q["mandatory"] == 1){
                $name .= "*";
            }
            $form->addText("q".$q["id"], $q["name"], $q["mandatory"]);
        }
    }
    
    private function canBookForOthers($id_user){
        
        $modelUser = new EcUser();
        $userStatus = $modelUser->getStatus($id_user);
        if ($userStatus < 3){
            return false;
        }
        else{
            return true;
        }
    }
    
    private function editReservation($param){
        
    }
    
    private function isNew($param){
        $contentAction = explode("_", $param);
        return $contentAction[0];
    }
}
