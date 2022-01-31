<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeOrigin.php';
require_once 'Modules/services/Controller/ServicesController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesoriginsController extends ServicesController {

    private $originModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        //$this->checkAuthorizationMenu("services");
        $this->originModel = new SeOrigin();
    }

    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $data = $this->originModel->getAll($id_space);
        //print_r($data);

        $headers = array(
            "name" => CoreTranslator::type($lang)
        );

        $table = new TableView();
        $table->setTitle(ServicesTranslator::servicesOrigin($lang), 3);
        $table->addLineEditButton("serviceoriginedit/" . $id_space);
        $table->addDeleteButton("serviceorigindelete/" . $id_space);

        $tableHtml = $table->view($data, $headers);

        $this->render(array("id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableHtml));
    }

    public function editAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        if (!$id) {
            $value = array("name" => "", "display_order" => 1);
        } else {
            $value = $this->originModel->get($id_space, $id);
        }

        $form = new Form($this->request, "editserviceform");
        $form->addSeparator(ServicesTranslator::Edit_Origin($lang));

        $form->addText("name", CoreTranslator::Name($lang), true, $value["name"]);
        $form->addNumber("display_order", CoreTranslator::Display_order($lang), false, $value["display_order"]);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "serviceoriginedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "servicesorigins/" . $id_space);

        if ($form->check()) {
            $origin_id = $this->originModel->set($id, 
                    $this->request->getParameter("name"), 
                    $this->request->getParameter("display_order"), $id_space);
            
            return $this->redirect("servicesorigins/" . $id_space, [], ['origin' => ['id' => $origin_id]]);
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $this->originModel->delete($id_space, $id);
        $this->redirect("servicesorigins/" . $id_space);
    }

   

}
