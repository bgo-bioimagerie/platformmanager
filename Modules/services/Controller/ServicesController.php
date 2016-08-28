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
    public function __construct() {
        parent::__construct();
        //$this->checkAuthorizationMenu("services");
        $this->serviceModel = new SeService();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

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
            $this->serviceModel->setService($id, $this->request->getParameter("id_space"), $this->request->getParameter("name"), $this->request->getParameter("description"), $this->request->getParameter("display_order"), $this->request->getParameter("type_id")
            );

            $this->redirect("services");
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
