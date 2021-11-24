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
class ServiceslistingController extends CoresecureController {

    private $serviceModel;
    private $typeModel;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("services");
        $this->serviceModel = new SeService();
        $this->typeModel = new SeServiceType();
        $_SESSION["openedNav"] = "services";
    }

    public function listingAction($id_space) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $data = $this->serviceModel->getAll($id_space);

        // set types from services
        $typesArray = $this->typeModel->getTypes();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["type"] = ServicesTranslator::serviceTypes($typesArray[$data[$i]["type_id"]], $lang);
        }

        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::Name($lang),
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

        if (!$id) {
            $value = array("name" => "", "description" => "", "display_order" => "", "type_id" => "");
        } else {
            $value = $this->serviceModel->getItem($id_space ,$id);
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
        $form->setCancelButton(CoreTranslator::Cancel($lang), "serviceslisting/" . $id_space);

        if ($form->check()) {
            $this->serviceModel->setService(
                $id, $id_space,
                $this->request->getParameter("name"),
                $this->request->getParameter("description"),
                $this->request->getParameter("display_order"),
                $this->request->getParameter("type_id")
            );

            $this->redirect("serviceslisting/" . $id_space);
            return;
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $this->serviceModel->delete($id_space, $id);
        $this->redirect("serviceslisting/" . $id_space);
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
