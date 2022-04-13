<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * Controller for services module
 * Only index still used to redirect to serviceslisting  and navbar
 */
class ServicesController extends CoresecureController {

    private $serviceModel;
    private $typeModel;


    public function sideMenu() {
        $id_space = $this->args['id_space'];
        return $this->navbar($id_space);
    }

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        //$this->checkAuthorizationMenu("services");
        $this->serviceModel = new SeService();
        $this->typeModel = new SeServiceType();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $modelCoreConfig = new CoreConfig();
        $servicesuseproject = $modelCoreConfig->getParamSpace("servicesuseproject", $id_space);
        if ($servicesuseproject == 1) {
            return $this->redirect('servicesprojectsopened/' . $id_space);
        }
        $servicesusecommand = $modelCoreConfig->getParamSpace("servicesusecommand", $id_space);
        if ($servicesusecommand == 1) {
            return $this->redirect('servicesorders/' . $id_space);
        }

        return $this->redirect('serviceslisting/' . $id_space);
    }

    public function navbar($id_space) {

        $lang = $this->getLanguage();

        $html  = '<div style="color:{{color}}; background-color:{{bgcolor}}; padding: 10px">';
        $html .= '<div  style="height: 50px; padding-top: 15px; background-color:{{bgcolor}}; border-bottom: 1px solid #fff;">';
        $html .= '<a style="background-color:{{bgcolor}}; color: {{color}};" href="serviceslisting/'.$id_space.'"> {{title}}'; 
        $html .= '    <span style="color: #fff; font-size:16px; float:right;" class=" hidden-xs showopacity glyphicon {{glyphicon}}"></span>';
        $html .= '</a>';
        $html .= '</div>';

        $modelCoreConfig = new CoreConfig();
        $servicesuseproject = $modelCoreConfig->getParamSpace("servicesuseproject", $id_space);
        if ($servicesuseproject == 1) {
            $htmlprojet = file_get_contents("Modules/services/View/Services/navbarproject.php");

            $htmlprojet = str_replace("{{id_space}}", $id_space, $htmlprojet);
            $htmlprojet = str_replace("{{Opened_projects}}", ServicesTranslator::Opened_projects($lang), $htmlprojet);
            $htmlprojet = str_replace("{{Closed_projects}}", ServicesTranslator::Closed_projects($lang), $htmlprojet);
            $htmlprojet = str_replace("{{Period_projects}}", ServicesTranslator::Period_projects($lang), $htmlprojet);


            $htmlprojet = str_replace("{{New_project}}", ServicesTranslator::New_project($lang), $htmlprojet);
            $htmlprojet = str_replace("{{Projects}}", strtoupper(ServicesTranslator::Projects($lang)), $htmlprojet);
            $htmlprojet = str_replace("{{origins}}", ServicesTranslator::servicesOrigin($lang), $htmlprojet);
            $htmlprojet = str_replace("{{visas}}", ServicesTranslator::servicesVisas($lang), $htmlprojet);
            $htmlprojet = str_replace("{{ganttopened}}", ServicesTranslator::GanttOpened($lang), $htmlprojet);
            $htmlprojet = str_replace("{{ganttperiod}}", ServicesTranslator::GanttPeriod($lang), $htmlprojet);
            
            $htmlprojet = str_replace("{{stock}}", strtoupper(ServicesTranslator::servicesStock($lang)), $htmlprojet);
            $htmlprojet = str_replace("{{cabinets}}", ServicesTranslator::Cabinets($lang), $htmlprojet);
            $htmlprojet = str_replace("{{shelfs}}", ServicesTranslator::Shelfs($lang), $htmlprojet);

            $html .= $htmlprojet;
        }

        $servicesusecommand = $modelCoreConfig->getParamSpace("servicesusecommand", $id_space);
        if ($servicesusecommand == 1) {

            $htmlOrder = file_get_contents("Modules/services/View/Services/navbarorder.php");

            $htmlOrder = str_replace("{{id_space}}", $id_space, $htmlOrder);
            $htmlOrder = str_replace("{{Opened_orders}}", ServicesTranslator::Opened_orders($lang), $htmlOrder);
            $htmlOrder = str_replace("{{Closed_orders}}", ServicesTranslator::Closed_orders($lang), $htmlOrder);
            $htmlOrder = str_replace("{{All_orders}}", ServicesTranslator::All_orders($lang), $htmlOrder);
            $htmlOrder = str_replace("{{New_orders}}", ServicesTranslator::New_orders($lang), $htmlOrder);
            $htmlOrder = str_replace("{{Orders}}", strtoupper(ServicesTranslator::Orders($lang)), $htmlOrder);

            $html .= $htmlOrder;
        }

        $servicesusestock = $modelCoreConfig->getParamSpace("servicesusestock", $id_space);
        if ($servicesusestock == 1) {
            $htmlStock = file_get_contents("Modules/services/View/Services/navbarstock.php");

            $htmlStock = str_replace("{{id_space}}", $id_space, $htmlStock);
            $htmlStock = str_replace("{{Stock}}", strtoupper(ServicesTranslator::Stock($lang)), $htmlStock);
            $htmlStock = str_replace("{{Stocks}}", ServicesTranslator::Stock($lang), $htmlStock);
            $htmlStock = str_replace("{{New_Purchase}}", ServicesTranslator::New_Purchase($lang), $htmlStock);
            $htmlStock = str_replace("{{Purchase}}", ServicesTranslator::Purchase($lang), $htmlStock);

            $html .= $htmlStock;
        }

        $htmlListing = file_get_contents("Modules/services/View/Services/navbarlisting.php");
        $htmlListing = str_replace("{{id_space}}", $id_space, $htmlListing);
        $htmlListing = str_replace("{{Listing}}", strtoupper(ServicesTranslator::Listing($lang)), $htmlListing);
        $htmlListing = str_replace("{{services}}", ServicesTranslator::services($lang), $htmlListing);

        $html .= $htmlListing;

        $html.= "</div>";

        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("services", $id_space);
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{color}}', $menuInfo['txtcolor'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', ServicesTranslator::Services($lang), $html);

        return $html;
    }

    public function getServiceTypeAction($id_space, $id_service) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelService = new SeService();
        $modelType = new SeServiceType();
        $serviceTypeName = $modelType->getType($modelService->getItemType($id_space, $id_service));
        $this->render(['data' => ['elements' => ServicesTranslator::ServicesTypes($serviceTypeName, $lang)]]);
    }

}
