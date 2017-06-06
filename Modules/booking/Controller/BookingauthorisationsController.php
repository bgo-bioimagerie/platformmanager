<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';

require_once 'Modules/booking/Model/BkAuthorization.php';

require_once 'Modules/resources/Model/ReCategory.php';
require_once 'Modules/resources/Model/ReVisa.php';

require_once 'Modules/ecosystem/Model/EcUser.php';
require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';
require_once 'Modules/booking/Model/BookingTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class BookingauthorisationsController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $_SESSION["openedNav"] = "ecusers";
    }

    public function indexAction($id_space, $id) {

        $this->checkAuthorizationMenuSpace("ecusers", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // get al the authorisations for the user
        $modelAuthorization = new BkAuthorization();
        $userAuthorizations = $modelAuthorization->getUserAuthorizations($id);

        // get all the resources
        $modelResources = new ReCategory();
        $resources = $modelResources->getBySpace($id_space);

        // user name
        $modelUser = new EcUser();
        $userName = $modelUser->getUserFUllName($id);

        // user unit
        $unit_id = $modelUser->getUnit($id);

        // visas
        $modelVisa = new ReVisa();
        $resourceVisas = array();
        foreach ($resources as $res) {
            $resourceVisas[$res["id"]] = $modelVisa->getVisasDesc($res["id"], $lang);
        }
        
        $modelConfig = new CoreConfig();
        $BkAuthorisationUseVisa = $modelConfig->getParamSpace("BkAuthorisationUseVisa", $id_space);

        $this->render(array(
            "lang" => $lang,
            "id_space" => $id_space,
            'userAuthorizations' => $userAuthorizations,
            'resources' => $resources,
            'userID' => $id,
            'unit_id' => $unit_id,
            'userName' => $userName,
            'visas' => $resourceVisas,
            'BkAuthorisationUseVisa' => $BkAuthorisationUseVisa
        ));
    }

    /**
     *
     */
    public function queryAction($id_space) {

        $this->checkAuthorizationMenuSpace("ecusers", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        $modelConfig = new CoreConfig();
        $BkAuthorisationUseVisa = $modelConfig->getParamSpace("BkAuthorisationUseVisa", $id_space);

        //echo "BkAuthorisationUseVisa = " . $BkAuthorisationUseVisa . "<br/>";
        
        $user_id = $this->request->getParameter("user_id");
        $unit_id = $this->request->getParameter("unit_id");
        $resource_id = $this->request->getParameter("resource_id");
        $is_active = $this->request->getParameter("is_active");
        
        if($BkAuthorisationUseVisa == 1){
            $date = $this->request->getParameter("date");
            $visa_id = $this->request->getParameter("visa_id");
        }
        else{
            $date = array();
            $visa_id = array();
            for( $i = 0 ; $i < count($resource_id) ; $i++ ){
                $date[] = "";
                $visa_id = 0;
            }
            
        }

        //print_r($resource_id);
        //print_r($is_active);

        $modelAuthorization = new BkAuthorization();
        for ($i = 0; $i < count($resource_id); $i++) {
            $authorizationID = $modelAuthorization->getAuthorisationID($resource_id[$i], $user_id);
            //echo "authorizationID = " . $authorizationID  . "<br/>";
            //echo "is_active = " . $is_active[$i]  . "<br/>";
            $cdate = CoreTranslator::dateToEn($date[$i], $lang);
            //echo "date = " . $date[$i] . "<br/>";
            //echo "cdate = " . $cdate . "<br/>";
            
            if($is_active[$i] > 0 && $BkAuthorisationUseVisa == 1){
                //echo 'test active <br>';
                //echo 'date = ' . $cdate . "<br>";
                //echo 'visa_id = ' . $visa_id[$i] . "<br>";
                if($cdate == "" || $cdate == 0 || $visa_id[$i] < 1){
                    $message = BookingTranslator::FieldsDateAndVisaAreMandatory($lang) . " " . $i ;
                    $_SESSION["message"] = $message;
                    //echo $message . "<br>";
                    $this->redirect("bookingauthorisations/".$id_space."/".$user_id);
                    return;
                }
            }
            
            if ($authorizationID > 0) {
                $modelAuthorization->editAuthorization($authorizationID, $cdate, $user_id, $unit_id, $visa_id[$i], $resource_id[$i], $is_active[$i]);
            } else {
                if ($is_active[$i] > 0) {
                    // add authorization
                    //echo "add authorization for resource : ". $resource_id[$i]. "<br/>";
                    $modelAuthorization->addAuthorization($cdate, $user_id, $unit_id, $visa_id[$i], $resource_id[$i], 1);
                }
            }
        }

        $_SESSION["message"] = BookingTranslator::Modifications_have_been_saved($lang);
        $this->redirect("bookingauthorisations/".$id_space . "/" . $user_id);
    }

}
