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

require_once 'Modules/clients/Model/ClCompany.php';
/**
 *
 * @author sprigent
 * Controller for the home page
 */
class QuotelistController extends QuoteController {

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
        $modelUSerClients = new ClClientUser();
        $data = $model->getAll($id_space);
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]["id_client"] > 0) {
                $data[$i]["address"] = $modelClient->getAddressInvoice($id_space, $data[$i]['id_client']);
                $data[$i]["client_name"] = $modelClient->getName($id_space, $data[$i]['id_client']);
            } else {
                $data[$i]["client_name"] = "";
            }

            if ($data[$i]["id_user"] > 0) {
                $data[$i]["recipient"] = $modelUser->getUserFUllName($data[$i]["id_user"]);
                if ($data[$i]["client_name"] === "") {
                    $resps = $modelUSerClients->getUserClientAccounts($data[$i]["id_user"], $id_space) ?: [];
                    if (!empty($resps)) {
                        $data[$i]["address"] = $modelClient->getAddressInvoice($id_space, $resps[0]['id']);
                        $data[$i]["client_name"] = $resps[0]['name'];
                    }
                }
            }
            
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
            "client_name" => ClientsTranslator::Client($lang),
            "date_open" => QuoteTranslator::DateCreated($lang),
            "date_last_modified" => QuoteTranslator::DateLastModified($lang)
        ));

        return $this->render(array(
            "id_space" => $id_space,
            "lang" => $lang,
            "tableHtml" => $tableHtml,
            "data" => ['quotes' => $data]
        ));
    }

    public function editAction($id_space, $id): ?array {
        $this->checkAuthorizationMenuSpace("quote", $id_space, $_SESSION["id_user"]);
        $modelQuote = new Quote();
        $info = $modelQuote->get($id_space, $id);

        if ($info["id_user"] > 0) {
            return $this->editexistinguserAction($id_space, $id);
        } else {
            return $this->editnewuserAction($id_space, $id);
        }
    }

    public function editexistinguserAction($id_space, $id): ?array {
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
        $form->addHidden('id_space', $id_space);
        $form->addSelectMandatory('id_user', CoreTranslator::User($lang), $users["names"], $users["ids"], $info['id_user']);

        $clientSelect['choices'] = [""];
        $clientSelect['choicesid'] = [""];
        $clientSelect['value'] = "";

        if ($id > 0) {
            $form->addText('date_open', QuoteTranslator::DateCreated($lang), false, CoreTranslator::dateFromEn($info['date_open'], $lang), 'disabled');
            $form->addHidden('date_open', $info['date_open']);
            $modelClientUser = new ClClientUser();
            $userClients = $modelClientUser->getUserClientAccounts($info['id_user'], $id_space) ?: [];
            foreach($userClients as $client) {
                array_push($clientSelect['choices'], $client['name']);
                array_push($clientSelect['choicesid'], $client['id']);
            }
            $clientSelect['value'] = ($info['id_client'] != 0) ? $info['id_client'] : $userClients[0]['id'] ?? "";
        } else {
            $form->addHidden('date_open', date('Y-m-d'));
        }

        $form->addSelectMandatory('id_client', ClientsTranslator::Client($lang), $clientSelect['choices'], $clientSelect['choicesid'], $clientSelect['value']);
        $form->setButtonsWidth(2, 10);
        $form->setValidationButton(CoreTranslator::Save($lang), "quoteuser/" . $id_space . "/" . $id);
        
        if ($form->check()) {
            $modelPricing = new ClPricing();
            $pricing = $modelPricing->getPricingByClient($id_space,$form->getParameter('id_client'));
            if (!$pricing || $pricing === null) {
                $_SESSION['flash'] = QuoteTranslator::pricingNeeded($lang);
                $_SESSION['flashClass'] = "danger";
                return $this->redirect("quoteuser/" . $id_space . "/" . $id, [], ['error' => 'pricingNeeded']);
            }
            $id = $modelQuote->set(
                $id,
                $id_space,
                "",
                "",
                "",
                0,
                $form->getParameter('id_user'),
                $form->getParameter('id_client'),
                $this->request->getParameterNoException('date_open')
            );
            $_SESSION["flash"] = QuoteTranslator::QuoteHasBeenSaved($lang);
            $_SESSION["flashClass"] = "success";
            return $this->redirect("quoteuser/" . $id_space . "/" . $id, [], ['quote' => ['id' => $id]]);
        }

        $formitemHtml = $this->createItemForm($id_space);

        return $this->render(array(
            "id_space" => $id_space,
            'id_quote' => $id,
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang),
            "tableHtml" => $tableHtml,
            'items' => $items,
            'formitemHtml' => $formitemHtml,
            'data' => ['quote' => $info, 'items' => $items]
        ), 'editexistinguserAction');
    }

    protected function createItemForm($id_space) {

        $lang = $this->getLanguage();
        $form = new Form($this->request, "createItemForm");
        $form->setTitle(QuoteTranslator::FormItem($lang));

        $form->addHidden("id");
        $form->addHidden("id_quote");

        $modelItem = new QuoteItem();
        $itemslist = $modelItem->getList($id_space);

        $form->addSelectMandatory('id_item', QuoteTranslator::ItemLabel($lang), $itemslist["names"], $itemslist["ids"]);
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

    public function editnewuserAction($id_space, $id): ?array {
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
        $modelClient = new ClClient();
        $clients = $modelClient->getAll($id_space) ?: [];
        $clientIds = ["0"];
        $clientNames = ["-- --"];
        forEach($clients as $client) {
            array_push($clientIds, $client['id']);
            array_push($clientNames, $client['name']);
        }
        // set selected client by default
        $selectedClientId = $info['id_client'] ?? $clientIds[0];
        $addressToDisplay = "";
        if ($info['address'] && $info['address'] != "") {
            $addressToDisplay = $info['address'];
        } else {
            $addressToDisplay = $modelClient->getAddressInvoice($id_space, $selectedClientId) ?? "";
        }
        $form->addHidden('id_space', $id_space);
        $form->addText("recipient", QuoteTranslator::Recipient($lang), true, $info['recipient']);
        $form->addEmail("recipient_email", QuoteTranslator::Recipient($lang) . " " . CoreTranslator::Email($lang), false, $info['recipient_email']);
        $form->addTextArea("address", ClientsTranslator::Client($lang) . " " . QuoteTranslator::Address($lang), true, $addressToDisplay);
        $form->addSelectMandatory('id_client', ClientsTranslator::Client($lang), $clientNames, $clientIds, $selectedClientId);

        $modelPricing = new ClPricing();
        if ($id > 0) {
            // if quote has a client: get this client's pricing
            if ($info['id_client'] && $info['id_client'] != 0) {
                // (A client has 0 to 1 pricing)
                $pricingName = $modelPricing->getPricingByClient($id_space, $info['id_client'])[0]['name'];
                // else if quote has a pricing: get it
            } else if ($info['id_belonging'] && $info['id_belonging'] != 0 ) {
                $pricingName = $modelPricing->getName($id_space, $info['id_belonging']);
            }
        }
        $form->addText('pricing', ClientsTranslator::Pricing($lang), false, $pricingName ?? "", true, true);

        if ($id > 0) {
            $form->addText('date_open', QuoteTranslator::DateCreated($lang), false, CoreTranslator::dateFromEn($info['date_open'], $lang), 'disabled', $info['date_open']);
            $form->addHidden('date_open', CoreTranslator::dateFromEn($info['date_open'], $lang));
        } else {
            $form->addHidden('date_open', date('Y-m-d'));
        }
        $form->setButtonsWidth(2, 10);
        $form->setValidationButton(CoreTranslator::Save($lang), "quotenew/" . $id_space . "/" . $id);
        if ($form->check()) {
            $pricing = $modelPricing->getPricingByClient($id_space,$form->getParameter('id_client'));
            if (!$pricing || $pricing === null) {
                $_SESSION['flash'] = QuoteTranslator::pricingNeeded($lang);
                $_SESSION['flashClass'] = "danger";
                return $this->redirect("quotenew/" . $id_space . "/" . $id, [], ['error' => 'pricingNeeded']);
            }
            $quote_id = $modelQuote->set(
                    $id,
                    $id_space,
                    $form->getParameter('recipient'),
                    $form->getParameter('recipient_email'),
                    $form->getParameter('address'),
                    0,
                    0,
                    $form->getParameter('id_client'),
                    $form->getParameter('date_open')
            );
            $_SESSION['flash'] = QuoteTranslator::QuoteHasBeenSaved($lang);
            if ($id > 0) {
                return $this->redirect("quotenew/" . $id_space . "/" . $id, [], ['quote' => ['id' => $quote_id]]);
            }
            return $this->redirect("quote/" . $id_space, [], ['quote' => ['id' => $quote_id]]);
        }

        $formitemHtml = $this->createItemForm($id_space);

        return $this->render(array(
            "id_space" => $id_space,
            'id_quote' => $id,
            "lang" => $lang,
            "formHtml" => $form->getHtml($lang), "tableHtml" => $tableHtml,
            "formitemHtml" => $formitemHtml,
            "items" => $items,
            "data" => ['quote' => $info, 'items' => $items]
        ), 'editnewuserAction');
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
        $id_item = $modelItem->setItem($id_space, $id, $id_quote, $id_content, $module, $quantity, $comment);

        $modelQuote = new Quote();
        $quote = $modelQuote->get($id_space, $id_quote);
        if ($quote["id_user"] == 0) {
            return $this->redirect("quotenew/" . $quote["id_space"] . '/' . $quote["id"], [], ['quote' => ['id' => $id_quote], 'item' => ['id' => $id_item]]);
        } else {
            return $this->redirect("quoteuser/" . $quote["id_space"] . '/' . $quote["id"], [], ['quote' => ['id' => $id_quote], 'item' => ['id' => $id_item]]);
        }
    }

    public function pdfAction($id_space, $id) {
        $this->checkAuthorizationMenuSpace("quote", $id_space, $_SESSION["id_user"]);

        $modelQuote = new Quote();
        $info = $modelQuote->getAllInfo($id_space, $id);

        // get the list of items
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
        $resp = $info["recipient"];
        $clientInfos = ['email' => ''];
        if (is_array($info["client"]) && !empty($info["client"])) {
            $clientInfos = $info["client"];
            $clientInfos["email"] = $info["client"]["email"] ?? "";
        }
        $date = CoreTranslator::dateFromEn(date('Y-m-d'), 'fr');
        $useTTC = true;
        $details = "";
        $invoiceInfo["title"] = "";
        $invoiceInfo["module"] = QuoteTranslator::quote($lang);
        $number = "";
        $unit = "";

        $this->generatePDF($id_space, [
            'id' => $id,
            'number' => $number,
            'date' => $date,
            'unit' => $unit,
            'resp' => $resp,
            'address' => $address,
            'table' => $table,
            'total' => $total,
            'useTTC' => $useTTC,
            'details' => $details,
            'clientInfos' => $clientInfos,
            'quoteInfos' => $invoiceInfo,
        ], $lang);
    }

    public function generatePDF($id_space, $data, $lang='en', $toFile=false) {

        $id = $data['id'];
        $number = $data['number'];
        $date = $data['date'];
        $unit = $data['unit'] ?? '';
        $resp = $data['resp'];
        $address = $data['address'];
        $adress = $data['address'];
        $table = $data['table'];
        $total = $data['total'];
        $useTTC = $data['useTTC'];
        $details = $data['details'];
        $clientInfos = $data['clientInfos'];
        $quoteInfos = $data['quoteInfos'];
        $isQuote = true;
        $space = $this->currentSpace;

        $translator = new QuoteTranslator();

        $clcm = new ClCompany();
        $company = $clcm->getForSpace($id_space);
        if(!isset($company['name'])) {
            $company = [
                'name' => $this->currentSpace['name'],
                'address' => '',
                'city' => '',
                'zipcode' => '',
                'country' => '',
                'tel' => '',
                'email' => '',
                'approval_number' => ''
            ];
        }


        if(!file_exists('data/quote/'.$id_space.'/template.twig') && file_exists('data/quote/'.$id_space.'/template.php')) {
            // backwark, templates were in PHP and no twig template available use old template
            $template = 'data/quote/'.$id_space.'/template.php';
            Configuration::getLogger()->debug('[quote][pdf]', ['template' => $template]);
            ob_start();
            include($template);
            $content = ob_get_clean();
        } else {
            $template = 'data/quote/'.$id_space.'/template.twig';
            if(!file_exists($template)){
                $template = 'externals/pfm/templates/quote_template.twig';
            }
            Configuration::getLogger()->debug('[quote][pdf]', ['template' => $template]);

            $loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../../..');
            $twig = new \Twig\Environment($loader, []);
            $content = $twig->render($template, [
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
                'clientInfos' => $clientInfos,
                'invoiceInfo' => $quoteInfos,
                'isquote' => $isQuote,
                'translator' => $translator,
                'lang' => $lang,
                'company' => $company,
                'space' => $space
            ]);
        }

        // convert in PDF
        try {
            $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'fr');
            $html2pdf->setDefaultFont('Arial');
            $html2pdf->writeHTML($content);

            if($toFile || getenv("PFM_MODE") == "test") {
                $html2pdf->Output(__DIR__."/../../../data/quote/$id_space/quote_".$resp.$number.".pdf", 'F');
            } else {
                $html2pdf->Output("quote_" . $resp . '.pdf');
            }
            
        } catch (Exception $e) {
           throw new PfmException('PDF conversion error: '.$e->getMessage());
        }
        return __DIR__."/../../../data/quote/$id_space/quote_".$resp.$number.".pdf";
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
        foreach ($tableData as $d) {

            $table .= "<tr>";
            $table .= "<td style=\"width: 52%; text-align: left; border: solid 1px black;\">" . $d["name"] . " " . $d["comment"] . "</td>";
            $table .= "<td style=\"width: 14%; border: solid 1px black;\">" . number_format($d["quantity"], 2, ',', ' ') . "</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . number_format($d['unit_price'], 2, ',', ' ') . " &euro;</td>";
            $table .= "<td style=\"width: 17%; text-align: right; border: solid 1px black;\">" . number_format($d['unit_price'] * $d['quantity'], 2, ',', ' ') . " &euro;</td>";
            $table .= "</tr>";
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
