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

require_once 'Modules/ecosystem/Model/EcosystemTranslator.php';
require_once 'Modules/ecosystem/Model/EcUnit.php';

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

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $formByProjects = $this->createByProjectForm($id_space, $lang);
        if ($formByProjects->check()) {
            $id_projects = $this->request->getParameter("id_project");
            $id_resp = $this->getProjectsResp($id_projects);
            //echo "id resp = " . $id_resp . "<br/>";
            $modelUser = new EcUser();
            $id_unit = $modelUser->getUnit($id_resp);
            //echo "id unit = " . $id_unit . "<br/>";
            $this->invoiceProjects($id_space, $id_projects, $id_unit, $id_resp);
            $this->redirect("invoices/" . $id_space);
            return;
        }
        $formByPeriod = $this->createByPeriodForm($id_space, $lang);
        if ($formByPeriod->check()) {

            $modelProject = new SeProject();
            $beginPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_begin"), $lang);
            $endPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_end"), $lang);
            $id_resp = $this->request->getParameter("id_resp");
            $id_unit = $this->request->getParameter("id_unit");
            if ($id_resp != 0 && $id_unit != 0) {
                $id_projects = $modelProject->getProjectsOpenedPeriodResp($beginPeriod, $endPeriod, $id_resp);

                $this->invoiceProjects($id_space, $id_projects, $id_unit, $id_resp);
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
        $invoice = $modelInvoice->get($id_invoice);

        $modelInvoiceItem = new InInvoiceItem();
        $id_items = $modelInvoiceItem->getInvoiceItems($id_invoice);

        // generate pdf
        if ($pdf == 1) {
            $this->generatePDFInvoice($id_space, $invoice, $id_items[0]["id"], $lang);
            return;
        }

        //print_r($id_items);
        // unparse details
        $detailsData = array();
        $item = $modelInvoiceItem->getItem($id_items[0]["id"]);
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
            for ($i = 0; $i < count($id_services); $i++) {
                $content .= $id_services[$i] . "=" . $quantity[$i] . "=" . $unit_price[$i] . "=" . $comment[$i] . ";";
                $total_ht += $quantity[$i] * $unit_price[$i];
            }
            $modelInvoiceItem->editItemContent($id_items[0]["id"], $content, $total_ht);
            $modelInvoice->setTotal($id_invoice, $total_ht);
            $this->redirect("servicesinvoiceprojectedit/" . $id_space . "/" . $id_invoice . "/O");
        }

        // render
        $this->render(array("id_space" => $id_space, "lang" => $lang, "invoice" => $invoice, "details" => $detailsData, "htmlForm" => $form->getHtml($lang)));
    }

    protected function unparseContent($id_item) {

        $modelServices = new SeService();
        $modelInvoiceItem = new InInvoiceItem();
        $item = $modelInvoiceItem->getItem($id_item);

        $contentArray = explode(";", $item["content"]);
        $contentList = array();
        foreach ($contentArray as $content) {
            //echo "content = " . $content . "<br/>";
            $data = explode("=", $content);
            //echo "size = " . count($data) . "<br/>";
            if (count($data) == 3) {
                $contentList[] = array($modelServices->getItemName($data[0]), $data[1], $data[2]);
            }
            if(count($data) > 3){
                $contentList[] = array($modelServices->getItemName($data[0]) . " " . $data[3], $data[1], $data[2] );
            }
        }
        return $contentList;
    }

    public function deleteAction($id_space, $id_invoice) {

        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        // get items
        $modelProject = new SeProject();
        $services = $modelProject->getServicesInvoice($id_invoice);
        foreach ($services as $s) {
            $modelProject->setServiceInvoice($s["id"], 0);
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
        $item = $modelInvoiceItem->getItem($id_item);

        $contentArray = explode(";", $item["content"]);
        $total = 0;
        foreach ($contentArray as $content) {
            $data = explode("=", $content);
            if (count($data) >= 3) {
                $itemIds[] = $id_item;
                $itemServices[] = $data[0];
                $itemQuantities[] = $data[1];
                $itemPrices[] = $data[2];
                $total += $data[1] * $data[2];
                if (count($data) == 4) {
                    $itemComments[] = $data[3];
                } else {
                    $itemComments[] = "";
                }
            }
        }
        $modelServices = new SeService();
        $services = $modelServices->getForList();

        $formAdd = new FormAdd($this->request, "editinvoiceprojectformadd");
        $formAdd->addSelect("id_service", ServicesTranslator::service($lang), $services["names"], $services["ids"], $itemServices);
        $formAdd->addNumber("quantity", ServicesTranslator::Quantity($lang), $itemQuantities);
        $formAdd->addNumber("unit_price", ServicesTranslator::UnitPrice($lang), $itemPrices);
        $formAdd->addText("comment", ServicesTranslator::Comment($lang), $itemComments);
        //$formAdd->addHidden("id_item", $itemIds);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form = new Form($this->request, "editinvoiceprojectform");
        $form->setButtonsWidth(2, 9);
        $form->setValidationButton(CoreTranslator::Save($lang), "servicesinvoiceprojectedit/" . $id_space . "/" . $id_invoice . "/0");
        $form->addExternalButton(InvoicesTranslator::GeneratePdf($lang), "servicesinvoiceprojectedit/" . $id_space . "/" . $id_invoice . "/1", "danger", true);
        $form->setFormAdd($formAdd);
        $form->addNumber("total", InvoicesTranslator::Total_HT($lang), false, $total);
        $form->setColumnsWidth(9, 2);
        return $form;
    }

    protected function createByProjectForm($id_space, $lang) {
        $form = new Form($this->request, "ByProjectForm");
        $form->addSeparator(ServicesTranslator::By_projects($lang));

        $modelProject = new SeProject();
        $projects = $modelProject->getOpenedProjectForList();

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

        $form->addDate("period_begin", InvoicesTranslator::Period_begin($lang), false, $this->request->getParameterNoException("period_begin"));
        $form->addDate("period_end", InvoicesTranslator::Period_end($lang), false, $this->request->getParameterNoException("period_end"));
        $unitId = $this->request->getParameterNoException("id_unit");
        $respId = $this->request->getParameterNoException("id_resp");

        $modelUnit = new EcUnit();
        $units = $modelUnit->getUnitsForList("name");

        $modelUser = new EcUser();
        $resps = $modelUser->getResponsibleOfUnit($unitId);

        $form->addSelect("id_unit", EcosystemTranslator::Units($lang), $units["names"], $units["ids"], $unitId, true);
        $form->addSelect("id_resp", EcosystemTranslator::Responsible($lang), $resps["names"], $resps["ids"], $respId);
        $form->setButtonsWidth(2, 9);

        $form->setValidationButton(CoreTranslator::Save($lang), "servicesinvoiceproject/" . $id_space);
        return $form;
    }

    protected function invoiceProjects($id_space, $id_projects, $id_unit, $id_resp) {

        // add invoice
        //echo "add invoice <br/>";
        $modelInvoiceItem = new InInvoiceItem();
        $modelInvoice = new InInvoice();
        $module = "services";
        $controller = "servicesinvoiceproject";
        $number = $modelInvoice->getNextNumber();
        $id_invoice = $modelInvoice->addInvoice($module, $controller, $id_space, $number, date("Y-m-d", time()), $id_unit, $id_resp);

        // parse content
        //echo "parse content <br/>";
        $modelUnit = new EcUnit();
        $id_belonging = $modelUnit->getBelonging($id_unit, $id_space);
        $total_ht = 0;
        $modelProject = new SeProject();
        $addedServices = array();
        $addedServicesCount = array();
        $addedServicesPrice = array();
        $addedServicesComment = array();
        $modelPrice = new SePrice();

        foreach ($id_projects as $id_proj) {
            $services = $modelProject->getNoInvoicesServices($id_proj);

            $servicesMerged = array();
            for ($i = 0; $i < count($services); $i++) {
                $modelProject->setServiceInvoice($services[$i]["id"], $id_invoice);
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
                $price = $modelPrice->getPrice($servicesMerged[$i]["id_service"], $id_belonging);
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
        foreach ($id_projects as $id_proj) {
            $name = $modelProject->getName($id_proj);
            $details .= $name . "=" . "servicesprojectfollowup/" . $id_space . "/" . $id_proj . ";";
        }
        //echo "set item <br/>";
        // set invoice itmems
        $modelInvoiceItem->setItem(0, $id_invoice, $module, $controller, $content, $details, $total_ht);
        $modelInvoice->setTotal($id_invoice, $total_ht);
    }

    protected function getProjectsResp($id_projects) {

        if (count($id_projects) == 0) {
            throw new Exception("You need to select at least one project");
        }

        $modelProject = new SeProject();

        $id_resp = $modelProject->getResp($id_projects[0]);
        for ($i = 1; $i < count($id_projects); $i++) {
            $id_respi = $modelProject->getResp($id_projects[$i]);
            if ($id_respi != $id_resp) {
                throw new Exception("Projects must have the same responsible");
            }
        }
        return $id_resp;
    }

    protected function generatePDFInvoice($id_space, $invoice, $id_item, $lang) {

        $table = "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;\">
                    <tr>
                        <th style=\"width: 52%\">" . InvoicesTranslator::Designation($lang) . "</th>
                        <th style=\"width: 17%\">" . InvoicesTranslator::UnitPrice($lang) . "</th>
                        <th style=\"width: 14%\">" . InvoicesTranslator::Quantity($lang) . "</th>
                        <th style=\"width: 17%\">" . InvoicesTranslator::Price_HT($lang) . "</th>
                    </tr>
                </table>
        ";


        $table .= "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #F7F7F7; text-align: center; font-size: 10pt;\">";
        $content = $this->unparseContent($id_item);
        //print_r($invoice);
        $total = 0;
        foreach ($content as $d) {
            $table .= "<tr>";
            $table .= "<td style=\"width: 52%; text-align: left; border: solid 1px black;\">" . $d[0] . "</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . number_format($d[2], 2, ',', ' ') . " &euro;</td>";
            $table .= "<td style=\"width: 14%; border: solid 1px black;\">" . number_format($d[1], 2, ',', ' ') . "</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . number_format($d[1] * $d[2], 2, ',', ' ') . " &euro;</td>";
            $table .= "</tr>";
            $total += $d[1] * $d[2];
        }
        $table .= "</table>";

        $modelUnit = new EcUnit();
        $unit = $modelUnit->getUnitName($invoice["id_unit"]);
        $adress = $modelUnit->getAdress($invoice["id_unit"]);
        $modelUser = new EcUser();
        $resp = $modelUser->getUserFUllName($invoice["id_responsible"]);
        $this->genreratePDF($invoice["number"], $invoice["date_generated"], $unit, $resp, $adress, $table, $total);
    }

}
