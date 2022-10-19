<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Controller/ServicesController.php';

/**
 *
 * @author sprigent
 * Controller for services list page
 */
class ServiceslistingController extends ServicesController
{
    private $serviceModel;
    private $typeModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        //$this->checkAuthorizationMenu("services");
        $this->serviceModel = new SeService();
        $this->typeModel = new SeServiceType();
    }

    public function listingAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $data = $this->serviceModel->getAll($idSpace);

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
        $table->addLineEditButton("servicesedit/" . $idSpace);
        $table->addDeleteButton("servicesdelete/" . $idSpace);

        $tableHtml = $table->view($data, $headers);
        return $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "tableHtml" => $tableHtml,
            "data" => ['services' => $data]
        ));
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        if (!$id) {
            $value = array("name" => "", "description" => "", "display_order" => "", "type_id" => "");
        } else {
            $value = $this->serviceModel->getItem($idSpace, $id);
        }

        $form = new Form($this->request, "editserviceform");
        $form->addSeparator(ServicesTranslator::Edit_service($lang));

        $form->addText("name", CoreTranslator::Name($lang), true, $value["name"]);
        $form->addText("description", CoreTranslator::Description($lang), false, $value["description"]);
        $form->addNumber("display_order", CoreTranslator::Display_order($lang), false, $value["display_order"]);

        $modelTypes = new SeServiceType();
        $types = $modelTypes->getAllForSelect();

        $form->addSelect("type_id", CoreTranslator::type($lang), $types["names"], $types["ids"], $value["type_id"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesedit/" . $idSpace . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "serviceslisting/" . $idSpace);

        if ($form->check()) {
            $service_id = $this->serviceModel->setService(
                $id,
                $idSpace,
                $this->request->getParameter("name"),
                $this->request->getParameter("description"),
                $this->request->getParameter("display_order"),
                $this->request->getParameter("type_id")
            );

            return $this->redirect("serviceslisting/" . $idSpace, [], ['service' => ['id' => $service_id]]);
        }

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);

        $this->serviceModel->delete($idSpace, $id);
        $this->redirect("serviceslisting/" . $idSpace);
    }

    public function stockAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $data = $this->serviceModel->getAll($idSpace);
        $headers = array(
            "id" => "ID",
            "name" => CoreTranslator::name($lang),
            "quantity" => ServicesTranslator::Quantity($lang)
        );

        $table = new TableView();
        $table->setTitle(ServicesTranslator::Stock($lang), 3);

        $tableHtml = $table->view($data, $headers);

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "tableHtml" => $tableHtml));
    }
}
