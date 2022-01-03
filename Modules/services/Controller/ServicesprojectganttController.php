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
require_once 'Modules/services/Controller/ServicesController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesprojectganttController extends ServicesController {

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space, $allPeriod = 0, $incharge = "") {

        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // get sort action
        $modelProject = new SeProject();
        $modelVisa = new SeVisa();

        if($allPeriod == 1){
            $modelConfig = new CoreConfig();
            $projectperiodbegin = $modelConfig->getParamSpace("projectperiodbegin", $id_space);
            $projectperiodend = $modelConfig->getParamSpace("projectperiodend", $id_space);
            $today = new DateTime();
            if ($projectperiodbegin === "") {
                $projectperiodbegin = $today->format('Y')."-1-1";
            }
            if ($projectperiodend === "") {
                $projectperiodend = $today->format('Y')."-12-31";
            }
            
            $projectperiodbeginArray = explode("-", $projectperiodbegin);
            $projectperiodendArray = explode("-", $projectperiodend);
            if( $projectperiodbeginArray[1] <= date("m", time()) ){
                $year = date("Y", time());
            }
            else{
                $year = date("Y", time()) - 1;
            }
            $yearp = $year + 1;
            $periodStart = $year . "-" . $projectperiodbeginArray[1] . "-" . $projectperiodbeginArray[2];
            $periodEnd = $yearp . "-" . $projectperiodendArray[1] . "-" . $projectperiodendArray[2] . "<br/>";
                    
            if ($incharge == "") {
                $projects = $modelProject->allPeriodProjects($id_space, $periodStart, $periodEnd);
            } else {
                $projects = $modelProject->allPeriodProjectsByInCharge($id_space, $incharge, $periodStart, $periodEnd);
            }
        }
        else{
            if ($incharge == "") {
                $projects = $modelProject->allOpenedProjects($id_space);
            } else {
                $projects = $modelProject->allOpenedProjectsByInCharge($id_space, $incharge);
            }
        }

        // format into json
        $modelUser = new CoreUser();
        $projectsjson = "[";
        $first = true;
        $modelClient = new ClClient();
        $modelBelonging = new ClPricing();
        foreach ($projects as $proj) {

            $belInfo = $modelClient->get($id_space, $proj["id_resp"]);
            
            $bkColor = 'col' . $belInfo["id"];
            
            if (!$first) {
                $projectsjson .= ",";
            }
            $first = false;
            $projectsjson .= "{";

            $visa = $modelVisa->get($id_space ,$proj["in_charge"]);
            $projectsjson .= "name: \"" . $modelUser->getUserInitiales($visa["id_user"]) . "\",";
            $projectsjson .= "desc: \"" . $proj["name"] . "\",";
            $projectsjson .= "values: [{";

            $startTime = time();
            if ($proj["date_open"] && $proj["date_open"] != "0000-00-00") {
                $startTime = strtotime($proj["date_open"]);
            }

            $projectsjson .= "from: \"/Date(" . 1000 * $startTime . ")/\",";

            $dateEnd = time();
            if ($proj["time_limit"] && $proj["time_limit"] != "0000-00-00" && $proj["time_limit"] != "") {
                $dateEnd = strtotime($proj["time_limit"]);
            }

            $projectsjson .= "to: \"/Date(" . 1000 * $dateEnd . ")/\",";
            $projectsjson .= "label: \"" . $proj["name"] . "\",";
            $projectsjson .= "customClass: \"" . $bkColor . "\"";
            $projectsjson .= "}";

            $closeTime = 0;
            if ($proj["date_close"] && $proj["date_close"] != "0000-00-00" && $proj["date_close"] != "") {
                $closeTime = strtotime($proj["date_close"]);
            }
            if ($closeTime == 0) {
                $closeTime = time();
            }
            if ($closeTime > $dateEnd) {
                $projectsjson .= ",{";
                $projectsjson .= "from: \"/Date(" . 1000 * $dateEnd . ")/\",";
                $projectsjson .= "to: \"/Date(" . 1000 * $closeTime . ")/\",";
                $projectsjson .= "label: \"" . "\",";
                $projectsjson .= "customClass: \"colTimeOver\"";
                $projectsjson .= "}";
            }

            $projectsjson .= "]";
            $projectsjson .= "}";
        }
        $projectsjson .= "]";


        $bels = $modelBelonging->getAll($id_space);
        $css = "";
        foreach ($bels as $bel) {

            $css .= ".fn-gantt ." . "col" . $bel["id"] . " {";
            $css .= "background-color: " . $bel["color"] . ";";
            $css .= "}";
        }
        $css .= ".fn-gantt ." . "colTimeOver" ." {";
        $css .= "background-color: #DFAF2C;";
        $css .= "}";


        $personInCharge = $modelVisa->getAll($id_space);
        //print_r($personInCharge);
        // render 
        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'projectsjson' => $projectsjson,
            'personInCharge' => $personInCharge,
            'activeGantt' => $incharge,
            'css' => $css,
            'allPeriod' => $allPeriod
                ), "indexAction");
    }

}
