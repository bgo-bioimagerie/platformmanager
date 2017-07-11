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
    

    public function genreratePDF($id_space, $number, $date, $unit, $resp, $adress, $table, $total, $useTTC = true, $details = "") {
        
        $adress = nl2br($adress);
        $date = CoreTranslator::dateFromEn($date, 'fr');
        
        $modelInvoice = new InInvoice();
        $invoiceInfo = $modelInvoice->getByNumber($number);
        
        ob_start();
        include('data/invoices/'.$id_space.'/template.php');
        $content = ob_get_clean();
        
        // convert in PDF
        require_once('externals/html2pdf/vendor/autoload.php');
        try {
            $html2pdf = new HTML2PDF('P', 'A4', 'fr');
            //$html2pdf->setModeDebug();
            $html2pdf->setDefaultFont('Arial');
            //$html2pdf->writeHTML($content, isset($_GET['vuehtml']));
            $html2pdf->writeHTML($content);
            //echo "name = " . $unit . "_" . $resp . " " . $number . '.pdf' . "<br/>"; 
            $html2pdf->Output($unit . "_" . $resp . " " . $number . '.pdf');
            return;
        } catch (HTML2PDF_exception $e) {
            echo $e;
            exit;
        }
    }

}
