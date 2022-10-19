<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/invoices/Controller/InvoicesController.php';
require_once 'Modules/clients/Model/ClCompany.php';
require_once 'Modules/core/Model/CoreSpace.php';
/**
 *
 * @author sprigent
 * Controller for the home page
 */
abstract class InvoiceAbstractController extends InvoicesController
{
    /**
     * To desplay the form that allows to edit an order and xport as pdf
     */
    abstract public function editAction($idSpace, $id_invoice, $pdf);

    /**
     * To delete the invoice data in the content tables
     */
    abstract public function deleteAction($idSpace, $id_invoice);


    public function generatePDF($idSpace, $invoice_id, $date, $unit, $resp, $address, $table, $total, $useTTC = true, $details = "", $clientInfos = null, $toFile=false, $lang='en')
    {
        $address = nl2br($address);
        $date = CoreTranslator::dateFromEn($date, $lang);

        $modelInvoice = new InInvoice();
        $invoiceInfo = $modelInvoice->get($idSpace, $invoice_id);
        $number = $invoiceInfo['number'];
        $invoiceInfo['module'] = InvoicesTranslator::Module($invoiceInfo['module'], $lang);

        $translator = new InvoicesTranslator();
        $csm = new CoreSpace();
        $space = $csm->getSpace($idSpace);

        $clcm = new ClCompany();
        $company = $clcm->getForSpace($idSpace);
        if (!isset($company['name'])) {
            $company = [
                'name' => $space['name'],
                'address' => '',
                'city' => '',
                'zipcode' => '',
                'country' => '',
                'tel' => '',
                'email' => '',
                'approval_number' => ''
            ];
        }

        if (!file_exists('data/invoices/'.$idSpace.'/template.twig') && file_exists('data/invoices/'.$idSpace.'/template.php')) {
            // backwark, templates were in PHP and no twig template available use old template
            ob_start();
            include('data/invoices/'.$idSpace.'/template.php');
            $content = ob_get_clean();
        } else {
            $template = 'data/invoices/'.$idSpace.'/template.twig';
            if (!file_exists($template)) {
                $template = 'externals/pfm/templates/invoices_template.twig';
            }
            Configuration::getLogger()->debug('[invoices][pdf]', ['template' => $template]);

            $loader = new \Twig\Loader\FilesystemLoader(__DIR__.'/../../..');
            $twig = new \Twig\Environment($loader, []);
            $content = $twig->render($template, [
                'id_space' => $idSpace,
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
                'invoiceInfo' => $invoiceInfo,
                'translator' => $translator,
                'lang' => $lang,
                'company' => $company,
                'space' => $space
            ]);
        }

        // convert in PDF
        $out = __DIR__."/../../../data/invoices/$idSpace/invoice_".$number.".pdf";
        try {
            $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'fr');
            $html2pdf->setDefaultFont('Arial');
            $html2pdf->writeHTML($content);
            if ($toFile || getenv("PFM_MODE") == "test") {
                $html2pdf->Output($out, 'F');
            } else {
                $html2pdf->Output($unit . "_" . $resp . "_" . $number . '.pdf');
            }
        } catch (Exception $e) {
            throw new PfmException("Pdf generation error: " . $e, 500);
        }
        return $out;
    }
}
