<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/invoices/Controller/InvoicesController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
abstract class InvoiceAbstractController extends InvoicesController {
    
    /**
     * To desplay the form that allows to edit an order and xport as pdf
     */
    public abstract function editAction($id_space, $id_invoice, $pdf);
    
        /**
     * To delete the invoice data in the content tables
     */
    public abstract function deleteAction($id_space, $id_invoice);
    

    public function generatePDF($id_space, $number, $date, $unit, $resp, $address, $table, $total, $useTTC = true, $details = "", $clientInfos = null, $toFile=false) {
        $address = nl2br($address);
        $adress = $address; // backwark compat
        $date = CoreTranslator::dateFromEn($date, 'fr');
        
        $modelInvoice = new InInvoice();
        $invoiceInfo = $modelInvoice->getByNumber($id_space, $number);

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
            ]);
        }
        
        // convert in PDF
        // require_once('externals/html2pdf/vendor/autoload.php');
        try {
            $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'fr');
            $html2pdf->setDefaultFont('Arial');
            $html2pdf->writeHTML($content);
            if($toFile || getenv("PFM_MODE") == "test") {
                $html2pdf->Output(__DIR__."/../../../data/invoices/$id_space/invoice_".$number.".pdf", 'F');
            } else {
                $html2pdf->Output($unit . "_" . $resp . "_" . $number . '.pdf');
            }
        } catch (Exception $e) {
            throw new PfmException("Pdf generation error: " . $e, 500);
        }
        return __DIR__."/../../../data/invoices/$id_space/invoice_".$number.".pdf";
    }

}
