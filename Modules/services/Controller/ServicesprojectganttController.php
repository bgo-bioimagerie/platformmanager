<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/SeOrigin.php';
require_once 'Modules/services/Model/SeVisa.php';

require_once 'Modules/ecosystem/Model/EcUser.php';
require_once 'Modules/ecosystem/Model/EcBelonging.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesprojectganttController extends CoresecureController {

    private $serviceModel;

    /**
     * Constructor
     */
    public function __construct() {
        parent::__construct();
        $_SESSION["openedNav"] = "services";
        //$this->checkAuthorizationMenu("services");
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $incharge = "") {

        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // get sort action
        $modelProject = new SeProject();
        $modelVisa = new SeVisa();

        if ($incharge == "") {
            $projects = $modelProject->allOpenedProjects($id_space);
        } else {
            
            //$visa = $modelVisa->get($incharge);
            $projects = $modelProject->allOpenedProjectsByInCharge($id_space, $incharge);
        }

        // format into json
        $modelUser = new CoreUser();
        $projectsjson = "[";
        $first = true;
        foreach ($projects as $proj) {
            if (!$first) {
                $projectsjson .= ",";
            }
            $first = false;
            $projectsjson .= "{";
            
            $visa = $modelVisa->get($proj["in_charge"]);
            $projectsjson .= "name: \"" . $modelUser->getUserInitiales($visa["id_user"]) . "\",";
            $projectsjson .= "desc: \"" . $proj["name"] . "\",";
            $projectsjson .= "values: [{";
            $projectsjson .= "from: \"/Date(" . 1000*strtotime($proj["date_open"]) . ")/\",";
            
            $dateEnd = time();
            if ($proj["date_close"] != "0000-00-00" && $proj["date_close"] != "") {
                $dateEnd = strtotime($proj["date_close"]);
            } else if ($proj["time_limit"] != "0000-00-00" && $proj["time_limit"] != "") {
                $dateEnd = strtotime($proj["time_limit"]);
            }
            //echo "dateEnd = " . $dateEnd . "<br/>";
            //echo "dateEnd back = " . date("Y-m-d", $dateEnd ) . "<br/>";

            $projectsjson .= "to: \"/Date(" . 1000*$dateEnd . ")/\",";
            $projectsjson .= "label: \"" . $proj["name"] . "\",";
            $projectsjson .= "customClass: \"ganttRed\"";
            $projectsjson .= "}]";
            $projectsjson .= "}";
        }
        $projectsjson .= "]";

        $personInCharge = $modelVisa->getAll($id_space);
        //print_r($personInCharge);
        

        // render 
        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'projectsjson' => $projectsjson,
            'personInCharge' => $personInCharge,
            'activeGantt' => $incharge
                ), "indexAction");
    }

}
