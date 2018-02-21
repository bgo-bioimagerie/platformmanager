<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/estore/Model/EstoreTranslator.php';
require_once 'Modules/estore/Model/EsSale.php';
require_once 'Modules/estore/Model/EsProductCategory.php';



require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/estore/Model/EsContactType.php';
require_once 'Modules/estore/Model/EsSaleEnteredItem.php';
require_once 'Modules/estore/Model/EsSaleHistory.php';
require_once 'Modules/estore/Model/EsSaleStatus.php';
require_once 'Modules/estore/Model/EsSaleItem.php';
require_once 'Modules/estore/Model/EsDeliveryMethod.php';

require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/breeding/Model/BrBatch.php';
require_once 'Modules/estore/Model/EsPrice.php';


/**
 * 
 * @author sprigent
 * Controller for the provider example of estore module
 */
class EstoresaleController extends CoresecureController {

    /**
     * User model object
     */
    private $modelSales;
    private $modelSaleHistory;

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $this->modelSales = new EsSale();
        $this->modelSaleHistory = new EsSaleHistory();
        $_SESSION["openedNav"] = "estore";
    }

    protected function getUserRole($id_space) {
        $id_user = $_SESSION["id_user"];
        $modelSpace = new CoreSpace();
        return $modelSpace->getUserSpaceRole($id_space, $id_user);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     * Page showing a table containing all the providers in the database
     */
    public function newAction($id_space) {

        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);


        if (!$this->request->isParameter("products")) {
            echo json_encode(array("status" => "error", "message" => "Cannot find the products"));
            return;
        }

        // create the sale with default values
        $id_client = 0;
        $date_expected = "0000-00-00";
        $id_contact_type = 0;
        $further_information = "";
        $id_sale = $this->modelSales->setEntered(0, $id_space, $id_client, $date_expected, $id_contact_type, $further_information);

        // create the items
        $modelEnteredItems = new EsSaleEnteredItem();
        $products = $this->request->getParameter("products");

        foreach ($products as $p) {

            $id_product = $p["id"];
            $quantity = $p["quantity"];//*$p["price"];
            $modelEnteredItems->set(0, $id_sale, $id_product, $quantity);
        }

        // render the View
        //echo json_encode($products); 
        $redirectUrl = "essaleenterededit/" . $id_space . "/" . $id_sale;
        echo json_encode(array("status" => "success", "redirect" => $redirectUrl));
    }

    public function enterededitAction($id_space, $id) {

        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $saleInfo = $this->modelSales->get($id);

        $modelClients = new ClClient();
        $id_user = $_SESSION["id_user"];
        $modelSpace = new CoreSpace();
        $role = $modelSpace->getUserSpaceRole($id_space, $id_user);
        if ($role > 2) {
            $clients = $modelClients->getForList($id_space);
        } else {
            $clients = $modelClients->getForListByUser($id_space, $id_user);
        }
        $modelContact = new EsContactType();
        $contactTypes = $modelContact->getForList($id_space);

        $form = new Form($this->request, "essalesenterededitform");
        $form->addSelect("id_client", EstoreTranslator::ClientAccount($lang), $clients["names"], $clients["ids"], $saleInfo["id_client"]);
        $form->addDate("date_expected", EstoreTranslator::DateExpected($lang), true, CoreTranslator::dateFromEn($saleInfo["date_expected"], $lang));
        $form->addSelect("id_contact_type", EstoreTranslator::ContactType($lang), $contactTypes["names"], $contactTypes["ids"], $saleInfo["id_contact_type"]);
        $form->addTextArea("further_information", EstoreTranslator::FurtherInformation($lang), false, $saleInfo["further_information"]);
        $form->setValidationButton(CoreTranslator::Save($lang), "essaleenterededit/" . $id_space . "/" . $id);
        $form->setButtonsWidth(2, 8);

        if ($form->check()) {

            $this->modelSales->setEntered($id, $id_space, $form->getParameter("id_client"), CoreTranslator::dateToEn($form->getParameter("date_expected"), $lang), $form->getParameter("id_contact_type"), $form->getParameter("further_information")
            );

            $modelSaleHistory = new EsSaleHistory();
            $modelSaleHistory->set($id, EsSaleStatus::$Entered, $_SESSION["id_user"], date('Y-m-d'), time());
            $this->modelSales->updateStatus($id);


            $_SESSION["message"] = EstoreTranslator::Sale_entered_saved($lang);
            $this->redirect("essaleenterededit/" . $id_space . "/" . $id);
            return;
        }

        // sale items
        $modelItems = new EsSaleEnteredItem();
        $items = $modelItems->getitemsDesc($id_space, $id);

        $table = new TableView();
        $headers = array(
            "product" => EstoreTranslator::Product($lang),
            "quantity" => EstoreTranslator::Quantity($lang)
        );
        $tableHtml = $table->view($items, $headers);

        $this->render(array(
            "id_sale" => $id,
            "id_space" => $id_space,
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang),
            "tableHtml" => $tableHtml,
            'salestatus' => 0,
        ));
    }

    public function enteredAction($id_space) {

        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$Entered);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::EnteredSales($lang));
        $table->addLineEditButton("essaleenteredadminedit/" . $id_space);
        $headers = array(
            "number" => EstoreTranslator::ID($lang),
            "date_expected" => EstoreTranslator::DateExpected($lang),
            "client" => EstoreTranslator::ClientAccount($lang)
        );
        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableHtml
        ));
    }

    public function enteredadmineditAction($id_space, $id) {

        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelClient = new ClClient();
        $modelContact = new EsContactType();
        $saleInfo = $this->modelSales->get($id);
        $saleInfo["client"] = $modelClient->getName($saleInfo["id_client"]);
        $saleInfo["contacttype"] = $modelContact->getName($saleInfo["id_contact_type"]);

        $modelItems = new EsSaleEnteredItem();
        $items = $modelItems->getitemsDesc($id_space, $id);

        $modelHistory = new EsSaleHistory();
        $history = $modelHistory->getEntered($id);

        // table
        $table = new TableView();
        $headers = array(
            "product" => EstoreTranslator::Product($lang),
            "quantity" => EstoreTranslator::Quantity($lang)
        );
        $tableHtml = $table->view($items, $headers);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            'id_sale' => $saleInfo["id"],
            'salestatus' => $saleInfo["id_status"],
            "saleInfo" => $saleInfo,
            "items" => $items,
            "tableHtml" => $tableHtml,
            "history" => $history
        ));
    }
    
    
    public function feasibilityAction($id_space, $id_sale){
        
    }
    
    
    
    
    
    
    
    
    
    
    

    public function inprogressAction($id_space, $id_sale) {
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // extract data from database
        $modelBatch = new BrBatch();
        $batchs = $modelBatch->getForList($id_space);

        $modelSaleItems = new EsSaleItem();
        $items = $modelSaleItems->getitems($id_sale);
        $ids = array();
        $id_batchs = array();
        $quantities = array();
        foreach ($items as $it) {
            $ids[] = $it['id'];
            $id_batchs[] = $it['id_batch'];
            $quantities[] = $it['quantity'];
        }

        $sale = $this->modelSales->get($id_sale);

        // build form
        $form = new Form($this->request, "esaleinprogressForm");
        $form->setTitle(EstoreTranslator::Sale($lang) . " #" . $sale['id'] . " : " . EstoreTranslator::InProgress($lang));
        $formAdd = new FormAdd($this->request, "esaleinprogressFromAdd");
        $formAdd->addHidden("id", $ids);
        $formAdd->addSelect("id_batch", EstoreTranslator::Batch($lang), $batchs["names"], $batchs["ids"], $id_batchs);
        $formAdd->addNumber("quantity", EstoreTranslator::Quantity($lang), $quantities);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        
        $form->setFormAdd($formAdd, EstoreTranslator::Items($lang));
        $form->addDate("date_validated", EstoreTranslator::DateValidated($lang), true, CoreTranslator::dateFromEn($sale["date_validated"], $lang) );
        $form->setValidationButton(EstoreTranslator::Next($lang), "essaleinprogress/" . $id_space . "/" . $id_sale);
        $form->setButtonsWidth(2, 9);
        
        if ($form->check()) {
            $this->modelSales->setInProgress($id_sale, CoreTranslator::dateToEn($form->getParameter("date_validated"), $lang) );
            $nids = $form->getParameter("id");
            $nid_batch = $form->getParameter("id_batch");
            $nquantities = $form->getParameter("quantity");
            for ($i = 0; $i < count($nids); $i++) {
                $modelSaleItems->set($nids[$i], $id_sale, $nid_batch[$i], $nquantities[$i]);
            }
            
            $this->modelSaleHistory->set($id_sale, EsSaleStatus::$InProgress, $_SESSION["id_user"], date('Y-m-d', time()) );
            $this->modelSales->updateStatus($id_sale);
            
            $_SESSION["message"] = EstoreTranslator::Data_has_been_saved($lang);
            $this->redirect("esalequote/" . $id_space . "/" . $id_sale);
            return;
        }

        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'id_sale' => $sale["id"],
            'salestatus' => $sale["id_status"],
            'formHtml' => $form->getHtml($lang),
        ));
    }

    public function quoteAction($id_space, $id_sale) {
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $sale = $this->modelSales->get($id_sale);

        $form = new Form($this->request, "esalequoteActionForm");
        $form->setTitle(EstoreTranslator::Sale($lang) . " #" . $sale['id'] . " : " . EstoreTranslator::Quoted($lang) );
        $form->addText("quote_packing_price", EstoreTranslator::PackingPrice($lang), false, $sale["quote_packing_price"], true);
        $form->addText("quote_delivery_price", EstoreTranslator::DeliveryPrice($lang), false, $sale["quote_delivery_price"], true);
        $form->setValidationButton(EstoreTranslator::Next($lang), "esalequote/" . $id_space . "/" . $id_sale);
        $form->setButtonsWidth(2, 9);
        
        if ($form->check()) {
            $this->modelSales->setQuote($id_sale, $form->getParameter("quote_packing_price"), $form->getParameter("quote_delivery_price")
            );
            
            $this->modelSaleHistory->set($id_sale, EsSaleStatus::$Quoted, $_SESSION["id_user"], date('Y-m-d', time()) );
            $this->modelSales->updateStatus($id_sale);
            
            $this->redirect("esaledelivery/" . $id_space . "/" . $id_sale);
            return;
        }

        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'id_sale' => $sale["id"],
            'salestatus' => $sale["id_status"],
            'formHtml' => $form->getHtml($lang),
        ));
    }

    public function deliveryAction($id_space, $id_sale) {
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // get data
        $sale = $this->modelSales->get($id_sale);

        $modelDelivery = new EsDeliveryMethod();
        $esDelivery = $modelDelivery->getForList($id_space);

        // generate form
        $form = new Form($this->request, "deliveryActionForm");
        $form->setTitle(EstoreTranslator::Sale($lang) . " #" . $sale['id'] . " : " . EstoreTranslator::Deliver($lang));
        $form->addText("purchase_order_num", EstoreTranslator::PurchaseOderNum($lang), true, $sale["purchase_order_num"], true);
        $form->addSelectMandatory("id_delivery_method", EstoreTranslator::Delivery($lang), $esDelivery["names"], $esDelivery["ids"], $sale["id_delivery_method"]);
        $form->addDate("date_delivery", EstoreTranslator::DateDeliveryExpected($lang), true, CoreTranslator::dateFromEn( $sale["date_delivery"], $lang) );
        $form->setValidationButton(CoreTranslator::Save($lang), "esaledelivery/".$id_space."/".$id_sale);
        
        $form->setButtonsWidth(2, 9);    
        
        if ($form->check()) {

            $this->modelSales->setSent($id_sale, 
                    $form->getParameter("purchase_order_num"), 
                    $form->getParameter("id_delivery_method"), 
                    CoreTranslator::dateToEn($form->getParameter("date_delivery"), $lang) 
            );
            $this->modelSaleHistory->set($id_sale, EsSaleStatus::$Sent, $_SESSION["id_user"], date('Y-m-d', time()) );
            $this->modelSales->updateStatus($id_sale);
            
            $this->redirect("esaledelivery/" . $id_space . "/" . $id_sale);
            return;
        }

        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'id_sale' => $sale["id"],
            'salestatus' => $sale["id_status"],
            'formHtml' => $form->getHtml($lang),
        ));
    }
    
    public function deliverypdfAction($id_space, $id_sale){
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        // data
        $sale = $this->modelSales->get($id_sale);
        
        $modelDelivery = new EsDeliveryMethod();
        $deliveryName = $modelDelivery->getName($sale["id_delivery_method"]);
        
        $modelClient = new ClClient();
        $client = $modelClient->get($sale["id_client"]);
        
        $history = $this->modelSaleHistory->getDelivery($id_sale);
        $modelItems = new EsSaleItem();
        $items = $modelItems->getitems($id_sale);
        
        $table = "<table class=\"table\" style=\"width: 100%;\" font-site: 14px; font-family: times;\">
            <thead>
                    <tr>
                        <th style=\"width: 25%\">" . "<i>Code Produit</i>" . "</th>
                        <th style=\"width: 25%\">" . "<i>Nom du produit</i>" . "</th>
                        <th style=\"width: 25%\">" . "<i>Lot</i>" . "</th>
                        <th style=\"width: 25%\">" . "<i>Unités</i>" . "</th>
                    </tr>
            </thead>        
                
        ";
        $table .= "<tbody>";
        $modelBatch = new BrBatch();
        $modelProduct = new EsProduct($id_space);
        $modelProductCategory = new EsProductCategory($id_space);
        foreach($items as $item){
            
            $batchName = $modelBatch->getName($item["id_batch"]);
            $batchInfo = $modelBatch->get($item["id_batch"]);
            $productInfo = $modelProduct->get($batchInfo["id_product"]);
            
            $category = $modelProductCategory->getName($productInfo["id"]);
            
            $table .= "<tr>";
            $table .= "<td style=\"width: 25%; text-align: left; \">" . $category . "</td>";
            $table .= "<td style=\"width: 25%; \">" . $productInfo["name"] . "</td>";
            $table .= "<td style=\"width: 35%; text-align: left; \">" . $batchName . " </td>";
            $table .= "<td style=\"width: 15%; text-align: left; \">" . $item["quantity"] . "</td>";
            $table .= "</tr>";
        }
        $table .= "</tbody></table>";
        
        // render
        ob_start();
        include('data/estore/delivery/'.$id_space.'/template.php');
        $content = ob_get_clean();
        
        // convert in PDF
        require_once('externals/html2pdf/vendor/autoload.php');
        try {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr');
            //$html2pdf->setModeDebug();
            $html2pdf->setDefaultFont('Arial');
            //$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
            $html2pdf->writeHTML($content);
            //echo "name = " . $unit . "_" . $resp . " " . $number . '.pdf' . "<br/>"; 
            $html2pdf->Output('bon_de_livraison.pdf');
            return;
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
        
    }

    public function invoiceAction($id_space, $id_sale) {
        
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        // extract data from database
        $sale = $this->modelSales->get($id_sale);
        
        
        $modelBatch = new BrBatch();
        $batchs = $modelBatch->getForList($id_space);

        $modelPrice = new EsPrice();
        $modelClient = new ClClient();
        
        $modelSaleItems = new EsSaleItem();
        $items = $modelSaleItems->getitems($id_sale);
        $ids = array();
        $id_batchs = array();
        $quantities = array();
        $prices = array();
        $pricest = array();
        foreach ($items as $it) {
            $ids[] = $it['id'];
            $id_batchs[] = $it['id_batch'];
            $quantities[] = $it['quantity'];
            
            $id_product = $modelBatch->getProductID($it['id_batch']);
            
            $id_pricing = $modelClient->getPricingID($sale["id_client"]);
            $prices[] = $modelPrice->getPrice($id_product, $id_pricing);
            $pricest[] = $it['quantity']*$modelPrice->getPrice($id_product, $id_pricing);
        }

        

        // build form
        $form = new Form($this->request, "esaleinprogressForm");
        $form->setTitle(EstoreTranslator::Sale($lang) . " #" . $sale['id'] . " : " . EstoreTranslator::InProgress($lang));
        $formAdd = new FormAdd($this->request, "esaleinprogressFromAdd");
        $formAdd->addHidden("id", $ids);
        $formAdd->addSelect("id_batch", EstoreTranslator::Batch($lang), $batchs["names"], $batchs["ids"], $id_batchs);
        $formAdd->addNumber("quantity", EstoreTranslator::Quantity($lang), $quantities);
        $formAdd->addText("price", EstoreTranslator::UnitPrice($lang), $prices);
        $formAdd->addText("pricet", EstoreTranslator::Prices($lang), $pricest);
        $formAdd->setButtonsVisible(false);
        //$formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        
        $form->setFormAdd($formAdd, EstoreTranslator::Items($lang));
        $form->addText("packing_price", EstoreTranslator::PackingPrice($lang), false, $sale["packing_price"]);
        $form->addText("delivery_price", EstoreTranslator::DeliveryPrice($lang), false, $sale["delivery_price"]);
        $form->addText("discount", EstoreTranslator::Discount($lang), false, $sale["discount"]);
        $form->addSelect("vat", EstoreTranslator::VAT($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $sale["vat"]);
        $form->addText("total_ht", EstoreTranslator::TotalHT($lang), false, $sale["total_ht"], false);
        $form->addText("total_ttc", EstoreTranslator::TotalTTC($lang), false, $sale["total_ttc"], false);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "esaleinvoice/" . $id_space . "/" . $id_sale);
        $form->setColumnsWidth(3, 9);
        $form->setButtonsWidth(2, 9);
        
        if ($form->check()) {
            
            // get form data
            $packing_price = $form->getParameter("packing_price");
            $delivery_price = $form->getParameter("delivery_price");
            $discount = $form->getParameter("discount");
            
            $nids = $form->getParameter("id");
            $nquantities = $form->getParameter("quantity");
            $nprices = $form->getParameter("price");
           
            // get all prices
            
            $total_ht = 0;
            for ($i = 0; $i < count($nids); $i++) {
                $total_ht += $nquantities[$i]*$nprices[$i];
            }
            
            //echo "total_ht calculated = " . $total_ht . "<br/>";
            
            $total_ht += $packing_price + $delivery_price; 
            //echo "total_ht calculated = " . $total_ht . "<br/>";
            $total_ht = $total_ht - $total_ht*$discount/100;
            $total_ttc = $total_ht * 1.2;
            
            $this->modelSales->setSold($id_sale, 
                    $packing_price, 
                    $delivery_price, 
                    $discount, 
                    $total_ht, 
                    $total_ttc);
            
            $this->modelSaleHistory->set($id_sale, EsSaleStatus::$Sold, $_SESSION["id_user"], date('Y-m-d', time()) );
            $this->modelSales->updateStatus($id_sale);
            
            $_SESSION["message"] = EstoreTranslator::Data_has_been_saved($lang);
            $this->redirect("esaleinvoice/" . $id_space . "/" . $id_sale);
            return;
        }

        $this->render(array(
            'id_space' => $id_space,
            'lang' => $lang,
            'id_sale' => $sale["id"],
            'salestatus' => $sale["id_status"],
            'formHtml' => $form->getHtml($lang),
        ));
    }
    
    public function invoicepdfAction($id_space, $id_sale){
        
    }
    
    public function inprogresslistAction($id_space){
                // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$InProgress);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::SalesInProgress($lang));
        $table->addLineEditButton("essaleinprogress/" . $id_space);
        $headers = array(
            "number" => EstoreTranslator::ID($lang),
            "date_expected" => EstoreTranslator::DateExpected($lang),
            "client" => EstoreTranslator::ClientAccount($lang)
        );
        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableHtml
        ));
    }

    public function quotedlistAction($id_space){
        
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$Quoted);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::SalesQuoted($lang));
        $table->addLineEditButton("esalequote/" . $id_space);
        $headers = array(
            "number" => EstoreTranslator::ID($lang),
            "date_expected" => EstoreTranslator::DateExpected($lang),
            "client" => EstoreTranslator::ClientAccount($lang)
        );
        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableHtml
        ));
    }
    
    public function sentlistAction($id_space){
        
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$Sent);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::SalesSent($lang));
        $table->addLineEditButton("esaledelivery/" . $id_space);
        $headers = array(
            "number" => EstoreTranslator::ID($lang),
            "date_expected" => EstoreTranslator::DateExpected($lang),
            "client" => EstoreTranslator::ClientAccount($lang)
        );
        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableHtml
        ));
    }
    
    public function canceledlistAction($id_space){
        
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$Canceled);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::SalesCanceled($lang));
        $table->addLineEditButton("essaleenteredadminedit/" . $id_space);
        $headers = array(
            "number" => EstoreTranslator::ID($lang),
            "date_expected" => EstoreTranslator::DateExpected($lang),
            "client" => EstoreTranslator::ClientAccount($lang)
        );
        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableHtml
        ));
    }
    
    public function archivelistAction($id_space){
        
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$Sold);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::SalesArchives($lang));
        $table->addLineEditButton("esaleinvoice/" . $id_space);
        $headers = array(
            "number" => EstoreTranslator::ID($lang),
            "date_expected" => EstoreTranslator::DateExpected($lang),
            "client" => EstoreTranslator::ClientAccount($lang)
        );
        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableHtml
        ));
    }
    
}
