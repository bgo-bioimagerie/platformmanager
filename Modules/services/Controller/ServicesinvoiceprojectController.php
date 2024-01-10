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

use Fp\Collection as Fp;
use Fp\Functional\Option\Option;


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
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();
        $formByProjects = $this->createByProjectForm($id_space, $lang);
        if ($formByProjects->check()) {
            $id_projects = $this->request->getParameter("id_project");
            $id_resp = $this->getProjectsResp($id_space, $id_projects);
            $this->invoiceProjects($id_space, $id_projects, $id_resp);
            return $this->redirect("invoices/" . $id_space . "/");
        }
        $formByPeriod = $this->createByPeriodForm($id_space, $lang);
        if ($formByPeriod->check()) {
            $modelProject = new SeProject();
            $beginPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_begin"), $lang);
            $endPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_end"), $lang);
            $id_resp = $this->request->getParameter("id_resp");
            if ($id_resp != 0) {
                $id_projects = $modelProject->getProjectsOpenedPeriodResp($id_space, $beginPeriod, $endPeriod, $id_resp);

                $this->invoiceProjects($id_space, $id_projects, $id_resp, $beginPeriod, $endPeriod);
                return $this->redirect("invoices/" . $id_space);
            }
        }

        $this->render(array("id_space" => $id_space, "lang" => $lang,
            "formByProjects" => $formByProjects->getHtml($lang),
            "formByPeriod" => $formByPeriod->getHtml($lang)));
    }

    public function editAction($id_space, $id_invoice, $pdf = 0)
    {
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

            $modelInvoiceItem->editItemContent($id_space, $id_items[0]["id"], $content, $total_ht);
            $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);
            $modelInvoice->setDiscount($id_space, $id_invoice, $discount);

            $_SESSION['flash'] = InvoicesTranslator::InvoiceHasBeenSaved($lang);
            $_SESSION['flashClass'] = 'success';

            Events::send([
                "action" => Events::ACTION_INVOICE_EDIT,
                "space" => ["id" => intval($id_space)],
                "invoice" => ["id" => intval($id_invoice)]
            ]);
            return $this->redirect("servicesinvoiceprojectedit/" . $id_space . "/" . $id_invoice . "/O");
        }


        // render
        return $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "invoice" => $invoice,
            "details" => $detailsData,
            "htmlForm" => $form->getHtml($lang),
            "formAddName" => $formAddName,
            "data" => ['item' => $item, 'invoice' => $invoice]
        ));
    }

    public function deleteAction($id_space, $id_invoice)
    {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        // get items
        $modelProject = new SeProject();
        $services = $modelProject->getServicesInvoice($id_space, $id_invoice);
        foreach ($services as $s) {
            $modelProject->setServiceInvoice($id_space, $s["id"], 0);
        }
    }

    public function editForm($id_item, $id_space, $id_invoice, $lang)
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
                $itemQuantityTypes[] = $modelSeTypes->getType($modelServices->getItemType($id_space, $data[0]));
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
        $services = $modelServices->getForList($id_space);
        foreach ($itemServices as $s) {
            if (! in_array($s, $services["ids"])) {
                $services["ids"][] = $s;
                $services["names"][] = '[!] '. $modelServices->getName($id_space, $s, true);
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


        $form->setValidationButton(CoreTranslator::Save($lang), "servicesinvoiceprojectedit/" . $id_space . "/" . $id_invoice . "/0");
        $form->addExternalButton(InvoicesTranslator::GeneratePdf($lang), "servicesinvoiceprojectedit/" . $id_space . "/" . $id_invoice . "/1", "danger", true);
        $form->setFormAdd($formAdd);

        $modelInvoice = new InInvoice();
        $discount = $modelInvoice->getDiscount($id_space, $id_invoice);
        $form->addText("discount", ServicesTranslator::Discount($lang), false, $discount);


        $total = (1-floatval($discount)/100)*$total;
        $form->addNumber("total", InvoicesTranslator::Total_HT($lang), false, $total);
        $form->setColumnsWidth(9, 2);

        return $form;
    }

    protected function createByProjectForm($id_space, $lang)
    {
        $form = new Form($this->request, "ByProjectForm");
        $form->addSeparator(ServicesTranslator::By_projects($lang));

        $modelProject = new SeProject();
        $projects = $modelProject->getOpenedProjectForList($id_space);

        $formAdd = new FormAdd($this->request, "ByProjectFormAdd");
        $formAdd->addSelect("id_project", ServicesTranslator::Project($lang), $projects["names"], $projects["ids"]);
        $formAdd->setButtonsNames(CoreTranslator::Add($lang), CoreTranslator::Delete($lang));

        $form->setFormAdd($formAdd);


        $form->setValidationButton(CoreTranslator::Save($lang), "servicesinvoiceproject/" . $id_space);
        return $form;
    }

    protected function createByPeriodForm($id_space, $lang)
    {
        $form = new Form($this->request, "ByPeriodForm");
        $form->addSeparator(ServicesTranslator::By_period($lang));

        $form->addDate("period_begin", InvoicesTranslator::Period_begin($lang), true, $this->request->getParameterNoException("period_begin"));
        $form->addDate("period_end", InvoicesTranslator::Period_end($lang), true, $this->request->getParameterNoException("period_end"));
        $respId = $this->request->getParameterNoException("id_resp");

        $modelClient = new ClClient();
        $resps = $modelClient->getForList($id_space);

        $form->addSelect("id_resp", ClientsTranslator::ClientAccount($lang), $resps["names"], $resps["ids"], $respId);


        $form->setValidationButton(CoreTranslator::Save($lang), "servicesinvoiceproject/" . $id_space);
        return $form;
    }

    public function invoiceprojectAction($id_space, $id_project)
    {
        $modelProject = new SeProject();
        $id_resp = $modelProject->getResp($id_space, $id_project);

        $id_projects = array();
        $id_projects[] = $id_project;
        $this->invoiceProjects($id_space, $id_projects, $id_resp);
        return $this->redirect("invoices/" . $id_space);
    }

    protected function invoiceProjects($id_space, $id_projects, $id_client, $beginPeriod=null, $endPeriod=null)
    {
        $cv = new CoreVirtual();
        $projects = implode(',', $id_projects);
        $period = '';
        if ($beginPeriod) {
            $period = "$beginPeriod => $endPeriod";
        }
        $rid = $cv->newRequest($id_space, "invoices", "projects[$id_client][$projects]:$period");
        Events::send([
            "action" => Events::ACTION_INVOICE_REQUEST,
            "space" => ["id" => intval($id_space)],
            "user" => ["id" => $_SESSION['id_user']],
            "type" => ServicesInvoice::$INVOICES_SERVICES_PROJECTS_CLIENT,
            "period_begin" => $beginPeriod,
            "period_end" => $endPeriod,
            "id_client" => $id_client,
            "id_projects" => $id_projects,
            "request" => ["id" => $rid]
        ]);
    }

    protected function getProjectsResp($id_space, $id_projects)
    {
        if (empty($id_projects)) {
            throw new PfmParamException("You need to select at least one project");
        }

        $modelProject = new SeProject();

        $id_resp = $modelProject->getResp($id_space, $id_projects[0]);
        for ($i = 1; $i < count($id_projects); $i++) {
            $id_respi = $modelProject->getResp($id_space, $id_projects[$i]);
            if ($id_respi != $id_resp) {
                throw new PfmParamException("Projects must have the same responsible");
            }
        }
        return $id_resp;
    }
}


