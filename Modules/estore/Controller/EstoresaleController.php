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
require_once 'Modules/estore/Model/EsSaleItemInvoice.php';


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

    
    public function enteredadmineditlistAction($id_space){
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$Entered);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::Entered($lang));
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
    
    
    public function feasibilitylistAction($id_space){
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$Feasibility);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::Feasibility($lang));
        $table->addLineEditButton("essalefeasibility/" . $id_space);
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
    
    public function feasibilityAction($id_space, $id_sale){
        
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
        $form->setTitle(EstoreTranslator::Sale($lang) . " #" . $sale['id'] . " : " . EstoreTranslator::Feasibility($lang));
        $formAdd = new FormAdd($this->request, "esaleinprogressFromAdd");
        $formAdd->addHidden("id", $ids);
        $formAdd->addSelect("id_batch", EstoreTranslator::Batch($lang), $batchs["names"], $batchs["ids"], $id_batchs);
        $formAdd->addNumber("quantity", EstoreTranslator::Quantity($lang), $quantities);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        
        $form->setFormAdd($formAdd, EstoreTranslator::Items($lang));
        $form->addDate("date_validated", EstoreTranslator::DateValidated($lang), true, CoreTranslator::dateFromEn($sale["date_validated"], $lang) );
        $form->setValidationButton(EstoreTranslator::Next($lang), "essalefeasibility/" . $id_space . "/" . $id_sale);
        $form->setButtonsWidth(2, 9);
        
        if ($form->check()) {
            
            $this->modelSales->setInProgress($id_sale, CoreTranslator::dateToEn($form->getParameter("date_validated"), $lang) );
            $nids = $form->getParameter("id");
            $nid_batch = $form->getParameter("id_batch");
            $nquantities = $form->getParameter("quantity");
            for ($i = 0; $i < count($nids); $i++) {
                $modelSaleItems->set($nids[$i], $id_sale, $nid_batch[$i], $nquantities[$i]);
            }
            
            $this->modelSaleHistory->set($id_sale, EsSaleStatus::$Feasibility, $_SESSION["id_user"], date('Y-m-d', time()) );
            $this->modelSales->updateStatus($id_sale);
            
            $_SESSION["message"] = EstoreTranslator::Data_has_been_saved($lang);
            $this->redirect("essaletodoquote/" . $id_space . "/" . $id_sale);
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
    
    public function todoquotelistAction($id_space){
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$TodoQuote);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::TodoQuote($lang));
        $table->addLineEditButton("essaletodoquote/" . $id_space);
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
    
    public function todoquoteAction($id_space, $id_sale){
        
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        // data
        $sale = $this->modelSales->get($id_sale);
        
        $modelBatch = new BrBatch();
        $batchs = $modelBatch->getForList($id_space);

        $modelSaleItems = new EsSaleItem();
        $modelPrice = new EsPrice();
        
        $modelClient = new ClClient();
        $id_pricing = $modelClient->getPricingID( $sale["id_client"] );
        
        $items = $modelSaleItems->getitems($id_sale);
        $ids = array();
        $id_batchs = array();
        $quantities = array();
        $prices = array();
        foreach ($items as $it) {
            $ids[] = $it['id'];
            $id_batchs[] = $it['id_batch'];
            $quantities[] = $it['quantity'];
            if ( $it['price'] == -1 ){
                $prices[] = $modelPrice->getPrice($modelBatch->getProductID( $it['id_batch'] ), $id_pricing);
            }
            else{
                $prices[] = $it['price'];
            }
        }
        
        // form
        $form = new Form($this->request, "esaletodoquoteForm");
        $form->setTitle(EstoreTranslator::Sale($lang) . " #" . $sale['id'] . " : " . EstoreTranslator::TodoQuote($lang));
        $formAdd = new FormAdd($this->request, "esaleinprogressFromAdd");
        $formAdd->addHidden("id", $ids);
        $formAdd->addSelect("id_batch", EstoreTranslator::Batch($lang), $batchs["names"], $batchs["ids"], $id_batchs);
        $formAdd->addNumber("quantity", EstoreTranslator::Quantity($lang), $quantities);
        $formAdd->addText("prices", EstoreTranslator::UnitPrice($lang), $prices);
        $formAdd->setButtonsVisible(false);
        
        $form->setFormAdd($formAdd, EstoreTranslator::Items($lang));
        $form->addDate("quote_delivery_date", EstoreTranslator::DateDeliveryExpected($lang), true, CoreTranslator::dateFromEn($sale["quote_delivery_date"], $lang));
        $form->addText("quote_delivery_price", EstoreTranslator::DeliveryPrice($lang), true, $sale["quote_delivery_price"] );
        //$form->addText("quote_totalht", EstoreTranslator::DeliveryPrice($lang), true, $sale["quote_totalht"] );
        
        $form->addExternalButton("PDF", "essalequotepdf/" . $id_space . "/" . $id_sale, "danger");
        $form->addExternalButton(CoreTranslator::Next($lang), "essalequotesent/" . $id_space . "/" . $id_sale, "default");
        $form->setValidationButton(EstoreTranslator::Save($lang), "essaletodoquote/" . $id_space . "/" . $id_sale);
        $form->setButtonsWidth(4, 8);
        
        if ($form->check()) {
            
            $totalht = 0;
            $nids  = $form->getParameter("id");
            $nid_batch = $form->getParameter("id_batch");
            $nquantities = $form->getParameter("quantity");
            $nprices = $form->getParameter("prices");
            
            $this->modelSales->setTodoQuote($id_sale, CoreTranslator::dateToEn($form->getParameter("quote_delivery_date"), $lang), $form->getParameter("quote_delivery_price"));
            for ($i = 0; $i < count($nids); $i++) {
                $modelSaleItems->set($nids[$i], $id_sale, $nid_batch[$i], $nquantities[$i], $nprices[$i]);
                $totalht += $nquantities[$i]*$nprices[$i];
            }
            
            $this->modelSaleHistory->set($id_sale, EsSaleStatus::$TodoQuote, $_SESSION["id_user"], date('Y-m-d', time()) );
            $this->modelSales->updateStatus($id_sale);
            
            $_SESSION["message"] = EstoreTranslator::Data_has_been_saved($lang);
            $this->redirect("essaletodoquote/" . $id_space . "/" . $id_sale);
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
    
    public function todoquotepdfAction($id_space, $id_sale){
        
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
                        <th style=\"width: 25%\">" . "<i>Désignation</i>" . "</th>
                        <th style=\"width: 25%\">" . "<i>Quantité</i>" . "</th>
                        <th style=\"width: 25%\">" . "<i>Prix unitaire</i>" . "</th>
                        <th style=\"width: 15%\">" . "<i>Prix HT</i>" . "</th>
                        <th style=\"width: 10%\">" . "<i>TVA</i>" . "</th>
                    </tr>
            </thead>        
                
        ";
        $table .= "<tbody>";
        $modelBatch = new BrBatch();
        $modelProduct = new EsProduct($id_space);
        $modelProductCategory = new EsProductCategory($id_space);
        $totalHT = 0;
        $totalVAT = 0;
        foreach($items as $item){
            
            $batchName = $modelBatch->getName($item["id_batch"]);
            $batchInfo = $modelBatch->get($item["id_batch"]);
            $productInfo = $modelProduct->get($batchInfo["id_product"]);
            
            $vat = $modelProductCategory->getVat($productInfo["id_category"]);
            
            $table .= "<tr>";
            $table .= "<td style=\"width: 25%; \">" . $productInfo["name"] . "</td>";
            $table .= "<td style=\"width: 25%; text-align: left; \">" . $item["quantity"] . " </td>";
            $table .= "<td style=\"width: 25%; text-align: left; \">" . $item["price"] . "</td>";
            $table .= "<td style=\"width: 15%; text-align: left; \">" . $item["quantity"]*$item["price"] . "</td>";
            $table .= "<td style=\"width: 10%; text-align: left; \">" . $vat . "%</td>";
            
            $totalHT += $item["quantity"]*$item["price"];
            $totalVAT += $item["quantity"]*$item["price"]*(1+$vat/100);
            $table .= "</tr>";
        }
        $table .= "</tbody></table>";
        
        $table .= '<br>';
        $table .= '<table cellspacing="0" style="width: 100%; border: solid 1px black; background: #fff; text-align: center; font-size: 10pt;">';
        $table .= '    <tr>';
        $table .= '        <th style="width: 87%; text-align: right;">Total HT: </th>';
        $table .= '        <th style="width: 13%; text-align: right;">'. number_format($totalHT, 2, ',', ' ') .' &euro;</th>';
        $table .= '    </tr>';
        $table .= '    <tr>';
        $table .= '        <th style="width: 87%; text-align: right;">Total TTC: </th>';
        $table .= '        <th style="width: 13%; text-align: right;">'. number_format($totalVAT, 2, ',', ' ') .' &euro;</th>';
        $table .= '    </tr>';
        $table .= '</table>';
        
        // render
        ob_start();
        include('data/invoices/'.$id_space.'/template.php');
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
    
    public function tosendsalelistAction($id_space){
        
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$ToSendSale);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::ToSendSale($lang));
        $table->addLineEditButton("essaletosendsale/" . $id_space);
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
    
    public function quotesentAction($id_space, $id_sale){
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        
        // data
        $sale = $this->modelSales->get($id_sale);
        
        // form
        $form = new Form($this->request, "estorequotesent");
        $form->setTitle(EstoreTranslator::Sale($lang) . " #" . $sale['id'] . " : " . EstoreTranslator::QuoteSent($lang));
        
        $form->addText("purchase_order_num", EstoreTranslator::PurchaseOderNum($lang), false, $sale["purchase_order_num"]);
        $form->addDownloadButton("data/estore/purchaseorders/" . $sale["purchase_order_file"], EstoreTranslator::PurchaseOrder($lang), $sale["purchase_order_file"], false);
        $form->addUpload("purchase_order_file", EstoreTranslator::PurchaseOrder($lang));
        $form->addExternalButton(CoreTranslator::Next($lang), "essaletosendsale/" . $id_space . "/" . $id_sale, "default");
        $form->setValidationButton(EstoreTranslator::Save($lang), "essalequotesent/" . $id_space . "/" . $id_sale);
        $form->setButtonsWidth(4, 8);
        
        if ($form->check()){
            
            $this->modelSales->setQuoteSent($id_sale, $form->getParameter("purchase_order_num"));
            
            // upload file
            $target_dir = "data/estore/purchaseorders/";
            if ($_FILES["purchase_order_file"]["name"] != "") {
                $ext = pathinfo($_FILES["purchase_order_file"]["name"], PATHINFO_EXTENSION);

                $url = $id_space . "_" . $id_sale . "." . $ext;
                FileUpload::uploadFile($target_dir, "purchase_order_file", $url);

                $this->modelSales->setQuoteSentFile($id_sale, $url);
            }
            
            // history
            $this->modelSaleHistory->set($id_sale, EsSaleStatus::$QuoteSent, $_SESSION["id_user"], date('Y-m-d', time()) );
            $this->modelSales->updateStatus($id_sale);
            
            // redirect
            $_SESSION["message"] = EstoreTranslator::Data_has_been_saved($lang);
            $this->redirect("essalequotesent/" . $id_space . "/" . $id_sale);
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
    
    public function quotesentlistAction($id_space){
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        
        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$QuoteSent);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::QuoteSent($lang));
        $table->addLineEditButton("quotesent/" . $id_space);
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
    
    public function tosendsaleAction($id_space, $id_sale){
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        // data
        $sale = $this->modelSales->get($id_sale);
        
        $modelDeliveries = new EsDeliveryMethod();
        $deliveries = $modelDeliveries->getForList($id_space);
        
        // form
        $form = new Form($this->request, "estorequotesent");
        $form->setTitle(EstoreTranslator::Sale($lang) . " #" . $sale['id'] . " : " . EstoreTranslator::ToSendSale($lang));
        
        $form->addSelectMandatory("delivery_type", EstoreTranslator::Delivery($lang), $deliveries["names"], $deliveries["ids"], $sale["delivery_type"]);
        $form->addDate("delivery_date_expected", EstoreTranslator::DeliveryDateExpected($lang), true, CoreTranslator::dateFromEn($sale["delivery_date_expected"], $lang));
        
        $form->addExternalButton(EstoreTranslator::DeliveryPaper($lang), "essaletosendsalepdf/" . $id_space . "/" . $id_sale, "danger");
        $form->addExternalButton(EstoreTranslator::Next($lang), "essaleinvoicing/" . $id_space . "/" . $id_sale, "default");
        
        $form->setValidationButton(EstoreTranslator::Save($lang), "essaletosendsale/" . $id_space . "/" . $id_sale);
        $form->setButtonsWidth(6, 6);
        
        if ( $form->check() ){
            $this->modelSales->setDelivery($id_sale, $form->getParameter("delivery_type"), CoreTranslator::dateToEn($form->getParameter("delivery_date_expected"), $lang));
        
            // history
            $this->modelSaleHistory->set($id_sale, EsSaleStatus::$ToSendSale, $_SESSION["id_user"], date('Y-m-d', time()) );
            $this->modelSales->updateStatus($id_sale);
            
            // redirect
            $_SESSION["message"] = EstoreTranslator::Data_has_been_saved($lang);
            $this->redirect("essaletosendsale/" . $id_space . "/" . $id_sale);
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
    
    public function tosendsalepdfAction($id_space, $id_sale){
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
    
    public function invoicinglistAction($id_space){
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$Invoicing);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::Invoicing($lang));
        $table->addLineEditButton("essaleinvoicing/" . $id_space);
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
    
    public function invoicingAction($id_space, $id_sale){
        
        // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        
        // data
        $sale = $this->modelSales->get($id_sale);
        
        $modelBatch = new BrBatch();
        $batchs = $modelBatch->getForList($id_space);

        $modelSaleItemsInovice = new EsSaleItemInvoice();
        $modelPrice = new EsPrice();
        
        $modelClient = new ClClient();
        $id_pricing = $modelClient->getPricingID( $sale["id_client"] );
        
        
        $items = $modelSaleItemsInovice->getitems($id_sale);
        if (count($items) == 0){
            $modelSaleItems = new EsSaleItem();
            $items = $modelSaleItems->getitems($id_sale);
        }
        
        $ids = array();
        $id_batchs = array();
        $quantities = array();
        $prices = array();
        foreach ($items as $it) {
            $ids[] = $it['id'];
            $id_batchs[] = $it['id_batch'];
            $quantities[] = $it['quantity'];
            if ( $it['price'] == -1 ){
                $prices[] = $modelPrice->getPrice($modelBatch->getProductID( $it['id_batch'] ), $id_pricing);
            }
            else{
                $prices[] = $it['price'];
            }
        }
        
        // form
        $form = new Form($this->request, "esaletodoquoteForm");
        $form->setTitle(EstoreTranslator::Sale($lang) . " #" . $sale['id'] . " : " . EstoreTranslator::TodoQuote($lang));
        $formAdd = new FormAdd($this->request, "esaleinprogressFromAdd");
        $formAdd->addHidden("id", $ids);
        $formAdd->addSelect("id_batch", EstoreTranslator::Batch($lang), $batchs["names"], $batchs["ids"], $id_batchs);
        $formAdd->addNumber("quantity", EstoreTranslator::Quantity($lang), $quantities);
        $formAdd->addText("prices", EstoreTranslator::UnitPrice($lang), $prices);
        $formAdd->setButtonsVisible(false);
        
        $form->setFormAdd($formAdd, EstoreTranslator::Items($lang));
        $form->addText("invoice_delivery_price", EstoreTranslator::DeliveryPrice($lang), true, $sale["invoice_delivery_price"] );
        
        $form->addExternalButton("PDF", "essaleinvoicingpdf/" . $id_space . "/" . $id_sale, "danger");
        $form->addExternalButton(CoreTranslator::Next($lang), "essalepaymentpending/" . $id_space . "/" . $id_sale, "default");
        $form->setValidationButton(EstoreTranslator::Save($lang), "essaleinvoicing/" . $id_space . "/" . $id_sale);
        $form->setButtonsWidth(4, 8);
        
        if ($form->check()) {
            
            $totalht = 0;
            $nids  = $form->getParameter("id");
            $nid_batch = $form->getParameter("id_batch");
            $nquantities = $form->getParameter("quantity");
            $nprices = $form->getParameter("prices");
            
            $this->modelSales->setInvoice($id_sale, $form->getParameter("invoice_delivery_price"));
            for ($i = 0; $i < count($nids); $i++) {
                $modelSaleItemsInovice->set($nids[$i], $id_sale, $nid_batch[$i], $nquantities[$i], $nprices[$i]);
                $totalht += $nquantities[$i]*$nprices[$i];
            }
            
            $this->modelSaleHistory->set($id_sale, EsSaleStatus::$Invoicing, $_SESSION["id_user"], date('Y-m-d', time()) );
            $this->modelSales->updateStatus($id_sale);
            
            $_SESSION["message"] = EstoreTranslator::Data_has_been_saved($lang);
            $this->redirect("essaleinvoicing/" . $id_space . "/" . $id_sale);
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
    
    public function invoicingpdfAction($id_space, $id_sale){
                
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
        $modelItems = new EsSaleItemInvoice();
        $items = $modelItems->getitems($id_sale);
        
        $table = "<table class=\"table\" style=\"width: 100%;\" font-site: 14px; font-family: times;\">
            <thead>
                    <tr>
                        <th style=\"width: 25%\">" . "<i>Désignation</i>" . "</th>
                        <th style=\"width: 25%\">" . "<i>Quantité</i>" . "</th>
                        <th style=\"width: 25%\">" . "<i>Prix unitaire</i>" . "</th>
                        <th style=\"width: 15%\">" . "<i>Prix HT</i>" . "</th>
                        <th style=\"width: 10%\">" . "<i>TVA</i>" . "</th>
                    </tr>
            </thead>        
                
        ";
        $table .= "<tbody>";
        $modelBatch = new BrBatch();
        $modelProduct = new EsProduct($id_space);
        $modelProductCategory = new EsProductCategory($id_space);
        $totalHT = 0;
        $totalVAT = 0;
        foreach($items as $item){
            
            $batchName = $modelBatch->getName($item["id_batch"]);
            $batchInfo = $modelBatch->get($item["id_batch"]);
            $productInfo = $modelProduct->get($batchInfo["id_product"]);
            
            $vat = $modelProductCategory->getVat($productInfo["id_category"]);
            
            $table .= "<tr>";
            $table .= "<td style=\"width: 25%; \">" . $productInfo["name"] . "</td>";
            $table .= "<td style=\"width: 25%; text-align: left; \">" . $item["quantity"] . " </td>";
            $table .= "<td style=\"width: 25%; text-align: left; \">" . $item["price"] . "</td>";
            $table .= "<td style=\"width: 15%; text-align: left; \">" . $item["quantity"]*$item["price"] . "</td>";
            $table .= "<td style=\"width: 10%; text-align: left; \">" . $vat . "%</td>";
            
            $totalHT += $item["quantity"]*$item["price"];
            $totalVAT += $item["quantity"]*$item["price"]*(1+$vat/100);
            $table .= "</tr>";
        }
        $table .= "</tbody></table>";
        
        $table .= '<br>';
        $table .= '<table cellspacing="0" style="width: 100%; border: solid 1px black; background: #fff; text-align: center; font-size: 10pt;">';
        $table .= '    <tr>';
        $table .= '        <th style="width: 87%; text-align: right;">Total HT: </th>';
        $table .= '        <th style="width: 13%; text-align: right;">'. number_format($totalHT, 2, ',', ' ') .' &euro;</th>';
        $table .= '    </tr>';
        $table .= '    <tr>';
        $table .= '        <th style="width: 87%; text-align: right;">Total TTC: </th>';
        $table .= '        <th style="width: 13%; text-align: right;">'. number_format($totalVAT, 2, ',', ' ') .' &euro;</th>';
        $table .= '    </tr>';
        $table .= '</table>';
        
        // render
        ob_start();
        include('data/invoices/'.$id_space.'/template.php');
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
    
    
    public function paymentpendinglistAction($id_space){
                // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$PaymentPending);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::PaymentPending($lang));
        $table->addLineEditButton("essalepaymentpending/" . $id_space);
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
    
    public function paymentpendingAction($id_space, $id_sale){
        
    }
    
    public function endedlistAction($id_space){
        
                // security
        $this->checkAuthorizationMenuSpace("estore", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSale = new EsSale();
        $data = $modelSale->getForSpace($id_space, EsSaleStatus::$Ended);

        $table = new TableView();
        $table->setTitle(EstoreTranslator::Ended($lang));
        $table->addLineEditButton("essaleended/" . $id_space);
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
    
    public function endedAction($id_space, $id_sale){

    }
    
    
    
  
    
}
