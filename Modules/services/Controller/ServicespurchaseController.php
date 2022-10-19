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
class ServicespurchaseController extends ServicesController
{
    private $serviceModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        //$this->checkAuthorizationMenu("services");
        $this->serviceModel = new SePurchase();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        $data = $this->serviceModel->getForSpace($idSpace);
        //print_r($data);

        $headers = array(
            "id" => "ID",
            "comment" => CoreTranslator::Description($lang),
            "date" => CoreTranslator::Date($lang)
        );

        $table = new TableView();
        $table->setTitle(ServicesTranslator::Purchase($lang), 3);
        $table->addLineEditButton("servicespurchaseedit/" . $idSpace);
        $table->addDeleteButton("servicespurchasedelete/" . $idSpace, "id", "date");

        $tableHtml = $table->view($data, $headers);

        $this->render(array("id_space" => $idSpace, "lang" => $lang, "tableHtml" => $tableHtml));
    }

    public function editAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        $modelItem = new SePurchaseItem();
        if (!$id) {
            $value = array("comment" => "", "date" => date("Y-m-d", time()));
            $items = array("services" => array(), "quantities" => array());
        } else {
            $value = $this->serviceModel->getItem($idSpace, $id);
            $items = $modelItem->getForPurchase($idSpace, $id);
        }

        $modelServices = new SeService();
        $services = $modelServices->getForList($idSpace);

        $form = new Form($this->request, "editserviceform");
        $form->addSeparator(ServicesTranslator::New_Purchase($lang));

        $form->addText("comment", CoreTranslator::Description($lang), false, $value["comment"]);
        $form->addDate("date", CoreTranslator::Date($lang), false, $value["date"]);

        $formAdd = new FormAdd($this->request, "editserviceformadd");
        $formAdd->addSelect("services", ServicesTranslator::services($lang), $services["names"], $services["ids"], $items["services"]);
        $formAdd->addFloat("quantities", ServicesTranslator::Quantity($lang), $items["quantities"]);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($formAdd, ServicesTranslator::services($lang));
        $form->setValidationButton(CoreTranslator::Save($lang), "servicespurchaseedit/" . $idSpace . "/" . $id);
        $form->setCancelButton(CoreTranslator::Cancel($lang), "servicespurchase/" . $idSpace);

        if ($form->check()) {
            $id_purchase = $this->serviceModel->set($id, $this->request->getParameter("comment"), $idSpace, CoreTranslator::dateToEn($this->request->getParameter("date"), $lang));

            $servicesIds = $this->request->getParameter("services");
            $servicesQuantities = $this->request->getParameter("quantities");

            for ($i = 0; $i < count($servicesQuantities); $i++) {
                if (!$id) {
                    $qOld = 0;
                } else {
                    $qOld = $modelItem->getItemQuantity($idSpace, $servicesIds[$i], $id)['quantity'];
                }
                $qDelta = $servicesQuantities[$i] - $qOld;
                $modelServices->editquantity($idSpace, $servicesIds[$i], $qDelta, "add");
                $modelItem->set($idSpace, $id_purchase, $servicesIds[$i], $servicesQuantities[$i], "");
            }

            return $this->redirect("servicespurchase/" . $idSpace, [], ['purchase' => ['id' => $id_purchase]]);
        }

        return $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang),
            "data" => ['purchase' => $value, 'items' => $items]
        ));
    }

    public function deleteAction($idSpace, $id)
    {
        $this->checkAuthorizationMenuSpace("services", $idSpace, $_SESSION["id_user"]);

        $this->serviceModel->delete($idSpace, $id);
        $this->redirect("services/" . $idSpace);
    }
}
