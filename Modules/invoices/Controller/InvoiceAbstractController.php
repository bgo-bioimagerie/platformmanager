<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
abstract class InvoiceAbstractController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("invoices");
    }
    
    /**
     * To desplay the form that allows to edit an order and xport as pdf
     */
    public abstract function editAction($id_space, $id_invoice, $pdf);
    
        /**
     * To delete the invoice data in the content tables
     */
    public abstract function deleteAction($id_space, $id_invoice);
    

    public function generatePDF($id_space, $number, $date, $unit, $resp, $address, $table, $total, $useTTC = true, $details = "", $clientInfos = null) {
        $address = nl2br($address);
        $date = CoreTranslator::dateFromEn($date, 'fr');
        
        $modelInvoice = new InInvoice();
        $invoiceInfo = $modelInvoice->getByNumber($id_space, $number);
        
        if(!file_exists('data/invoices/'.$id_space.'/template.twig') && file_exists('data/invoices/'.$id_space.'/template.php')) {
            // backwark, templates were in PHP and no twig template available use old template
            ob_start();
            include('data/invoices/'.$id_space.'/template.php');
            $content = ob_get_clean();
        } else {
            $content = $this->twig->render('data/invoices/'.$id_space.'/template.twig', [
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
                'clientsInfos' => $clientInfos,
                'invoiceInfo' => $invoiceInfo,
            ]);
        }
        
        // convert in PDF
        // require_once('externals/html2pdf/vendor/autoload.php');
        try {
            $html2pdf = new \Spipu\Html2Pdf\Html2Pdf('P', 'A4', 'fr');
            $html2pdf->setDefaultFont('Arial');
            $html2pdf->writeHTML($content);
            $html2pdf->Output($unit . "_" . $resp . " " . $number . '.pdf');
            return;
        } catch (Exception $e) {
            echo $e;
            exit;
        }
    }

}
