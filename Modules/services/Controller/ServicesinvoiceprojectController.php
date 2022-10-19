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
require_once 'Modules/services/Model/ServicesInvoice.php';



require_once 'Modules/clients/Model/ClientsTranslator.php';


/**
 *
 * @author sprigent
 * Controller for the home page
 */
class ServicesinvoiceprojectController extends InvoiceAbstractController
{
    private $serviceModel;

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);
        $this->serviceModel = new SeService();
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkAuthorizationMenuSpace("invoices", $idSpace, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $formByProjects = $this->createByProjectForm($idSpace, $lang);
        if ($formByProjects->check()) {
            $id_projects = $this->request->getParameter("id_project");
            $id_resp = $this->getProjectsResp($idSpace, $id_projects);
            $this->invoiceProjects($idSpace, $id_projects, $id_resp);
            return $this->redirect("invoices/" . $idSpace . "/");
        }
        $formByPeriod = $this->createByPeriodForm($idSpace, $lang);
        if ($formByPeriod->check()) {
            $modelProject = new SeProject();
            $beginPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_begin"), $lang);
            $endPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_end"), $lang);
            $id_resp = $this->request->getParameter("id_resp");
            if ($id_resp != 0) {
                $id_projects = $modelProject->getProjectsOpenedPeriodResp($idSpace, $beginPeriod, $endPeriod, $id_resp);

                $this->invoiceProjects($idSpace, $id_projects, $id_resp, $beginPeriod, $endPeriod);
                return $this->redirect("invoices/" . $idSpace);
            }
        }

        $this->render(array("id_space" => $idSpace, "lang" => $lang,
            "formByProjects" => $formByProjects->getHtml($lang),
            "formByPeriod" => $formByPeriod->getHtml($lang)));
    }

    public function editAction($idSpace, $id_invoice, $pdf = 0)
    {
        $this->checkAuthorizationMenuSpace("invoices", $idSpace, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $modelInvoice = new InInvoice();
        $invoice = $modelInvoice->get($idSpace, $id_invoice);

        $modelInvoiceItem = new InInvoiceItem();
        $id_items = $modelInvoiceItem->getInvoiceItems($idSpace, $id_invoice);

        // generate pdf
        if ($pdf == 1) {
            return $this->generatePDFInvoice($idSpace, $invoice, $id_items[0]["id"], $lang);
        }

        //print_r($id_items);
        // unparse details
        $detailsData = array();
        $item = $modelInvoiceItem->getItem($idSpace, $id_items[0]["id"]);
        $details = $item["details"];
        $detailsArray = explode(";", $details);
        foreach ($detailsArray as $de) {
            $d = explode("=", $de);
            if (count($d) == 2) {
                $detailsData[] = $d;
            }
        }

        // create edit form
        $form = $this->editForm($id_items[0]["id"], $idSpace, $id_invoice, $lang);
        $formAddName = $form->getFormAddId();
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

            $modelInvoiceItem->editItemContent($idSpace, $id_items[0]["id"], $content, $total_ht);
            $modelInvoice->setTotal($idSpace, $id_invoice, $total_ht);
            $modelInvoice->setDiscount($idSpace, $id_invoice, $discount);

            $_SESSION['flash'] = InvoicesTranslator::InvoiceHasBeenSaved($lang);
            $_SESSION['flashClass'] = 'success';

            Events::send([
                "action" => Events::ACTION_INVOICE_EDIT,
                "space" => ["id" => intval($idSpace)],
                "invoice" => ["id" => intval($id_invoice)]
            ]);
            return $this->redirect("servicesinvoiceprojectedit/" . $idSpace . "/" . $id_invoice . "/O");
        }


        // render
        return $this->render(array(
            "id_space" => $idSpace,
            "lang" => $lang,
            "invoice" => $invoice,
            "details" => $detailsData,
            "htmlForm" => $form->getHtml($lang),
            "formAddName" => $formAddName,
            "data" => ['item' => $item, 'invoice' => $invoice]
        ));
    }

    protected function unparseContent($idSpace, $id_item)
    {
        $modelServices = new SeService();
        $modelInvoiceItem = new InInvoiceItem();
        $item = $modelInvoiceItem->getItem($idSpace, $id_item);

        $contentArray = explode(";", $item["content"]);
        $contentList = array();
        foreach ($contentArray as $content) {
            $data = explode("=", $content);
            if (count($data) == 3) {
                $contentList[] = array($modelServices->getItemName($idSpace, $data[0], true) ?? Constants::UNKNOWN, $data[1], $data[2]);
            }
            if (count($data) > 3) {
                $contentList[] = array(($modelServices->getItemName($idSpace, $data[0], true) ?? Constants::UNKNOWN) . " " . $data[3], $data[1], $data[2]);
            }
        }
        return $contentList;
    }

    public function deleteAction($idSpace, $id_invoice)
    {
        $this->checkAuthorizationMenuSpace("invoices", $idSpace, $_SESSION["id_user"]);

        // get items
        $modelProject = new SeProject();
        $services = $modelProject->getServicesInvoice($idSpace, $id_invoice);
        foreach ($services as $s) {
            $modelProject->setServiceInvoice($idSpace, $s["id"], 0);
        }
    }

    public function editForm($id_item, $idSpace, $id_invoice, $lang)
    {
        $itemIds = array();
        $itemServices = array();
        $itemQuantities = array();
        $itemPrices = array();
        $itemComments = array();
        $itemQuantityTypes = array();
        $modelInvoiceItem = new InInvoiceItem();
        $modelServices = new SeService();
        $modelSeTypes = new SeServiceType();

        $item = $modelInvoiceItem->getItem($idSpace, $id_item);

        $contentArray = explode(";", $item["content"]);
        $total = 0;
        foreach ($contentArray as $content) {
            $data = explode("=", $content);
            if (count($data) >= 3) {
                $itemIds[] = $id_item;
                $itemServices[] = $data[0];
                $itemQuantities[] = $data[1];
                $itemPrices[] = $data[2];
                $itemQuantityTypes[] = $modelSeTypes->getType($modelServices->getItemType($idSpace, $data[0]));
                if (is_numeric($data[1]) && is_numeric($data[2])) {
                    $total += $data[1] * $data[2];
                } else {
                    $_SESSION['flash'] = InvoicesTranslator::NonNumericValue($lang);
                    $_SESSION['flashClass'] = 'danger';
                }
                if (count($data) == 4) {
                    $itemComments[] = $data[3];
                } else {
                    $itemComments[] = "";
                }
            }
        }
        $modelServices = new SeService();
        $services = $modelServices->getForList($idSpace);
        foreach ($itemServices as $s) {
            if (! in_array($s, $services["ids"])) {
                $services["ids"][] = $s;
                $services["names"][] = '[!] '. $modelServices->getName($idSpace, $s, true);
            }
        }


        $formAdd = new FormAdd($this->request, "editinvoiceprojectformadd");
        $formAdd->addSelect("id_service", ServicesTranslator::service($lang), $services["names"], $services["ids"], $itemServices);
        $formAdd->addFloat("quantity", ServicesTranslator::Quantity($lang), $itemQuantities);
        $formAdd->addLabel("type", $itemQuantityTypes);
        $formAdd->addFloat("unit_price", ServicesTranslator::UnitPrice($lang), $itemPrices);
        $formAdd->addText("comment", ServicesTranslator::Comment($lang), $itemComments);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));
        $form = new Form($this->request, "editinvoiceprojectform");


        $form->setValidationButton(CoreTranslator::Save($lang), "servicesinvoiceprojectedit/" . $idSpace . "/" . $id_invoice . "/0");
        $form->addExternalButton(InvoicesTranslator::GeneratePdf($lang), "servicesinvoiceprojectedit/" . $idSpace . "/" . $id_invoice . "/1", "danger", true);
        $form->setFormAdd($formAdd);

        $modelInvoice = new InInvoice();
        $discount = $modelInvoice->getDiscount($idSpace, $id_invoice);
        $form->addText("discount", ServicesTranslator::Discount($lang), false, $discount);


        $total = (1-floatval($discount)/100)*$total;
        $form->addNumber("total", InvoicesTranslator::Total_HT($lang), false, $total);
        $form->setColumnsWidth(9, 2);

        return $form;
    }

    protected function createByProjectForm($idSpace, $lang)
    {
        $form = new Form($this->request, "ByProjectForm");
        $form->addSeparator(ServicesTranslator::By_projects($lang));

        $modelProject = new SeProject();
        $projects = $modelProject->getOpenedProjectForList($idSpace);

        $formAdd = new FormAdd($this->request, "ByProjectFormAdd");
        $formAdd->addSelect("id_project", ServicesTranslator::Project($lang), $projects["names"], $projects["ids"]);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($formAdd);


        $form->setValidationButton(CoreTranslator::Save($lang), "servicesinvoiceproject/" . $idSpace);
        return $form;
    }

    protected function createByPeriodForm($idSpace, $lang)
    {
        $form = new Form($this->request, "ByPeriodForm");
        $form->addSeparator(ServicesTranslator::By_period($lang));

        $form->addDate("period_begin", InvoicesTranslator::Period_begin($lang), true, $this->request->getParameterNoException("period_begin"));
        $form->addDate("period_end", InvoicesTranslator::Period_end($lang), true, $this->request->getParameterNoException("period_end"));
        $respId = $this->request->getParameterNoException("id_resp");

        $modelClient = new ClClient();
        $resps = $modelClient->getForList($idSpace);

        $form->addSelect("id_resp", ClientsTranslator::ClientAccount($lang), $resps["names"], $resps["ids"], $respId);


        $form->setValidationButton(CoreTranslator::Save($lang), "servicesinvoiceproject/" . $idSpace);
        return $form;
    }

    public function invoiceprojectAction($idSpace, $id_project)
    {
        $modelProject = new SeProject();
        $id_resp = $modelProject->getResp($idSpace, $id_project);

        $id_projects = array();
        $id_projects[] = $id_project;
        $this->invoiceProjects($idSpace, $id_projects, $id_resp);
        return $this->redirect("invoices/" . $idSpace);
    }

    protected function invoiceProjects($idSpace, $id_projects, $id_client, $beginPeriod=null, $endPeriod=null)
    {
        $cv = new CoreVirtual();
        $projects = implode(',', $id_projects);
        $period = '';
        if ($beginPeriod) {
            $period = "$beginPeriod => $endPeriod";
        }
        $rid = $cv->newRequest($idSpace, "invoices", "projects[$id_client][$projects]:$period");
        Events::send([
            "action" => Events::ACTION_INVOICE_REQUEST,
            "space" => ["id" => intval($idSpace)],
            "user" => ["id" => $_SESSION['id_user']],
            "type" => ServicesInvoice::$INVOICES_SERVICES_PROJECTS_CLIENT,
            "period_begin" => $beginPeriod,
            "period_end" => $endPeriod,
            "id_client" => $id_client,
            "id_projects" => $id_projects,
            "request" => ["id" => $rid]
        ]);
    }

    protected function getProjectsResp($idSpace, $id_projects)
    {
        if (empty($id_projects)) {
            throw new PfmParamException("You need to select at least one project");
        }

        $modelProject = new SeProject();

        $id_resp = $modelProject->getResp($idSpace, $id_projects[0]);
        for ($i = 1; $i < count($id_projects); $i++) {
            $id_respi = $modelProject->getResp($idSpace, $id_projects[$i]);
            if ($id_respi != $id_resp) {
                throw new PfmParamException("Projects must have the same responsible");
            }
        }
        return $id_resp;
    }

    protected function generatePDFInvoice($idSpace, $invoice, $id_item, $lang)
    {
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
        $content = $this->unparseContent($idSpace, $id_item);
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
        if ($discount>0) {
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
        $adress = $modelClient->getAddressInvoice($idSpace, $invoice["id_responsible"]);
        $clientInfos = $modelClient->get($idSpace, $invoice["id_responsible"]);
        $resp = $clientInfos["contact_name"];
        return $this->generatePDF($idSpace, $invoice["id"], $invoice["date_generated"], $unit, $resp, $adress, $table, $total, clientInfos: $clientInfos, lang: $lang);
    }
}
