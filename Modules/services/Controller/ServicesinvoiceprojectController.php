<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/invoices/Controller/InvoiceAbstractController.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';
require_once 'Modules/invoices/Model/InInvoice.php';


require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/services/Model/ServicesTranslator.php';
require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SeServiceType.php';
require_once 'Modules/services/Model/SeProject.php';
require_once 'Modules/services/Model/SePrice.php';

require_once 'Modules/clients/Model/ClientsTranslator.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class ServicesinvoiceprojectController extends InvoiceAbstractController {

    private $serviceModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        //$this->checkAuthorizationMenu("services");
        $this->serviceModel = new SeService();

    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $formByProjects = $this->createByProjectForm($id_space, $lang);
        if ($formByProjects->check()) {
            $id_projects = $this->request->getParameter("id_project");
            $id_resp = $this->getProjectsResp($id_space, $id_projects);
            //echo "id resp = " . $id_resp . "<br/>";
            //echo "id unit = " . $id_unit . "<br/>";
            $id_invoice = $this->invoiceProjects($id_space, $id_projects, $id_resp);
            $this->redirect("invoiceedit/" . $id_space . "/" . $id_invoice);
            return;
        }
        $formByPeriod = $this->createByPeriodForm($id_space, $lang);
        if ($formByPeriod->check()) {

            $modelProject = new SeProject();
            $beginPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_begin"), $lang);
            $endPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_end"), $lang);
            $id_resp = $this->request->getParameter("id_resp");
            if ($id_resp != 0) {
                $id_projects = $modelProject->getProjectsOpenedPeriodResp($id_space, $beginPeriod, $endPeriod, $id_resp);

                $this->invoiceProjects($id_space, $id_projects, $id_resp);
                $this->redirect("invoices/" . $id_space);
                return;
            }
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang,
            "formByProjects" => $formByProjects->getHtml($lang),
            "formByPeriod" => $formByPeriod->getHtml($lang)));
    }

    public function editAction($id_space, $id_invoice, $pdf = 0) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $modelInvoice = new InInvoice();
        $invoice = $modelInvoice->get($id_space, $id_invoice);

        $modelInvoiceItem = new InInvoiceItem();
        $id_items = $modelInvoiceItem->getInvoiceItems($id_space, $id_invoice);

        // generate pdf
        if ($pdf == 1) {
            $this->generatePDFInvoice($id_space, $invoice, $id_items[0]["id"], $lang);
            return;
        }

        //print_r($id_items);
        // unparse details
        $detailsData = array();
        $item = $modelInvoiceItem->getItem($id_space, $id_items[0]["id"]);
        $details = $item["details"];
        $detailsArray = explode(";", $details);
        foreach ($detailsArray as $de) {
            $d = explode("=", $de);
            if (count($d) == 2) {
                $detailsData[] = $d;
            }
        }

        // create edit form
        $form = $this->editForm($id_items[0]["id"], $id_space, $id_invoice, $lang);
        if ($form->check()) {
            $total_ht = 0;
            $id_services = $this->request->getParameter("id_service");
            $quantity = $this->request->getParameter("quantity");
            $unit_price = $this->request->getParameter("unit_price");
            $comment = $this->request->getParameter("comment");
            $content = "";
            $id_services = is_array($id_services) ? $id_services : [];
            for ($i = 0; $i < count($id_services); $i++) {
                $content .= $id_services[$i] . "=" . $quantity[$i] . "=" . $unit_price[$i] . "=" . $comment[$i] . ";";
                $total_ht += floatval($quantity[$i]) * floatval($unit_price[$i]);
            }
            // apply discount
            $discount = $form->getParameter("discount");
            $total_ht = (1-floatval($discount)/100)*$total_ht;
            
            $modelInvoiceItem->editItemContent($id_space, $id_items[0]["id"], $content, $total_ht);
            $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);
            $modelInvoice->setDiscount($id_space, $id_invoice, $discount);
            Events::send([
                "action" => Events::ACTION_INVOICE_EDIT,
                "space" => ["id" => intval($id_space)],
                "invoice" => ["id" => intval($id_invoice)]
            ]);
            $this->redirect("servicesinvoiceprojectedit/" . $id_space . "/" . $id_invoice . "/O");
            return;
        }
        

        // render
        $this->render(array("id_space" => $id_space, "lang" => $lang, "invoice" => $invoice, "details" => $detailsData, "htmlForm" => $form->getHtml($lang)));
    }
    
    protected function unparseContent($id_space, $id_item) {

        $modelServices = new SeService();
        $modelInvoiceItem = new InInvoiceItem();
        $item = $modelInvoiceItem->getItem($id_space, $id_item);

        $contentArray = explode(";", $item["content"]);
        $contentList = array();
        foreach ($contentArray as $content) {
            //echo "content = " . $content . "<br/>";
            $data = explode("=", $content);
            //echo "size = " . count($data) . "<br/>";
            if (count($data) == 3) {
                $contentList[] = array($modelServices->getItemName($id_space, $data[0]), $data[1], $data[2]);
            }
            if (count($data) > 3) {
                $contentList[] = array($modelServices->getItemName($id_space, $data[0]) . " " . $data[3], $data[1], $data[2]);
            }
        }
        return $contentList;
    }

    public function deleteAction($id_space, $id_invoice) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        // get items
        $modelProject = new SeProject();
        $services = $modelProject->getServicesInvoice($id_space, $id_invoice);
        foreach ($services as $s) {
            $modelProject->setServiceInvoice($id_space, $s["id"], 0);
        }
    }

    public function editForm($id_item, $id_space, $id_invoice, $lang) {

        $itemIds = array();
        $itemServices = array();
        $itemQuantities = array();
        $itemPrices = array();
        $itemComments = array();
        $modelInvoiceItem = new InInvoiceItem();

        //print_r($id_item);
        $item = $modelInvoiceItem->getItem($id_space, $id_item);

        $contentArray = explode(";", $item["content"]);
        $total = 0;
        foreach ($contentArray as $content) {
            $data = explode("=", $content);
            if (count($data) >= 3) {
                $itemIds[] = $id_item;
                $itemServices[] = $data[0];
                $itemQuantities[] = $data[1];
                $itemPrices[] = $data[2];
                $total += floatval($data[1]) * floatval($data[2]);
                if (count($data) == 4) {
                    $itemComments[] = $data[3];
                } else {
                    $itemComments[] = "";
                }
            }
        }
        $modelServices = new SeService();
        $services = $modelServices->getForList($id_space);

        $formAdd = new FormAdd($this->request, "editinvoiceprojectformadd");
        $formAdd->addSelect("id_service", ServicesTranslator::service($lang), $services["names"], $services["ids"], $itemServices);
        $formAdd->addText("quantity", ServicesTranslator::Quantity($lang), $itemQuantities);
        $formAdd->addText("unit_price", ServicesTranslator::UnitPrice($lang), $itemPrices);
        $formAdd->addText("comment", ServicesTranslator::Comment($lang), $itemComments);
        //$formAdd->addHidden("id_item", $itemIds);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form = new Form($this->request, "editinvoiceprojectform");
        $form->setButtonsWidth(2, 9);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesinvoiceprojectedit/" . $id_space . "/" . $id_invoice . "/0");
        $form->addExternalButton(InvoicesTranslator::GeneratePdf($lang), "servicesinvoiceprojectedit/" . $id_space . "/" . $id_invoice . "/1", "danger", true);
        $form->setFormAdd($formAdd);
        
        $modelInvoice = new InInvoice();
        $discount = $modelInvoice->getDiscount($id_space, $id_invoice);
        $form->addText("discount", ServicesTranslator::Discount($lang), false, $discount);
        
        
        $total = (1-floatval($discount)/100)*$total;
        $form->addNumber("total", InvoicesTranslator::Total_HT($lang), false, $total);
        $form->setColumnsWidth(9, 2);
        $form->setButtonsWidth(4, 8);
        return $form;
    }

    protected function createByProjectForm($id_space, $lang) {
        $form = new Form($this->request, "ByProjectForm");
        $form->addSeparator(ServicesTranslator::By_projects($lang));

        $modelProject = new SeProject();
        $projects = $modelProject->getOpenedProjectForList($id_space);

        $formAdd = new FormAdd($this->request, "ByProjectFormAdd");
        $formAdd->addSelect("id_project", ServicesTranslator::Project($lang), $projects["names"], $projects["ids"]);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($formAdd);
        $form->setButtonsWidth(2, 9);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesinvoiceproject/" . $id_space);
        return $form;
    }

    protected function createByPeriodForm($id_space, $lang) {
        $form = new Form($this->request, "ByPeriodForm");
        $form->addSeparator(ServicesTranslator::By_period($lang));

        $form->addDate("period_begin", InvoicesTranslator::Period_begin($lang), true, $this->request->getParameterNoException("period_begin"));
        $form->addDate("period_end", InvoicesTranslator::Period_end($lang), true, $this->request->getParameterNoException("period_end"));
        $respId = $this->request->getParameterNoException("id_resp");

        $modelClient = new ClClient();
        $resps = $modelClient->getForList($id_space);

        $form->addSelect("id_resp", ClientsTranslator::ClientAccount($lang), $resps["names"], $resps["ids"], $respId);
        $form->setButtonsWidth(2, 9);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesinvoiceproject/" . $id_space);
        return $form;
    }

    public function invoiceprojectAction($id_space, $id_project) {

        //echo "id_proj = " . $id_project . "<br/>";

        $modelProject = new SeProject();
        $id_resp = $modelProject->getResp($id_space, $id_project);
        //echo "$id_resp = " . $id_resp . "<br/>";
        
        $id_projects = array();
        $id_projects[] = $id_project;
        $id_invoice = $this->invoiceProjects($id_space, $id_projects, $id_resp);
        $this->redirect("invoiceedit/" . $id_space . "/" . $id_invoice);
    }

    protected function invoiceProjects($id_space, $id_projects, $id_resp) {

        // add invoice
        //echo "add invoice <br/>";
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoice = new InInvoice();
        $module = "services";
        $controller = "servicesinvoiceproject";
        $number = $modelInvoice->getNextNumber($id_space);
        $id_invoice = $modelInvoice->addInvoice($module, $controller, $id_space, $number, date("Y-m-d", time()), $id_resp);
        $modelInvoice->setEditedBy($id_space, $id_invoice, $_SESSION["id_user"]);
        
        // parse content
        //echo "parse content <br/>";
        $modelClient = new ClClient();
        $id_belonging = $modelClient->getPricingID($id_space ,$id_resp);
        
        //echo 'resp = ' . $id_resp . '<br/>';
        //echo 'belonging = ' . $id_belonging . "<br/>";
        $total_ht = 0;
        $modelProject = new SeProject();
        $addedServices = array();
        $addedServicesCount = array();
        $addedServicesPrice = array();
        $addedServicesComment = array();
        $modelPrice = new SePrice();

        foreach ($id_projects as $id_proj) {
            $services = $modelProject->getNoInvoicesServices($id_space, $id_proj);

            $servicesMerged = array();
            for ($i = 0; $i < count($services); $i++) {
                $modelProject->setServiceInvoice($id_space, $services[$i]["id"], $id_invoice);
                if (!isset($services[$i]["counted"])) {
                    $quantity = floatval($services[$i]["quantity"]);

                    for ($j = $i + 1; $j < count($services); $j++) {
                        if ($services[$i]["comment"] == $services[$j]["comment"] && $services[$i]["id_service"] == $services[$j]["id_service"]) {
                            $quantity += floatval($services[$j]["quantity"]);
                            $services[$j]["counted"] = 1;
                        }
                    }
                    $data["id_service"] = $services[$i]["id_service"];
                    $data["comment"] = $services[$i]["comment"];
                    $data["quantity"] = $quantity;

                    $servicesMerged[] = $data;
                }
            }

            //echo "services = ";
            //print_r($services); echo "<br/>";
            //echo "servicesMerged = ";
            //print_r($servicesMerged); echo "<br/>";
            for ($i = 0; $i < count($servicesMerged); $i++) {
                $addedServices[] = $servicesMerged[$i]["id_service"];
                $quantity = floatval($servicesMerged[$i]["quantity"]);
                $price = floatval($modelPrice->getPrice($id_space, $servicesMerged[$i]["id_service"], $id_belonging));
                $addedServicesCount[] = $quantity;
                $addedServicesPrice[] = $price;
                $addedServicesComment[] = $servicesMerged[$i]["comment"];
                $total_ht += $quantity * $price;
            }
        }

        $content = "";
        for ($i = 0; $i < count($addedServices); $i++) {
            $content .= $addedServices[$i] . "=" . $addedServicesCount[$i] . "=" . $addedServicesPrice[$i] . "=" . $addedServicesComment[$i] . ";";
        }
        // get details
        //echo "get details <br/>";
        $details = "";
        $title = "";
        foreach ($id_projects as $id_proj) {
            $name = $modelProject->getName($id_space, $id_proj);
            $details .= $name . "=" . "servicesprojectfollowup/" . $id_space . "/" . $id_proj . ";";
            $title .= $name . " ";
        }
        //echo "set item <br/>";
        // set invoice itmems
        $modelInvoiceItem->setItem($id_space ,0, $id_invoice, $module, $controller, $content, $details, $total_ht);
        $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);
        $modelInvoice->setTitle($id_space, $id_invoice, $title);
        Events::send([
            "action" => Events::ACTION_INVOICE_EDIT,
            "space" => ["id" => intval($id_space)],
            "invoice" => ["id" => intval($id_invoice)]
        ]);

        return $id_invoice;
    }

    protected function getProjectsResp($id_space, $id_projects) {

        if (empty($id_projects)) {
            throw new PfmParamException("You need to select at least one project", 403);
        }

        $modelProject = new SeProject();

        $id_resp = $modelProject->getResp($id_space, $id_projects[0]);
        for ($i = 1; $i < count($id_projects); $i++) {
            $id_respi = $modelProject->getResp($id_space, $id_projects[$i]);
            if ($id_respi != $id_resp) {
                throw new PfmParamException("Projects must have the same responsible", 403);
            }
        }
        return $id_resp;
    }

    protected function generatePDFInvoice($id_space, $invoice, $id_item, $lang) {
        $table = "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;\">
                    <tr>
                        <th style=\"width: 52%\">" . InvoicesTranslator::Designation($lang) . "</th>
                        <th style=\"width: 14%\">" . InvoicesTranslator::Quantity($lang) . "</th>
                        <th style=\"width: 17%\">" . InvoicesTranslator::UnitPrice($lang) . "</th>
                        <th style=\"width: 17%\">" . InvoicesTranslator::Price_HT($lang) . "</th>
                    </tr>
                </table>
        ";

        $table .= "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #F7F7F7; text-align: center; font-size: 10pt;\">";
        $content = $this->unparseContent($id_space, $id_item);
        $total = 0;
        foreach ($content as $d) {
            $rawQuantity = floatval($d[1]);
            $rawUnitPrice = floatval($d[2]);
            $name = $d[0];
            $formattedQuantity = number_format($rawQuantity, 2, ',', ' ');
            $formattedUnitPrice = number_format($rawUnitPrice, 2, ',', ' ') . "&euro;";
            
            $table .= "<tr>";
            $table .= "<td style=\"width: 52%; text-align: left; border: solid 1px black;\">" . $name . "</td>";
            $table .= "<td style=\"width: 14%; border: solid 1px black;\">" . $formattedQuantity . "</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . $formattedUnitPrice . " </td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . number_format($rawQuantity * $rawUnitPrice, 2, ',', ' ') . " &euro;</td>";
            $table .= "</tr>";
            $total += $rawQuantity * $rawUnitPrice;
        }
        
        $discount = floatval($invoice["discount"]);
        if($discount>0){
            $total = (1-$discount/100)*$total;
            $table .= "<tr>";
            $table .= "<td style=\"width: 52%; text-align: left; border: solid 1px black;\">" . InvoicesTranslator::Discount($lang) . "</td>";
            $table .= "<td style=\"width: 14%; border: solid 1px black;\">" . 1 . "</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . $invoice["discount"] . " %</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . $invoice["discount"] . " %</td>";
            $table .= "</tr>";
        }
        $table .= "</table>";

        $modelClient = new ClClient();
        $unit = "";
        $adress = $modelClient->getAddressInvoice($id_space, $invoice["id_responsible"]); //$modelUnit->getAdress($invoice["id_unit"]);
        $clientInfos = $modelClient->get($id_space, $invoice["id_responsible"]);
        $resp = $clientInfos["contact_name"];
        $this->generatePDF($id_space, $invoice["number"], $invoice["date_generated"], $unit, $resp, $adress, $table, $total, clientInfos: $clientInfos);
    }

}
