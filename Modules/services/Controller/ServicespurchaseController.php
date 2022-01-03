<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SePurchase.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SePurchaseItem.php';
require_once 'Modules/services/Controller/ServicesController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicespurchaseController extends ServicesController {

    private $serviceModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        //$this->checkAuthorizationMenu("services");
        $this->serviceModel = new SePurchase();

    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        $data = $this->serviceModel->getForSpace($id_space);
        //print_r($data);

        $headers = array(
            "id" => "ID",
            "comment" => CoreTranslator::Description($lang),
            "date" => CoreTranslator::Date($lang)
        );

        $table = new TableView();
        $table->setTitle(ServicesTranslator::services($lang), 3);
        $table->addLineEditButton("servicespurchaseedit/" . $id_space);
        $table->addDeleteButton("servicespurchasedelete/" . $id_space, "id", "date");
        
        $tableHtml = $table->view($data, $headers);

        $this->render(array("id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableHtml));
    }

    public function editAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelItem = new SePurchaseItem();
        if (!$id) {
            $value = array("comment" => "", "date" => CoreTranslator::dateFromEn(date("Y-m-d", time()), $lang));
            $items = array("services" => array(), "quantities" => array());
        } else {
            $value = $this->serviceModel->getItem($id_space, $id);
            $value["date"] = CoreTranslator::dateFromEn($value["date"], $lang);
            $items = $modelItem->getForPurchase($id_space, $id);
        }
        
        $modelServices = new SeService();
        $services = $modelServices->getForList($id_space);

        $form = new Form($this->request, "editserviceform");
        $form->addSeparator(ServicesTranslator::Edit_service($lang));

        $form->addText("comment", CoreTranslator::Description($lang), false, $value["comment"]);
        $form->addDate("date", CoreTranslator::Date($lang), false, $value["date"]);

        $formAdd = new FormAdd($this->request, "editserviceformadd");
        $formAdd->addSelect("services", ServicesTranslator::services($lang), $services["names"], $services["ids"], $items["services"]);
        $formAdd->addNumber("quantities", ServicesTranslator::Quantity($lang), $items["quantities"]);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($formAdd, ServicesTranslator::services($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "servicespurchaseedit/" . $id_space . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "servicespurchase/" . $id_space);

        if ($form->check()) {

            $id_purchase = $this->serviceModel->set($id, $this->request->getParameter("comment"), $id_space, CoreTranslator::dateToEn($this->request->getParameter("date"), $lang));

            $servicesIds = $this->request->getParameter("services");
            $servicesQuantities = $this->request->getParameter("quantities");

            for ($i = 0; $i < count($servicesQuantities); $i++) {
                if (!$id){
                   $qOld = 0; 
                }
                else{
                    $qOld = $modelItem->getItemQuantity($id_space, $servicesIds[$i], $id);
                }
                $qDelta = $servicesQuantities[$i] - $qOld[0];
                $modelServices->editquantity($id_space, $servicesIds[$i], $qDelta, "add");
                $modelItem->set($id_space, $id_purchase, $servicesIds[$i], $servicesQuantities[$i], "");
            }

            $this->redirect("servicespurchase/" . $id_space);
            return;
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang, "formHtml" => $form->getHtml($lang)));
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("services", $id_space, $_SESSION["id_user"]);

        $this->serviceModel->delete($id_space, $id);
        $this->redirect("services/" . $id_space);
    }

}
