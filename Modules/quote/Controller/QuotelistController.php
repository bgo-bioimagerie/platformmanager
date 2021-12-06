<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/quote/Model/QuoteTranslator.php';

require_once 'Modules/quote/Model/Quote.php';
require_once 'Modules/quote/Model/QuoteItem.php';

require_once 'Modules/resources/Model/ResourceInfo.php';
require_once 'Modules/booking/Model/BkPrice.php';

require_once 'Modules/services/Model/SeService.php';
require_once 'Modules/services/Model/SePrice.php';

require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';


require_once 'Modules/clients/Model/ClClient.php';
require_once 'Modules/clients/Model/ClClientUser.php';

require_once 'Modules/quote/Controller/QuoteController.php';
/**
 *
 * @author sprigent
 * Controller for the home page
 */
class QuotelistController extends QuoteController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);
        //$this->checkAuthorizationMenu("quote");

    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $this->checkAuthorizationMenuSpace("quote", $id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $model = new Quote();
        $modelUser = new CoreUser();
        $modelClient = new ClClient();
        $pricings = new ClPricing();
        $modelUSerClients = new ClClientUser();
        $data = $model->getAll($id_space);
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]["id_user"] > 0) {
                $data[$i]["recipient"] = $modelUser->getUserFUllName($data[$i]["id_user"]);
                $resps = $modelUSerClients->getUserClientAccounts($data[$i]["id_user"], $id_space);
                if (count($resps) > 0) {
                    $unitID = $modelClient->getInstitution($id_space, $resps[0]['id']);
                    $data[$i]["address"] = $modelClient->getAddressInvoice($id_space, $resps[0]['id']);
                    $data[$i]["id_belonging"] = $modelClient->getPricingID($id_space, $resps[0]['id']);
                }
            }
            $data[$i]["belonging"] = $pricings->getName($id_space, $data[$i]["id_belonging"]);
            $data[$i]["date_open"] = CoreTranslator::dateFromEn($data[$i]["date_open"], $lang);
            $data[$i]["date_last_modified"] = CoreTranslator::dateFromEn($data[$i]["date_last_modified"], $lang);
        }

        $table = new TableView();
        $table->setTitle(QuoteTranslator::Quotes($lang));
        $table->addLineEditButton("quoteedit/" . $id_space);
        $table->addDeleteButton("quotedelete/" . $id_space, "id", "id");
        $tableHtml = $table->view($data, array("id" => "ID",
            "recipient" => QuoteTranslator::Recipient($lang),
            "address" => CoreTranslator::Address($lang),
            "belonging" => CoreTranslator::Belonging($lang),
            "date_open" => QuoteTranslator::DateCreated($lang),
            "date_last_modified" => QuoteTranslator::DateLastModified($lang)
        ));

        $this->render(array("id_space" => $id_space, "lang" => $lang, "tableHtml" => $tableHtml));
    }

    public function editAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("quote", $id_space, $_SESSION["id_user"]);
        $modelQuote = new Quote();
        $info = $modelQuote->get($id_space, $id);

        if ($info["id_user"] > 0) {
            $this->editexistinguserAction($id_space, $id);
        } else {
            $this->editnewuserAction($id_space, $id);
        }
    }

    public function editexistinguserAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("quote", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        // items table
        if ($id > 0) {
            $tableHtml = $this->createItemsTable($id_space, $id);
        } else {
            $tableHtml = "";
        }

        // information form
        $modelQuote = new Quote();
        $info = $modelQuote->get($id_space, $id);
        $modelQuoteitem = new QuoteItem();
        $items = $modelQuoteitem->getAll($id_space, $id);

        $form = new Form($this->request, "editexistinguserForm");

        if ($tableHtml != "") {
            $form->addSeparator2(QuoteTranslator::Description($lang));
        }

        $modelUser = new CoreUser();
        $users = $modelUser->getSpaceActiveUsersForSelect($id_space, "name");
        $form->addSelect('id_user', CoreTranslator::User($lang), $users["names"], $users["ids"], $info['id_user']);

        if ($id > 0) {
            $form->addText('date_open', QuoteTranslator::DateCreated($lang), false, CoreTranslator::dateFromEn($info['date_open'], $lang), 'disabled');
            //$form->addHidden('date_open', CoreTranslator::dateFromEn($info['date_open'], $lang));
        } else {
            $form->addHidden('date_open', date('Y-m-d'));
        }

        $form->setButtonsWidth(2, 10);
        $form->setValidationButton(CoreTranslator::Save($lang), "quoteuser/" . $id_space . "/" . $id);

        if ($form->check()) {

            //echo "date open = " . $form->getParameter('date_open') . "<br/>";
            //return;
            $id = $modelQuote->set($id, $id_space, "", "", "", $form->getParameter('id_user'),
                    $this->request->getParameterNoException('date_open')
            );
            $_SESSION['message'] = QuoteTranslator::QuoteHasBeenSaved($lang);
            $this->redirect("quoteuser/" . $id_space . "/" . $id);
            return;
        }

        $formitemHtml = $this->createItemForm($id_space);

        $this->render(array("id_space" => $id_space, 'id_quote' => $id, "lang" => $lang,
            "formHtml" => $form->getHtml($lang), "tableHtml" => $tableHtml,
            'items' => $items, 'formitemHtml' => $formitemHtml), 'editexistinguserAction');
    }

    protected function createItemForm($id_space) {

        $lang = $this->getLanguage();
        $form = new Form($this->request, "createItemForm");
        $form->setTitle(QuoteTranslator::FormItem($lang));

        $form->addHidden("id");
        $form->addHidden("id_quote");

        $modelItem = new QuoteItem();
        $itemslist = $modelItem->getList($id_space);

        $form->addSelect('id_item', QuoteTranslator::Presta($lang), $itemslist["names"], $itemslist["ids"]);
        $form->addText("quantity", QuoteTranslator::Quantity($lang), true);
        $form->addTextArea("comment", QuoteTranslator::Comment($lang));

        $form->setValidationButton(CoreTranslator::Save($lang), "quoteedititem/" . $id_space);
        return $form->getHtml($lang);
    }

    protected function createItemsTable($id_space, $id_quote) {

        $lang = $this->getLanguage();

        $modelQuoteItem = new QuoteItem();
        $modelResource = new ResourceInfo();
        $modelServices = new SeService();
        $items = $modelQuoteItem->getAll($id_space, $id_quote);
        for ($i = 0; $i < count($items); $i++) {
            if ($items[$i]["module"] == "booking") {
                $items[$i]["name"] = $modelResource->getName($id_space, $items[$i]["id_content"]);
            }
            if ($items[$i]["module"] == "services") {
                $items[$i]["name"] = $modelServices->getItemName($id_space, $items[$i]["id_content"]);
            }
        }
        $headers = array(
            "name" => CoreTranslator::Name($lang),
            "quantity" => QuoteTranslator::Quantity($lang),
            "comment" => QuoteTranslator::Comment($lang),
        );


        $table = new TableView();
        $table->addLineEditButton("edititem", 'id', true);
        $table->addDeleteButton("quoteitemdelete");
        return $table->view($items, $headers);
    }

    public function editnewuserAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("quote", $id_space, $_SESSION["id_user"]);

        $lang = $this->getLanguage();

        // items table
        if ($id > 0) {
            $tableHtml = $this->createItemsTable($id_space, $id);
        } else {
            $tableHtml = "";
        }

        $modelQuote = new Quote();
        $info = $modelQuote->get($id_space, $id);
        $modelQuoteitem = new QuoteItem();
        $items = $modelQuoteitem->getAll($id_space, $id);

        $form = new Form($this->request, "editexistinguserForm");

        if ($tableHtml != "") {
            $form->setTitle(QuoteTranslator::Description($lang));
        }

        $form->addText("recipient", QuoteTranslator::Recipient($lang), true, $info['recipient']);
        $form->addTextArea("address", QuoteTranslator::Address($lang), true, $info['address']);


        $belModel = new ClPricing();
        $bel = $belModel->getForList($id_space);
        $form->addSelect('id_belonging', ClientsTranslator::Pricings($lang), $bel["names"], $bel["ids"], $info['id_belonging']);
        if ($id > 0) {
            $form->addText('date_open', QuoteTranslator::DateCreated($lang), false, CoreTranslator::dateFromEn($info['date_open'], $lang), 'disabled', $info['date_open']);
            $form->addHidden('date_open', CoreTranslator::dateFromEn($info['date_open'], $lang));
        } else {
            $form->addHidden('date_open', date('Y-m-d'));
        }
        $form->setButtonsWidth(2, 10);
        $form->setValidationButton(CoreTranslator::Save($lang), "quotenew/" . $id_space . "/" . $id);
        if ($form->check()) {
            $id = $modelQuote->set($id, $id_space, $form->getParameter('recipient'),
                    $form->getParameter('address'),
                    $form->getParameter('id_belonging'), 0,
                    $form->getParameter('date_open')
            );
            $_SESSION['message'] = QuoteTranslator::QuoteHasBeenSaved($lang);
            $this->redirect("quotenew/" . $id_space . "/" . $id);
            return;
        }

        $formitemHtml = $this->createItemForm($id_space);

        $this->render(array("id_space" => $id_space, 'id_quote' => $id, "lang" => $lang,
            "formHtml" => $form->getHtml($lang), "tableHtml" => $tableHtml,
            "formitemHtml" => $formitemHtml,
            "items" => $items), 'editnewuserAction');
    }

    public function edititemAction($id_space) {
        $this->checkAuthorizationMenuSpace("quote", $id_space, $_SESSION["id_user"]);

        $id_quote = $this->request->getParameter("id_quote");
        $id = $this->request->getParameter("id");
        $id_contentform = $this->request->getParameter("id_item");
        $quantity = $this->request->getParameter("quantity");
        $comment = $this->request->getParameter("comment");

        $id_contentformArray = explode("_", $id_contentform);
        $module = $id_contentformArray[0];
        $id_content = $id_contentformArray[1];


        $modelItem = new QuoteItem();
        $modelItem->setItem($id_space, $id, $id_quote, $id_content, $module, $quantity, $comment);

        $modelQuote = new Quote();
        $quote = $modelQuote->get($id_space, $id_quote);
        if ($quote["id_user"] == 0) {
            $this->redirect("quotenew/" . $quote["id_space"] . '/' . $quote["id"]);
        } else {
            $this->redirect("quoteuser/" . $quote["id_space"] . '/' . $quote["id"]);
        }
    }

    public function pdfAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("quote", $id_space, $_SESSION["id_user"]);

        // get the list of items
        $modelQuote = new Quote();
        $info = $modelQuote->getAllInfo($id_space, $id);

        $modelQuoteitems = new QuoteItem();
        $items = $modelQuoteitems->getAll($id_space, $id);
        $table = array();
        $modelBooking = new ResourceInfo();
        $modelBookingPrices = new BkPrice();
        $modelServices = new SeService();
        $modelServicesPrices = new SePrice();
        $total = 0;
        foreach ($items as $item) {
            if ($item['module'] == "booking") {
                $name = $modelBooking->getName($id_space, $item['id_content']);
                $quantity = $item['quantity'];
                $unitprice = $modelBookingPrices->getPrice($id_space, $item['id_content'], $info['id_pricing']);
                $itemtotal = floatval($quantity) * floatval($unitprice);
            } else if ($item['module'] == "services") {
                $name = $modelServices->getItemName($id_space, $item['id_content']);
                $quantity = $item['quantity'];
                $unitprice = $modelServicesPrices->getPrice($id_space, $item['id_content'], $info['id_pricing']);
                $itemtotal = floatval($quantity) * floatval($unitprice);
            }
            $table[] = array('name'=> $name, 'comment'=> $item['comment'], 'quantity'=>$quantity, 'unit_price'=>$unitprice, 'total'=>$itemtotal);
            $total += $itemtotal;
        }

        $lang = $this->getLanguage();
        $table = $this->makePDFTable($table, $lang);

        // generate pdf
        $address = nl2br($info["address"]);
        $adress = $address; // backwark compat
        $resp = $info["recipient"];
        $date = CoreTranslator::dateFromEn(date('Y-m-d'), 'fr');
        $useTTC = true;
        $isquote = true;

        $details = "";
        $invoiceInfo["title"] = "";
        $number = "";
        $unit = "";

        if(!file_exists('data/invoices/'.$id_space.'/template.twig') && !file_exists('data/invoices/'.$id_space.'/template.php')) {
            throw new PfmFileException("No template found", 404);
        }

        if(!file_exists('data/invoices/'.$id_space.'/template.twig') && file_exists('data/invoices/'.$id_space.'/template.php')) {
            // backwark, templates were in PHP and no twig template available use old template
            ob_start();
            include('data/invoices/'.$id_space.'/template.php');
            $content = ob_get_clean();
        } else {
            $loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../../..');
            $twig = new \Twig\Environment($loader, []);
            $content = $twig->render('data/invoices/'.$id_space.'/template.twig', [
                'id_space' => $id_space,
                'id' => $id,
                'number' => $number,
                'date' => $date,
                'unit' => $unit,
                'resp' => $resp,
                'address' => $address,
                'adress' => $address,  // backward compat
                'table' => $table,
                'total' => $total,
                'useTTC' => $useTTC,
                'details' => $details,
                'clientsInfos' => null,
                'invoiceInfo' => $invoiceInfo,
                'isquote' => $isquote
            ]);
        }




        // convert in PDF
        // require_once('externals/html2pdf/vendor/autoload.php');
        try {
            $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'fr');
            //$html2pdf->setModeDebug();
            $html2pdf->setDefaultFont('Arial');
            //$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
            $html2pdf->writeHTML($content);
            //echo "name = " . $unit . "_" . $resp . " " . $number . '.pdf' . "<br/>";
            $html2pdf->Output(QuoteTranslator::quote($lang) . "_" . $resp . '.pdf');
            return;
        } catch (Exception $e) {
            echo $e;
            exit;
        }
    }

    private function makePDFTable($tableData, $lang) {

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

        //print_r($invoice);
        $total = 0;
        foreach ($tableData as $d) {

            $table .= "<tr>";
            $table .= "<td style=\"width: 52%; text-align: left; border: solid 1px black;\">" . $d["name"] . " " . $d["comment"] . "</td>";
            $table .= "<td style=\"width: 14%; border: solid 1px black;\">" . number_format($d["quantity"], 2, ',', ' ') . "</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . number_format($d['unit_price'], 2, ',', ' ') . " &euro;</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . number_format($d['unit_price'] * $d['quantity'], 2, ',', ' ') . " &euro;</td>";
            $table .= "</tr>";
            $total += $d['total'];
        }
        $table .= "</table>";
        return $table;
    }

    public function deleteAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("quote", $id_space, $_SESSION["id_user"]);

        $modelQuote = new Quote();
        $modelQuote->delete($id_space, $id);

        $this->redirect("quote/" . $id_space);
    }

    public function itemdelete($id_space, $id_item) {
        $this->checkAuthorizationMenuSpace("quote", $id_space, $_SESSION["id_user"]);

        $modelQuote = new QuoteItem();
        $info = $modelQuote->get($id_space, $id_item);
        $modelQuote->delete($id_space, $id_item);

        $this->redirect("quotedit/" . $id_space . '/' . $info["id_quote"]);
    }

}
