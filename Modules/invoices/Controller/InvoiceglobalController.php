<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/invoices/Controller/InvoiceAbstractController.php';
require_once 'Modules/invoices/Model/InInvoiceItem.php';
require_once 'Modules/invoices/Model/InInvoice.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/invoices/Model/GlobalInvoice.php';

require_once 'Modules/booking/Model/BkNightWE.php';
require_once 'Modules/booking/Model/BkPrice.php';
require_once 'Modules/booking/Model/BkOwnerPrice.php';
require_once 'Modules/booking/Model/BkCalendarEntry.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';

require_once 'Modules/booking/Model/BookinginvoiceTranslator.php';

require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';

require_once 'Modules/core/Model/CoreVirtual.php';

use Fp\Callable as F;
use Fp\Cast as Fc;
use Fp\Collection as Fp;
use Fp\Evidence as Fv;
use Fp\Functional\Option\Option;

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class InvoiceglobalController extends InvoiceAbstractController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space)
    {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $formAll = $this->createAllForm($id_space, $lang);
        if ($formAll->check()) {
            $beginPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_begin"), $lang);
            $endPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_end"), $lang);

            $cv = new CoreVirtual();
            $rid = $cv->newRequest($id_space, "invoices", "global:$beginPeriod => $endPeriod");
            Events::send([
                "action" => Events::ACTION_INVOICE_REQUEST,
                "space" => ["id" => intval($id_space)],
                "user" => ["id" => $_SESSION['id_user']],
                "type" => GlobalInvoice::$INVOICES_GLOBAL_ALL,
                "period_begin" => $beginPeriod,
                "period_end" => $endPeriod,
                "request" => ["id" => $rid]
            ]);

            $this->redirect("invoices/" . $id_space);
            return;
        }

        $formByPeriod = $this->createByPeriodForm($id_space, $lang);
        if ($formByPeriod->check()) {
            $beginPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_begin"), $lang);
            $endPeriod = CoreTranslator::dateToEn($this->request->getParameter("period_end"), $lang);
            $id_resp = $this->request->getParameter("id_resp");
            if ($id_resp != 0) {
                $cv = new CoreVirtual();
                $rid = $cv->newRequest($id_space, "invoices", "global[$id_resp]:$beginPeriod => $endPeriod");
                Events::send([
                    "action" => Events::ACTION_INVOICE_REQUEST,
                    "space" => ["id" => intval($id_space)],
                    "user" => ["id" => $_SESSION['id_user']],
                    "type" => GlobalInvoice::$INVOICES_GLOBAL_CLIENT,
                    "period_begin" => $beginPeriod,
                    "period_end" => $endPeriod,
                    "id_client" => $id_resp,
                    "request" => ["id" => $rid]
                ]);

                $this->redirect("invoices/" . $id_space);
                return;
            }
        }

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "formByPeriod" => $formByPeriod->getHtml($lang),
            "formAll" => $formAll->getHtml($lang)
        ));
    }

    public function editAction($id_space, $id_invoice, $pdf = false)
    {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelInvoice = new InInvoice();
        $invoice = $modelInvoice->get($id_space, $id_invoice);

        $modelItem = new InInvoiceItem();
        $invoiceitem = $modelItem->getForInvoice($id_space, $id_invoice);

        $validateURL = "invoiceglobaledit/" . $id_space . "/" . $id_invoice;

        $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "invoice" => $invoice,
            "invoiceitem" => $invoiceitem,
            "validateURL" => $validateURL
        ));
    }

    public function detailsAction($id_space, $id_invoice)
    {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelInvoice = new InInvoice();
        $invoice = $modelInvoice->get($id_space, $id_invoice);
        if (!$invoice) {
            throw new PfmParamException('invoice not found');
        }
        $modelItem = new InInvoiceItem();
        $invoiceitem = $modelItem->getForInvoice($id_space, $id_invoice);
        $details = json_decode($invoiceitem['content'], true);

        $mres = new ResourceInfo();
        $musers = new CoreSpace();
        $resources = $mres->getForSpace($id_space);
        $rmap = [];
        foreach ($resources as $r) {
            $rmap[$r['id']] = $r['name'];
        }
        $users= $musers->getUsers($id_space);
        $umap = [];
        foreach ($users as $u) {
            $umap[$u['id']] = $u['firstname'].' '.$u['name'];
        }

        $data = [];
        $others = [];

        foreach ($details as $detail) {
            $module = $detail['module'];
            if ($module == 'booking' && array_key_exists('details', $detail['data'])) {
                foreach ($detail['data']['details'] as $d) {
                    $data[] = [
                        'module' => $module,
                        'id' => $d['id'],
                        'start_time' => date('Y-m-d h-i', $d['start_time']),
                        'end_time' => date('Y-m-d h-i', $d['end_time']),
                        'day' => $d['nb_hours_day'],
                        'night' => $d['nb_hours_night'],
                        'we' => $d['nb_hours_we'],
                        'url' => 'bookingeditreservation/'.$id_space.'/r_'.$d['id'],
                        'user' => $umap[$d['user']] ?? '',
                        'resource' => $rmap[$d['resource']] ?? ''
                    ];
                }
            } else {
                foreach ($detail['data']['count'] as $d) {
                    $oinfo = [
                        'module' => $module,
                        'id' => $d['id'] ?? '',
                        'quantity' => $d['quantity'],
                        'resource' => $d['label'],
                        'info' => ''
                    ];
                    if (isset($d['no_identification'])) {
                        $oinfo['info'] = $d['no_identification'];
                    }
                    $others[] = $oinfo;
                }
            }
        }

        $table = new TableView('bookingDetails');
        $table->setTitle("Bookings - " . $invoice['number'], 3);
        $table->addDownloadButton('url');
        $headers = array("module" => "Module", "id" => "Id", "resource" => "Resource", "user" => "User", "start_time" => "Start", "end_time" => "End", "day" => "Day/H", "night" => "Night/H", "we" => "We/H");
        $tableHtml = '';
        if (!empty($data)) {
            $tableHtml = $table->view($data, $headers);
        }

        $table2 = new TableView('otherDetails');
        $table2->setTitle("Others - " . $invoice['number'], 3);
        $headers2 = array("module" => "Module", "id" => "Id", "resource" => "Resource", "quantity" => "Quantity", "info" => "Info");
        $tableHtml2 = $table2->view($others, $headers2);

        $this->render(['lang' => $lang, 'id_space' => $id_space, 'table' => $tableHtml, 'table2' => $tableHtml2, 'invoice' => $invoice, 'data' => ['invoicedetails' => $details]]);
    }

    public function pdfAction($id_space, $id_invoice, $details = 0)
    {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelInvoice = new InInvoice();
        $invoice = $modelInvoice->get($id_space, $id_invoice);

        $modelItem = new InInvoiceItem();
        $id_item = $modelItem->getInvoiceItems($id_space, $id_invoice)[0]["id"];

        $detailsTable = $details > 0 ? $this->generateDetailsTable($id_space, $id_invoice) : "";

        $this->generatePDFInvoice($id_space, $invoice, $id_item, $lang, $detailsTable);
    }

    protected final function mkInvoiceEntries(int $id_space, int $id_item, string $lang): array
    {
        $modelResources = new ResourceInfo();
        $modelItem = new InInvoiceItem();
        $invoiceItem = $modelItem->getItem($id_space, $id_item);

        $data = Fp\flatMap(json_decode($invoiceItem["content"], true)
                         , fn ($js) => $js["data"]["count"]);

        $groupedLines = Fp\groupMap($data
                                  , fn($d) => $d["resource"]
                                  , fn ($d) => new InvoiceLine($d["label"], $d["unitprice"], $d["quantity"]));

        return Fp\mapKV($groupedLines, F\partial(self::mkInvoiceEntry(...), $modelResources, $id_space));
    }

    /**
     * @param ResourceInfo $modelResources
     * @param int $id_space
     * @param int $id
     * @param array<InvoiceLine> $lines
     * @return InvoiceEntry
     */
    private static function mkInvoiceEntry(ResourceInfo $modelResources, int $id_space, int $id, array $lines): InvoiceEntry
    {
        $resName = $modelResources->getName($id_space, $id) ?? Constants::UNKNOWN;

        $newLines = Fp\map($lines, fn ($line) =>
            str_starts_with($line->name, $resName) ? $line->withName(str_replace($resName, "", $line->name)) : $line);

        return new InvoiceEntry($id, $resName, $newLines);
    }


    public function editqueryAction($id_space, $id_invoice)
    {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $discount = $_POST["discount"];
        $total_ht = $_POST["total_ht"];
        $content = $_POST["content"];

        $modelInvoice = new InInvoice();
        $modelInvoice->setDiscount($id_space, $id_invoice, $discount);
        $modelInvoice->setTotal($id_space, $id_invoice, $total_ht);

        Events::send([
            "action" => Events::ACTION_INVOICE_EDIT,
            "space" => ["id" => intval($id_space)],
            "invoice" => ["id" => intval($id_invoice)]
        ]);


        $modelItem = new InInvoiceItem();
        $modelItem->setItemContent($id_space, $id_invoice, $content);

        echo json_encode(array("status" => "success", "message" => InvoicesTranslator::InvoiceHasBeenSaved($lang)));
    }

    public function deleteAction($id_space, $id_invoice)
    {
        $this->checkAuthorizationMenuSpace("invoices", $id_space, $_SESSION["id_user"]);

        $modules = Configuration::get("modules");
        foreach ($modules as $module) {
            $invoiceModelFile = "Modules/" . strtolower($module) . "/Model/" . ucfirst(strtolower($module)) . "Invoice.php";
            if (file_exists($invoiceModelFile)) {
                require_once $invoiceModelFile;
                $modelName = ucfirst(strtolower($module)) . "Invoice";
                $model = new $modelName();
                $model->delete($id_space, $id_invoice);
            }
        }
        Events::send([
            "action" => Events::ACTION_INVOICE_DELETE,
            "space" => ["id" => intval($id_space)],
            "invoice" => ["id" => intval($id_invoice)]
        ]);
    }

    // ////////////////////////////////////////////////////////////////////// //
    //                      internal methods
    // ////////////////////////////////////////////////////////////////////// //
    protected function createAllForm($id_space, $lang)
    {
        $form = new Form($this->request, "GlobalInvoiceAllForm");
        $form->addSeparator(InvoicesTranslator::Invoice_All($lang));

        $form->addDate("period_begin", InvoicesTranslator::Period_begin($lang), true, $this->request->getParameterNoException("period_begin"));
        $form->addDate("period_end", InvoicesTranslator::Period_end($lang), true, $this->request->getParameterNoException("period_end"));



        $form->setValidationButton(CoreTranslator::Save($lang), "invoiceglobal/" . $id_space);
        return $form;
    }

    protected function createByPeriodForm($id_space, $lang)
    {
        $form = new Form($this->request, "ByPeriodForm");
        $form->addSeparator(InvoicesTranslator::Invoice_Responsible($lang));

        $form->addDate("period_begin", InvoicesTranslator::Period_begin($lang), true, $this->request->getParameterNoException("period_begin"));
        $form->addDate("period_end", InvoicesTranslator::Period_end($lang), true, $this->request->getParameterNoException("period_end"));
        $respId = $this->request->getParameterNoException("id_resp");

        $modelClients = new ClClient();
        $resps = $modelClients->getForList($id_space);

        $form->addSelect("id_resp", ClientsTranslator::ClientAccount($lang), $resps["names"], $resps["ids"], $respId);


        $form->setValidationButton(CoreTranslator::Save($lang), "invoiceglobal/" . $id_space);
        return $form;
    }

    protected function generateDetailsTable($id_space, $invoice_id)
    {
        $lang = $this->getLanguage();

        $html = "";
        $modules = Configuration::get("modules");
        foreach ($modules as $module) {
            $invoiceModelFile = "Modules/" . strtolower($module) . "/Model/" . ucfirst(strtolower($module)) . "Invoice.php";
            if (file_exists($invoiceModelFile)) {
                require_once $invoiceModelFile;
                $modelName = ucfirst(strtolower($module)) . "Invoice";
                $model = new $modelName();

                $details = $model->details($id_space, $invoice_id, $lang);
                if (isset($details["title"])) {
                    $html .= $this->detailsArrayToHtmlTable($details, $lang);
                }
            }
        }
        return $html;
    }

    protected function detailsArrayToHtmlTable($detail, $lang)
    {
        $colWidth = round(100 / count($detail["header"]));

        $table = $detail["title"] . "<br/>";
        $table .= "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; background: #E7E7E7; text-align: center; font-size: 10pt;\">
                    <tr>";

        foreach ($detail["header"] as $key => $value) {
            $table .= '<th style="width: ' . $colWidth . '%">' . $value . '</th>';
        }


        $table .= "</tr>" .
                " </table>";

        $table .= "<table cellspacing=\"0\" style=\"width: 100%; border: solid 1px black; border-collapse: collapse; background: #F7F7F7; text-align: center; font-size: 10pt;\">";

        foreach ($detail["content"] as $d) {
            $table .= "<tr>";

            foreach ($detail["header"] as $key => $value) {
                $table .= '<td style="width: ' . $colWidth . '%; text-align: left; border: solid 1px black;">' . $d[$key] . '</td>';
            }
            $table .= "</tr>";
        }

        $table .= "</table><br/>";
        return $table;
    }
}
