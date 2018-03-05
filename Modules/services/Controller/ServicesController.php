<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesController extends CoresecureController {

    private $serviceModel;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("services");
        $this->serviceModel = new SeService();
    }

    public function navbar($id_space) {

        $lang = $this->getLanguage();

        $html  = '<div class="col-xs-12" style="border: none; margin-top: 7px; padding-right: 0px; padding-left: 0px;">';
        $html .= '<div class="col-xs-12" style="height: 50px; padding-top: 15px; background-color:{{bgcolor}}; border-bottom: 1px solid #fff;">';
        $html .= '<a  style="background-color:{{bgcolor}}; color: #fff;" href=""> {{title}}'; 
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
            $htmlprojet = str_replace("{{Projects}}", ServicesTranslator::Projects($lang), $htmlprojet);
            $htmlprojet = str_replace("{{origins}}", ServicesTranslator::servicesOrigin($lang), $htmlprojet);
            $htmlprojet = str_replace("{{visas}}", ServicesTranslator::servicesVisas($lang), $htmlprojet);
            $htmlprojet = str_replace("{{ganttopened}}", ServicesTranslator::GanttOpened($lang), $htmlprojet);
            $htmlprojet = str_replace("{{ganttperiod}}", ServicesTranslator::GanttPeriod($lang), $htmlprojet);
            
            $htmlprojet = str_replace("{{stock}}", ServicesTranslator::servicesStock($lang), $htmlprojet);
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
            $htmlOrder = str_replace("{{Orders}}", ServicesTranslator::Orders($lang), $htmlOrder);

            $html .= $htmlOrder;
        }

        $servicesusestock = $modelCoreConfig->getParamSpace("servicesusestock", $id_space);
        if ($servicesusestock == 1) {
            $htmlStock = file_get_contents("Modules/services/View/Services/navbarstock.php");

            $htmlStock = str_replace("{{id_space}}", $id_space, $htmlStock);
            $htmlStock = str_replace("{{Stock}}", ServicesTranslator::Stock($lang), $htmlStock);
            $htmlStock = str_replace("{{New_Purchase}}", ServicesTranslator::New_Purchase($lang), $htmlStock);
            $htmlStock = str_replace("{{Purchase}}", ServicesTranslator::Purchase($lang), $htmlStock);

            $html .= $htmlStock;
        }

        $htmlListing = file_get_contents("Modules/services/View/Services/navbarlisting.php");

        $htmlListing = str_replace("{{id_space}}", $id_space, $htmlListing);
        $htmlListing = str_replace("{{Listing}}", ServicesTranslator::Listing($lang), $htmlListing);
        $htmlListing = str_replace("{{services}}", ServicesTranslator::services($lang), $htmlListing);

        $html .= $htmlListing;

        $html.= "</ul>";
        $html.= "   </ul>";
        $html.= "</div>";
        $html.= "</div>";
        $html.= "</nav>";

        $modelSpace = new CoreSpace();
        $menuInfo = $modelSpace->getSpaceMenuFromUrl("services", $id_space);
        $html = str_replace('{{bgcolor}}', $menuInfo['color'], $html);
        $html = str_replace('{{glyphicon}}', $menuInfo['icon'], $html);
        $html = str_replace('{{title}}', ServicesTranslator::Services($lang), $html);

        return $html;
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $modelCoreConfig = new CoreConfig();
        $servicesuseproject = $modelCoreConfig->getParamSpace("servicesuseproject", $id_space);
        if ($servicesuseproject == 1) {
            $this->redirect('servicesprojectsopened/' . $id_space);
            return;
        }
        $servicesusecommand = $modelCoreConfig->getParamSpace("servicesusecommand", $id_space);
        if ($servicesusecommand == 1) {
            $this->redirect('servicesorders/' . $id_space);
            return;
        }

        $this->redirect('serviceslisting/' . $id_space);
    }

    public function listingAction($id_space) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $data = $this->serviceModel->getAll($id_space);
        //print_r($data);

        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::type($lang),
            "description" => CoreTranslator::Description($lang),
            "type" => CoreTranslator::type($lang),
            "display_order" => CoreTranslator::Display_order($lang)
        );

        $table = new TableView();
        $table->setTitle(ServicesTranslator::services($lang), 3);
        $table->addLineEditButton("servicesedit/" . $id_space);
        $table->addDeleteButton("servicesdelete/" . $id_space);

        $tableHtml = $table->view($data, $headers);

        $this->render(array("id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableHtml));
    }

    public function editAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        if ($id == 0) {
            $value = array("name" => "", "description" => "", "display_order" => "", "type_id" => "");
        } else {
            $value = $this->serviceModel->getItem($id);
        }

        $form = new Form($this->request, "editserviceform");
        $form->addSeparator(ServicesTranslator::Edit_service($lang));

        $form->addText("name", CoreTranslator::Name($lang), true, $value["name"]);
        $form->addText("description", CoreTranslator::Description($lang), false, $value["description"]);
        $form->addNumber("display_order", CoreTranslator::Display_order($lang), false, $value["display_order"]);

        $modelTypes = new SeServiceType();
        $types = $modelTypes->getAllForSelect();

        $form->addSelect("type_id", CoreTranslator::type($lang), $types["names"], $types["ids"], $value["type_id"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "services/" . $id_space);

        if ($form->check()) {
            $this->serviceModel->setService($id, $id_space, $this->request->getParameter("name"), $this->request->getParameter("description"), $this->request->getParameter("display_order"), $this->request->getParameter("type_id")
            );

            $this->redirect("services/" . $id_space);
            return;
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $this->serviceModel->delete($id);
        $this->redirect("services/" . $id_space);
    }

    public function stockAction($id_space) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $data = $this->serviceModel->getAll($id_space);
        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::name($lang),
            "quantity" => ServicesTranslator::Quantity($lang)
        );

        $table = new TableView();
        $table->setTitle(ServicesTranslator::Stock($lang), 3);

        $tableHtml = $table->view($data, $headers);

        $this->render(array("id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableHtml));
    }

}
