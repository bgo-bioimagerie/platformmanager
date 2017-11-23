<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/breeding/Model/BreedingTranslator.php';
require_once 'Modules/breeding/Model/BrSale.php';
require_once 'Modules/breeding/Model/BrClient.php';
require_once 'Modules/breeding/Model/BrDeliveryMethod.php';
require_once 'Modules/breeding/Model/BrContactType.php';
require_once 'Modules/breeding/Model/BrSaleStatus.php';
require_once 'Modules/breeding/Model/BrSaleItem.php';
require_once 'Modules/breeding/Model/BrBatch.php';

/**
 * 
 * @author sprigent
 * Controller for the provider example of breeding module
 */
class BreedingsalesController extends CoresecureController {

    /**
     * model object
     */
    private $model;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->model = new BrSale ();
        $_SESSION["openedNav"] = "breeding";
    }

    public function newAction($id_space) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // data
        $modelClient = new BrClient();
        $clients = $modelClient->getForList($id_space);

        $modelDeliveryMethods = new BrDeliveryMethod();
        $deliveryMethods = $modelDeliveryMethods->getForList($id_space);

        $modelContactType = new BrContactType();
        $contactTypes = $modelContactType->getForList($id_space);

        // form
        $form = new Form($this->request, "newsaleform");
        $form->setTitle(BreedingTranslator::NewSale($lang));
        $form->addSelect("id_client", BreedingTranslator::Client($lang), $clients["names"], $clients["ids"]);
        $form->addSelect("id_delivery_method", BreedingTranslator::DeliveryMethod($lang), $deliveryMethods["names"], $deliveryMethods["ids"]);
        $form->addDate("delivery_expected", BreedingTranslator::DeliveryExpected($lang));
        $form->addSelect("id_contact_type", BreedingTranslator::ContactType($lang), $contactTypes["names"], $contactTypes["ids"]);
        $form->addTextArea("further_information", BreedingTranslator::FurtherInformations($lang));

        $form->setValidationButton(CoreTranslator::Next($lang), "brsalenew/" . $id_space);
        $form->setButtonsWidth(2, 9);

        if ($form->check()) {
            $id = $this->model->add(
                    $id_space, $_SESSION["id_user"], $form->getParameter('id_client'), $form->getParameter('id_delivery_method'), $form->getParameter('delivery_expected'), $form->getParameter('id_contact_type'), $form->getParameter('further_information')
            );
            $this->model->setStatus($id, 1);

            $this->redirect("brsaleedit/" . $id_space . "/" . $id);
            return;
        }

        // render the View
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'formHtml' => $form->getHtml($lang)
        ));
    }

    /**
     * Edit a provider form
     */
    public function editAction($id_space, $id) {
        
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // data
        $data = $this->model->get($id);
        
        //print_r($data);

        $modelClient = new BrClient();
        $clients = $modelClient->getForList($id_space);

        $modelDeliveryMethods = new BrDeliveryMethod();
        $deliveryMethods = $modelDeliveryMethods->getForList($id_space);

        $modelContactType = new BrContactType();
        $contactTypes = $modelContactType->getForList($id_space);

        $modelStatus = new BrSaleStatus();
        $status = $modelStatus->getForList($lang);

        // form
        $form = new Form($this->request, "newsaleform");
        $form->setTitle(BreedingTranslator::Tracking($lang));

        $form->addSelect("id_status", BreedingTranslator::Status($lang), $status["names"], $status["ids"], $data["id_status"]);
        $form->addText("purchase_order_num", BreedingTranslator::PurchaseOrderNumber($lang), $data["purchase_order_num"]);
        $form->addSelect("id_client", BreedingTranslator::Client($lang), $clients["names"], $clients["ids"], false, $data["id_client"]);
        $form->addSelect("id_delivery_method", BreedingTranslator::DeliveryMethod($lang), $deliveryMethods["names"], $deliveryMethods["ids"], $data["id_delivery_method"]);
        $form->addDate("delivery_expected", BreedingTranslator::DeliveryExpected($lang), false, CoreTranslator::dateFromEn($data["delivery_expected"], $lang));
        $form->addSelect("id_contact_type", BreedingTranslator::ContactType($lang), $contactTypes["names"], $contactTypes["ids"], $data["id_contact_type"]);
        $form->addText("cancel_reason", BreedingTranslator::CancelReason($lang), false, $data["cancel_reason"]);
        $form->addDate("cancel_date", BreedingTranslator::CancelDate($lang), false, CoreTranslator::dateFromEn($data["cancel_date"], $lang));
        $form->addTextArea("further_information", BreedingTranslator::FurtherInformations($lang), false, $data["further_information"]);

        $form->setValidationButton(CoreTranslator::Save($lang), "brsaleedit/" . $id_space . "/" . $id);
        $form->setButtonsWidth(2, 9);

        if ($form->check()) {
            
            $this->model->editInfo(
                    $id, $form->getParameter('id_status'), $form->getParameter('purchase_order_num'), 
                    $form->getParameter('id_delivery_method'), $form->getParameter('delivery_expected'), 
                    $form->getParameter('id_contact_type'), $form->getParameter('cancel_reason'), 
                    $form->getParameter('cancel_date'), $form->getParameter('further_information')
            );

            $this->redirect("brsaleedit/" . $id_space . "/" . $id);
            return;
             
        }

        // render the View
        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'id_sale' => $id,
            'activTab' => "tracking",
            'formHtml' => $form->getHtml($lang)
        ));
    }

    public function itemsAction($id_space, $id_sale) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // data
        $modelSaleItem = new BrSaleItem();
        $data = $modelSaleItem->getAll($id_sale);

        $modelBatch = new BrBatch();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["date"] = CoreTranslator::dateFromEn($data[$i]["date"], $lang);
            $data[$i]["batch"] = $modelBatch->getName( $data[$i]['id_batch'] );
        }

        $headers = array(
            "date" => CoreTranslator::Date($lang),
            "batch" => BreedingTranslator::Batch($lang),
            "requested_product" => BreedingTranslator::RequestedProduct($lang),
            "requested_quantity" => BreedingTranslator::RequestedQuantity($lang),
            "quantity" => BreedingTranslator::Quantity($lang),
            "comment" => BreedingTranslator::Comment($lang)
        );

        $table = new TableView();
        $table->addLineButton("brsaleitemedit/" . $id_space . "/" . $id_sale);
        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $id_space,
            "id_sale" => $id_sale,
            "lang" => $lang,
            'activTab' => "detail",
            "tableHtml" => $tableHtml
        ));
    }

    public function itemeditAction($id_space, $id_sale, $id) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // data
        $modelItem = new BrSaleItem();
        $data = $modelItem->get($id);
        
        $batchModels = new BrBatch();
        $batchs = $batchModels->getForList($id_space);

        // form
        $form = new Form($this->request, "itemeditform");
        $form->setTitle(BreedingTranslator::EditDetails($lang));
        $form->addDate("date", CoreTranslator::Date($lang), true, CoreTranslator::dateFromEn($data["date"], $lang));
        
        $form->addSelect("id_batch", BreedingTranslator::Batch($lang), $batchs["names"], $batchs["ids"], $data["id_batch"]);
        $form->addText("requested_product", BreedingTranslator::RequestedProduct($lang), true, $data["requested_product"]);
        $form->addText("requested_quantity", BreedingTranslator::RequestedQuantity($lang), true, $data["requested_quantity"]);
        $form->addText("quantity", BreedingTranslator::Quantity($lang), true, $data["quantity"]);
        $form->addTextArea("comment", BreedingTranslator::Comment($lang), false, $data["comment"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "brsaleitemedit/" . $id_space . "/" . $id_sale . "/" . $id);
        $form->setButtonsWidth(2, 9);

        if ($form->check()) {
            
            $id_batch = $form->getParameter("id_batch");
            $modelItem->set($id, $id_sale, CoreTranslator::dateToEn($form->getParameter("date"), $lang), $id_batch, $form->getParameter("requested_product"), $form->getParameter("requested_quantity"), $form->getParameter("quantity"), $form->getParameter("comment")
            );
            
            $modelBath = new BrBatch();
            $modelBath->updateQuantity($id_batch);
            
            $this->redirect("brsaleitems/" . $id_space . "/" . $id_sale);
        }

        $this->render(array(
            "id_space" => $id_space,
            "id_sale" => $id_sale,
            "lang" => $lang,
            'activTab' => "detail",
            "formHtml" => $form->getHtml($lang)
        ));
    }

    public function inprogressAction($id_space) {

        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // data
        $data = $this->model->getInProgress($id_space);

        $modelClient = new BrClient();
        $modelStatus = new BrSaleStatus();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["client"] = $modelClient->getName($data[$i]["id_client"]);
            $data[$i]["status"] = $modelStatus->getName($data[$i]["id_status"], $lang);
        }
        $headers = array(
            "status" => BreedingTranslator::Status($lang),
            "client" => BreedingTranslator::Client($lang),
            "delivery_expected" => BreedingTranslator::DeliveryExpected($lang)
        );

        // table
        $table = new TableView();
        $table->setTitle(BreedingTranslator::SalesInProgress($lang));
        $table->addLineEditButton("brsaleedit/" . $id_space);
        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableHtml
        ));
    }
    
    public function sentAction($id_space){
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // data
        $data = $this->model->getSent($id_space);

        $modelClient = new BrClient();
        $modelStatus = new BrSaleStatus();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["client"] = $modelClient->getName($data[$i]["id_client"]);
            $data[$i]["status"] = $modelStatus->getName($data[$i]["id_status"], $lang);
        }
        $headers = array(
            "status" => BreedingTranslator::Status($lang),
            "client" => BreedingTranslator::Client($lang),
            "delivery_expected" => BreedingTranslator::DeliveryExpected($lang)
        );

        // table
        $table = new TableView();
        $table->setTitle(BreedingTranslator::SalesSent($lang));
        $table->addLineEditButton("brsaleedit/" . $id_space);
        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableHtml
        ));
    }
    
    public function canceledAction($id_space){
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);
        // lang
        $lang = $this->getLanguage();

        // data
        $data = $this->model->getCanceled($id_space);

        $modelClient = new BrClient();
        $modelStatus = new BrSaleStatus();
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["client"] = $modelClient->getName($data[$i]["id_client"]);
            $data[$i]["status"] = $modelStatus->getName($data[$i]["id_status"], $lang);
        }
        $headers = array(
            "status" => BreedingTranslator::Status($lang),
            "client" => BreedingTranslator::Client($lang)
        );

        // table
        $table = new TableView();
        $table->setTitle(BreedingTranslator::SalesCanceled($lang));
        $table->addLineEditButton("brsaleedit/" . $id_space);
        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableHtml
        ));
    }

    /**
     * Remove a provider
     */
    public function deleteAction($id_space, $id) {
        // security
        $this->checkAuthorizationMenuSpace("breeding", $id_space, $_SESSION["id_user"]);

        // query to delete the provider
        $this->model->delete($id);

        // after the provider is deleted we redirect to the providers list page
        $this->redirect("brlossetypes/" . $id_space);
    }

}
