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

     // TODO: gérer les autorisations par projet ?

    public function indexAction($id_space, $allPeriod = 0, $incharge = "", $id_project = null) {

        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // get sort action
        $modelProject = new SeProject();
        $modelVisa = new SeVisa();

        $ganttOpened = ServicesTranslator::GanttOpened($lang);
        $ganttPeriod = ServicesTranslator::GanttPeriod($lang);

        if($allPeriod == 1){
            $ganttStatus = $ganttPeriod;
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
            else {
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
        } else {
            $ganttStatus = $ganttOpened;
            if ($incharge == "") {
                $projects = $modelProject->allOpenedProjects($id_space);
            } else {
                $projects = $modelProject->allOpenedProjectsByInCharge($id_space, $incharge);
            }
        }
        $personInCharge = $modelVisa->getAll($id_space);

        $headerInfo["allProjects"] = ServicesTranslator::All_projects($lang);

        $textContent = [
            "beginningPeriod" => ServicesTranslator::Beginning_period($lang),
            "endPeriod" => ServicesTranslator::End_period($lang),
            "affectedTo" => ServicesTranslator::AffectedTo($lang),
            "relatedServices" => ServicesTranslator::Related_services($lang),
            "details" => ServicesTranslator::Details($lang),
            "theme" => ServicesTranslator::Theme($lang),
            "periodError" => ServicesTranslator::PeriodError($lang),
            "project" => ServicesTranslator::Project($lang),
            "viewInKanban" => ServicesTranslator::ViewInKanban($lang),
            "allProjects" => ServicesTranslator::All_projects($lang),
        ];
        
        $data = array(
            'lang' => $lang,
            'id_space' => $id_space,
            'projects' => json_encode($projects),
            'personInCharge' => $personInCharge,
            'activeGantt' => $incharge,
            "ganttStatus" => $ganttStatus,
            'allPeriod' => $allPeriod,
            "textContent" => json_encode($textContent),
            "showProject" => json_encode($id_project),
            "headerInfo" => $headerInfo,
        );
        // render
        $this->render($data,"indexAction");
    }

}
