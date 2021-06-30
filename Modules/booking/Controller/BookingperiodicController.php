<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Email.php';
require_once 'Modules/booking/Controller/BookingabstractController.php';

require_once 'Modules/resources/Model/ResourcesTranslator.php';

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


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingdefaultController extends BookingabstractController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->module = "booking";
        //$this->checkAuthorizationMenu("booking");
    }

    public function indexAction() {
        
    }

    public function editreservationdefault($id_space, $param) {
        
        $this->checkAuthorizationMenuSpace("booking", $id_space, $_SESSION["id_user"]);

        if ($this->isNew($param)) {
            $resaInfo = $this->addreservation($param);
            $this->editReservation($id_space, $resaInfo, $param);
        } else {
            $resaInfo = $this->editReservationInfo($param);
            $this->editReservation($id_space, $resaInfo, $param);
        }
    }

    private function addreservation($param) {
        // get the parameters
        $paramVect = explode("_", $param);
        $date = $paramVect[1];
        $dateArray = explode("-", $date);
        $hour = $paramVect[2];
        $hourArray = explode("-", $hour);
        $id_resource = $paramVect[3];

        $minutes = 0;
        if (count($hourArray) == 2) {
            $minutes = $hourArray[1];
        }

        $start_time = mktime($hourArray[0], $minutes, 0, $dateArray[1], $dateArray[2], $dateArray[0]);
        $end_time = $start_time + 3600;

        $modelResa = new BkCalendarEntry();
        $id_user = $_SESSION["id_user"];
        
        $modelResource = new ResourceInfo();
        $_SESSION['bk_id_resource'] = $id_resource;
        $_SESSION['bk_id_area'] = $modelResource->getAreaID($id_resource);
        $_SESSION['bk_curentDate'] = date("Y-m-d", $start_time);
        
        return $modelResa->getDefault($start_time, $end_time, $id_resource, $id_user);
    }

    private function canUserEditReservation($id_space, $id_user, $id_reservation, $id_recipient, $start_date){
        
        if ($id_reservation == 0){
            return true;
        }
        $modelSpace = new CoreSpace();
        $role = $modelSpace->getUserSpaceRole($id_space, $id_user);
        if ($role >= 3){
            return true;
        }
        if ($id_recipient == $id_user){
            if ($start_date > time()){
                return true;
            }
        }
        return false;
    }
    
    public function editreservationqueryAction($id_space) {

        $responsible_id = $this->request->getParameterNoException("responsible_id"); 
        
        $id = $this->request->getParameter("id");
        $id_resource = $this->request->getParameter("id_resource");
        $booked_by_id = $_SESSION["id_user"];
        $recipient_id = $this->request->getParameter("recipient_id");
        $last_update = date("Y-m-d H:m:i", time());
        $color_type_id = $this->request->getParameter("color_type_id");
        $short_description = $this->request->getParameter("short_description");
        $full_description = $this->request->getParameterNoException("full_description");
        
        $lang = $this->getLanguage();
        $dateResaStart = CoreTranslator::dateToEn($this->request->getParameter("resa_start"), $lang);
        $dateResaStartArray = explode("-", $dateResaStart);
        $hour_startH = $this->request->getParameter("hour_startH");
        $hour_startM = $this->request->getParameter("hour_startm");
        $start_time = mktime($hour_startH, $hour_startM, 0, $dateResaStartArray[1], $dateResaStartArray[2], $dateResaStartArray[0]);

        $dateResaEnd = CoreTranslator::dateToEn($this->request->getParameter("resa_end"), $lang);
        
        $dateResaEndArray = explode("-", $dateResaEnd);
        $hour_endH = $this->request->getParameter("hour_endH");
        //echo "hour_endH = " . $hour_endH . "<br/>";
        $hour_endM = $this->request->getParameter("hour_endm");
        $end_time = mktime($hour_endH, $hour_endM, 0, $dateResaEndArray[1], $dateResaEndArray[2], $dateResaEndArray[0]);
  
        $modelSupInfo = new BkCalSupInfo();
        $supInfos = $modelSupInfo->getForResource($id_resource);
        $supplementaries = "";
        foreach ($supInfos as $sup) {
            $q = $this->request->getParameterNoException("sup" . $sup["id"]);
            $supplementaries .= $sup["id"] . "=" . $q . ";";
        }

        $modelQuantities = new BkCalQuantities();
        $quantitiesInfo = $modelQuantities->calQuantitiesByResource($id_resource);
        $quantities = "";
        foreach ($quantitiesInfo as $q) {
            $qt = $this->request->getParameterNoException("q" . $q["id"]);
            $quantities .= $q["id"] . "=" . $qt . ";";
        }

        $use_package = $this->request->getParameterNoException("use_package");
        $package_id = 0;
        if ($use_package == "yes") {
            $package_id = $this->request->getParameter("package_id");
            $modelPackage = new BkPackage();
            $pk_duration = $modelPackage->getPackageDuration($package_id);
            $end_time = $start_time + 3600*$pk_duration;
        }

        $modelResp = new ClClientUser();
        $userResps = $modelResp->getUserClientAccounts($recipient_id, $id_space);
        $foundResp = false;
        foreach($userResps as $uresp){
            if($uresp["id"] == $responsible_id){
                $foundResp = true;
                break;
            }
        }
        Configuration::getLogger()->debug(["TEST"], ["after new EcResponsible" => "var2"]);
        
        if( !$foundResp ){
            $resaInfo = array(
                "id" => $id,
                "start_time" => $start_time,
                "end_time" => $end_time,
                "resource_id" => $id_resource,
                "booked_by_id" => $booked_by_id,
                "recipient_id" => $recipient_id,
                "last_update" => date("Y-m-d H:m:i", time()),
                "color_type_id" => $color_type_id,
                "short_description" => $short_description,
                "full_description" => $full_description,
                "quantities" => $quantities,
                "supplementaries" => $supplementaries,
                "package_id" => $package_id,
                "responsible_id" => 0
            );
            $this->editReservation($id_space, $resaInfo);
            return;
        }

        $canEdit = $this->canUserEditReservation($id_space, $_SESSION["id_user"], $id, $recipient_id, $start_time);
        if (!$canEdit){
            throw new Exception("ERROR: You're not allowed to modify this reservation"); 
        }
        
        $modelCalEntry = new BkCalendarEntry();
        
        $valid = true;
        if($start_time >= $end_time){
            $_SESSION["message"] = "Error: Start Time Must Be Before End Time";
            $valid = false;
        }
        if($start_time==0){
            $_SESSION["message"] = "Error: Start Time Cannot Be Null";
            $valid = false;
        }
        if($start_time==0){
            $_SESSION["message"] = "Error: End Time Cannot Be Null";
            $valid = false;
        }
        
	// test if a resa already exists on this periode
	$conflict = $modelCalEntry->isConflict($start_time, $end_time, $id_resource, $id);
			
	if ($conflict){
            $_SESSION["message"] = BookingTranslator::reservationError($lang);
            $valid = false;
	}
        
        if($valid){
            $modelCalEntry->setEntry($id, $start_time, $end_time, $id_resource, $booked_by_id, $recipient_id, $last_update, 
                $color_type_id, $short_description, $full_description, $quantities, $supplementaries, $package_id, $responsible_id);
            $_SESSION["message"] = BookingTranslator::reservationSuccess($lang);    
        }
        
        $this->redirect("booking/".$id_space."/".$_SESSION["bk_id_area"]."/".$_SESSION["bk_id_resource"]);
    }

    private function editreservation($id_space, $resaInfo, $param = "") {
        
        $lang = $this->getLanguage();
        $modelSpace = new CoreSpace();
        $role = $modelSpace->getUserSpaceRole($id_space, $_SESSION["id_user"]);
        $canEditReservation = false;
        if ($role > 2) {
            $canEditReservation = true;
        }

        $id_resource = $resaInfo["resource_id"];

        $modelResource = new ResourceInfo();
        $resources = $modelResource->getAllForSelect($id_space, "name");

        $modelUser = new CoreUser();
        $users = $modelUser->getAcivesForSelect("name");

        $form = new Form($this->request, "editReservationDefault");
        $form->addHidden("id", $resaInfo["id"]);
        $form->setValisationUrl("bookingeditreservationquery/".$id_space);
        $form->setTitle(BookingTranslator::Edit_Reservation($lang));
        $form->addSelect("id_resource", ResourcesTranslator::resource($lang), $resources["names"], $resources["ids"], $id_resource);
        if ($this->canBookForOthers($id_space, $_SESSION["id_user"])) {
            $form->addSelect("recipient_id", CoreTranslator::User($lang), $users["names"], $users["ids"], $resaInfo["recipient_id"], true);
        } else {
            $form->addHidden("recipient_id", $resaInfo["recipient_id"]);
        }
        // responsible
        $modelUserClient = new ClClientUser();
        if ($canEditReservation) {
            $modelUser = new CoreUser();
            $choices = array();
            $choicesid = array();
            $rID = $this->request->getParameterNoException("recipient_id");
            if ($rID == ""){
                $rID = $resaInfo["recipient_id"];
            }
            $resps = $modelUserClient->getUserClientAccounts($rID, $id_space);
            foreach ($resps as $r) {
                $choicesid[] = $r["id"];
                $choices[] = $modelUser->getUserFUllName($r["id"]);
            }
            $form->addSelect("responsible_id", CoreTranslator::Responsible($lang), $choices, $choicesid, $resaInfo["responsible_id"]);
        } else {
            $resps = $modelUserClient->getUserClientAccounts($_SESSION["id_user"], $id_space);
            if (count($resps) > 1) {
                $modelUser = new CoreUser();
                $choices = array();
                $choicesid = array();
                foreach ($resps as $r) {
                    $choicesid[] = $r["id"];
                    $choices[] = $modelUser->getUserFUllName($r["id"]);
                }
                $form->addSelect("responsible_id", CoreTranslator::Responsible($lang), $choices, $choicesid, $resaInfo["responsible_id"]);
            } else {
                $form->addHidden("responsible_id", $resaInfo["responsible_id"]);
            }
        }

        // description
        $modelCoreConfig = new CoreConfig();
        $BkDescriptionFields = $modelCoreConfig->getParam("BkDescriptionFields");
        if ($BkDescriptionFields == 1 || $BkDescriptionFields == 3) {
            $form->addText("short_description", BookingTranslator::Short_desc($lang), false, $resaInfo["short_description"]);
        }
        if ($BkDescriptionFields == 2 || $BkDescriptionFields == 3) {
            $form->addTextArea("full_description", BookingTranslator::Short_desc($lang), false, $resaInfo["full_description"]);
        }

        // supplemetaries informations
        $modelSupInfo = new BkCalSupInfo();
        $supInfos = $modelSupInfo->getForResource($id_resource);
        $supData = explode(";", $resaInfo["supplementaries"]);
        $supDataId = array();
        $supDataValue = array();
        foreach ($supData as $sup) {
            $sd = explode("=", $sup);
            if (count($sd) == 2) {
                $supDataId[] = $sd[0];
                $supDataValue[] = $sd[1];
            }
        }
        foreach ($supInfos as $sup) {
            $name = $sup["name"];
            if ($sup["mandatory"] == 1) {
                $name .= "*";
            }
            $key = array_search($sup["id"], $supDataId);
            $value = "";
            if ($key !== false) {
                $value = $supDataValue[$key];
            }
            $form->addText("sup" . $sup["id"], $sup["name"], $sup["mandatory"], $value);
        }

        $modelColors = new BkColorCode();
        
        $modelUserSpace = new CoreSpace();
        $userSPaceRole = $modelUserSpace->getUserSpaceRole($id_space, $_SESSION["id_user"]);
        $colors = $modelColors->getColorCodesForListUser($id_space, $userSPaceRole, "display_order");
        $form->addSelect("color_type_id", BookingTranslator::color_code($lang), $colors["names"], $colors["ids"], $resaInfo["color_type_id"]);

        // quantities
        $modelQuantities = new BkCalQuantities();
        $quantitiesInfo = $modelQuantities->calQuantitiesByResource($id_resource);
        $qData = explode(";", $resaInfo["quantities"]);
        $qDataId = array();
        $qDataValue = array();
        foreach ($qData as $sup) {
            $sd = explode("=", $sup);
            if (count($sd) == 2) {
                $qDataId[] = $sd[0];
                $qDataValue[] = $sd[1];
            }
        }
        foreach ($quantitiesInfo as $q) {
            $name = $q["name"];
            if ($q["mandatory"] == 1) {
                $name .= "*";
            }
            $key = array_search($q["id"], $qDataId);
            $value = "";
            if ($key !== false) {
                $value = $qDataValue[$key];
            }
            $form->addNumber("q" . $q["id"], $q["name"], $q["mandatory"], $value);
        }

        // booking nav bar
        $curentResource = $_SESSION['bk_id_resource'];
        $curentAreaId = $_SESSION['bk_id_area'];
        $curentDate = date("Y-m-d", $resaInfo["start_time"]);
        $_SESSION['bk_curentDate'] = $curentDate;
        $menuData = $this->calendarMenuData($id_space, $curentAreaId, $curentResource, $curentDate);

        // date time
        $form->addDate("resa_start", BookingTranslator::Beginning_of_the_reservation($lang), false, CoreTranslator::DateFromEn(date("Y-m-d", $resaInfo["start_time"]), $lang));
        $form->addHour("hour_start", BookingTranslator::time($lang), false, array(date("H", $resaInfo["start_time"]), date("i", $resaInfo["start_time"])));

        // conditionnal on package
        $modelPackage = new BkPackage();
        $packages = $modelPackage->getByResource($id_resource);
        $pNames = array();
        $pIds = array();
        foreach ($packages as $p) {
            $pNames[] = $p["name"];
            $pIds[] = $p["id"];
        }

        $use_packages = false;
        if (count($packages) > 0) {
            $use_packages = true;
        }
        $formPackage = new Form($this->request, "formPackage");
        $formPackage->addSelect("package_id", BookingTranslator::Package($lang), $pNames, $pIds, $resaInfo["package_id"], false);

        $formEndDate = new Form($this->request, "formEndDate");
        $formEndDate->addDate("resa_end", BookingTranslator::End_of_the_reservation($lang), false, CoreTranslator::DateFromEn(date("Y-m-d", $resaInfo["end_time"]), $lang));
        $formEndDate->addHour("hour_end", BookingTranslator::time($lang), false, array(date("H", $resaInfo["end_time"]), date("i", $resaInfo["end_time"])));

        $packageChecked = $resaInfo["package_id"];

        $userCanEdit = $this->canUserEditReservation($id_space, $_SESSION["id_user"], $resaInfo["id"], $resaInfo["recipient_id"], $resaInfo["start_time"]);
        
        // create delete form
        $formDelete = new Form($this->request, "bookingeditreservationdefaultdeleteform");
        $formDelete->addComment(BookingTranslator::RemoveReservation($lang));
        $formDelete->addHidden("id_reservation", 0);
        
        $sendEmailWhenDelete = $modelCoreConfig->getParamSpace('BkBookingMailingDelete', $id_space);
        if($sendEmailWhenDelete == 1){
            $formDelete->addSelect("sendmail", BookingTranslator::SendEmailsToUsers($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), 1);
        }
        else{
            $formDelete->addHidden("sendmail", 0);
        }
        $formDelete->setValidationButton(CoreTranslator::Ok($lang), 'bookingeditreservationdefaultdelete/' . $id_space ."/". $resaInfo["id"]);
        $formDelete->setButtonsWidth(2, 10);
        
        $BkUseRecurentBooking = $modelCoreConfig->getParamSpace("BkUseRecurentBooking", $id_space);
        
        
        $this->render(array("id_space" => $id_space, "lang" => $lang, "menuData" => $menuData,
            "form" => $form, "use_packages" => $use_packages,
            "packageChecked" => $packageChecked,
            "userCanEdit" => $userCanEdit,
            "id_reservation" => $resaInfo["id"],
            "canEditReservation" => $canEditReservation,
            "formPackage" => $formPackage->getHtml($lang, false),
            "formEndDate" => $formEndDate->getHtml($lang, false),
            "usePeriodicBooking" => $BkUseRecurentBooking,
            "bookingType" => "single",
            "args" => $param,
            "formDelete" => $formDelete->getHtml($lang)), "addreservationAction");
    }

    private function canBookForOthers($id_space, $id_user) {
        $modelSpace = new CoreSpace();
        $userRole = $modelSpace->getUserSpaceRole($id_space, $id_user);
        return ($userRole >= 3);
    }

    private function editReservationInfo($param) {
        $contentAction = explode("_", $param);
        $id = $contentAction[1];
        
        $modelCalEntry = new BkCalendarEntry();
        $entryInfo =  $modelCalEntry->getEntry($id);
        
        $modelResource = new ResourceInfo();
        $_SESSION['bk_id_resource'] = $entryInfo["resource_id"];
        $_SESSION['bk_id_area'] = $modelResource->getAreaID($entryInfo["resource_id"]);
        $_SESSION['bk_curentDate'] = date("Y-m-d", $entryInfo["start_time"]);
        
        return $entryInfo;
    }

    private function isNew($param) {
        $contentAction = explode("_", $param);
        if ($contentAction[0] == "t"){
            return true;
        }
        return false;
    }

    public function deleteAction($id_space, $id){
        $sendEmail = $this->request->getParameter("sendmail");
        
        if( $sendEmail == 1){
            // get the resource
            $modelCalEntry = new BkCalendarEntry();
            $entryInfo = $modelCalEntry->getEntry($id);
            $id_resource = $entryInfo["resource_id"];
            $resourceModel = new ResourceInfo();
            $resourceName = $resourceModel->getName($id_resource);
            
            // mail content
            $toAddress = $modelCalEntry->getEmailsBookerResource($id_resource);
            $subject = $resourceName . " has been freed"; 
            $content = "The " . $resourceName . " has been freed from " . date("Y-m-d H:i", $entryInfo["start_time"]) . " to " . date("Y-m-d H:i", $entryInfo["end_time"]); 
     
           // NEW MAIL SENDER
            $params = [
                "id_space" => $id_space,
                "subject" => $subject,
                "to" => $toAddress,
                "content" => $content
            ];
            $email = new Email();
            $email->sendEmailToSpaceMembers($params, $this->getLanguage());
        }
        
        $modelCalEntry = new BkCalendarEntry();
        $modelCalEntry->removeEntry($id);
        
        $this->redirect("booking/".$id_space);
    }
}
