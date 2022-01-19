<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Framework/FileUpload.php';
require_once 'Framework/Errors.php';


require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/invoices/Model/InvoicesInstall.php';
require_once 'Modules/invoices/Model/InvoicesTranslator.php';
require_once 'Modules/invoices/Controller/InvoiceglobalController.php';
/**
 *
 * @author sprigent
 * Controller for the home page
 */
class InvoicesconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null) {
        parent::__construct($request, $space);

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }


    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        $modelCoreConfig = new CoreConfig();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($id_space, 'invoices', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($id_space, 'invoices', 'euro');
            return $this->redirect("invoicesconfig/" . $id_space);
        }

        // period invoices
        $formPeriod = $this->periodForm($modelCoreConfig, $id_space, $lang);
        if ($formPeriod->check()) {
            $modelCoreConfig->setParam("invoiceperiodbegin", CoreTranslator::dateToEn($this->request->getParameter("invoiceperiodbegin"), $lang), $id_space);
            $modelCoreConfig->setParam("invoiceperiodend", CoreTranslator::dateToEn($this->request->getParameter("invoiceperiodend"), $lang), $id_space);

            $this->redirect("invoicesconfig/" . $id_space);
            return;
        }

        // options
        $formUseInvoiceDatePaid = $this->useInvoiceDatePaidForm($id_space, $lang);
        if ($formUseInvoiceDatePaid->check()) {
            $modelCoreConfig->setParam("useInvoiceDatePaid", $this->request->getParameter("useInvoiceDatePaid"), $id_space);
            $this->redirect("invoicesconfig/" . $id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
            $formPeriod->getHtml($lang)
        );
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    function checkTemplate($id_space) {
        $client = (new ClClient())->get($id_space, 0);
        $client["name"] = "my client";
        $client["address_delivery"] = "in britany of course";
        $client["address_invoice"] = "somewhere in France";
        $client["phone"] = "112";
        $client["email"] = "do-not-reply@pfm.org";

        $date = date('Y-m-d');
        $address = "Somewhere\nover the\nrainbow";
        $total = 100;
        $resp = "A pfm user";
        $unit = "My prefered customer";
        $number = 'fake';
        $table = '<table class="table"><thead><tr><th>invoice details</th></tr></thead><tbody><tr><td>some details</td></tr></tbody></table>';

        $dest = null;
        try {
            $c = new InvoiceglobalController($this->request, $this->currentSpace);
            $dest = $c->generatePDF($id_space, $number, $date, $unit, $resp, $address, $table, $total, true, "", $client, true);
        } catch(Exception $e) {
            if(file_exists('data/invoices/' . $id_space . '/template.twig.save')) {
                // backup
                Configuration::getLogger()->debug('[invoices][template] revert existing template');
                copy('data/invoices/' . $id_space . '/template.twig.save', 'data/invoices/' . $id_space . '/template.twig');
                unlink('data/invoices/' . $id_space . '/template.twig.save');
            }
            Configuration::getLogger()->debug('[invoices][template] invalid template', ['error' => $e->getMessage()]);
            throw new PfmParamException('Invalid template: '.$e->getMessage());
        }
        return $dest;
    }

    public function pdftemplateAction($id_space) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $currentTemplate = 'data/invoices/' . $id_space . '/template.twig';
        if (! file_exists($currentTemplate)) {
            $currentTemplate = 'data/invoices/' . $id_space . '/template.php';
            if (! file_exists($currentTemplate)) {
                $currentTemplate = null;
            }
        }

        $formDownload = new Form($this->request, "formDownloadTemplate");
        $formDownload->setTitle(InvoicesTranslator::currentTemplate($lang));
        $hasTemplate = false;
        if(file_exists('data/invoices/' . $id_space . '/template.twig')) {
            $formDownload->addDownloadButton("url", InvoicesTranslator::Download($lang), 'template.twig', true);
            $hasTemplate = true;
        } else if (file_exists('data/invoices/' . $id_space . '/template.php')) {
            $formDownload->addDownloadButton("url", InvoicesTranslator::Download($lang), 'template.php', true);
            $hasTemplate = true;
        } else {
            $formDownload->addDownloadButton("url", InvoicesTranslator::DownloadTemplate($lang), 'externals/pfm/templates/invoice_template.twig', true);
        }
        if ($formDownload->check()) {

            $file = $this->request->getParameter('url');
            if(!str_starts_with($file, 'externals')) {
                $file = 'data/invoices/' . $id_space . '/'.$file;
            }
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$file");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");

            // read the file from disk
            readfile($file);
            return;
        }

        $formPreview = new Form($this->request, "formPreviewTemplate");
        $formPreview->setTitle('Preview');
        if($hasTemplate) {
            $formPreview->addDownloadButton("url", "Preview", "template");
        }
        if($formPreview->check()) {
            Configuration::getLogger()->debug('[invoice][template] preview');
            if(!file_exists('data/invoices/' . $id_space . '/template.twig') && !file_exists('data/invoices/' . $id_space . '/template.php')) {
               throw new PfmParamException('no template available');
            }
            $f = $this->checkTemplate($id_space);
            Configuration::getLogger()->debug('[invoice][template] shoud show', ['f' => $f]);
            $mime = mime_content_type($f);
            header('Content-Description: File Transfer');
            header('Content-Type: '.$mime);
            header('Content-Disposition: attachment; filename="invoice.pdf"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($f));
            readfile($f);
            return;
        }   

        $formUpload = new Form($this->request, "formUploadTemplate");
        $formUpload->setTitle(InvoicesTranslator::uploadTemplate($lang));
        $formUpload->addUpload("template", "");
        $formUpload->setValidationButton(CoreTranslator::Ok($lang), "invoicepdftemplate/" . $id_space);
        $formUpload->setColumnsWidth(0, 12);
        $formUpload->setButtonsWidth(2, 10);
        if ($formUpload->check()) {
            if (!file_exists('data/invoices/' . $id_space)) {
                mkdir('data/invoices/' . $id_space, 0777, true);
            }

            if(file_exists('data/invoices/' . $id_space . '/template.twig')) {
                // backup
                Configuration::getLogger()->debug('[invoices][template] backup existing template');
                copy('data/invoices/' . $id_space . '/template.twig', 'data/invoices/' . $id_space . '/template.twig.save');
            }
            FileUpload::uploadFile('data/invoices/' . $id_space . '/', 'template', 'template.twig');

            $this->checkTemplate($id_space);

            $_SESSION["message"] = InvoicesTranslator::TheTemplateHasBeenUploaded($lang) ;
            $this->redirect('invoicepdftemplate/' . $id_space);
            return;
        }

        $formUploadImages = new Form($this->request, "formUploadImages");
        $formUploadImages->setTitle(InvoicesTranslator::UploadImages($lang));
        $formUploadImages->addUpload("image", "");
        $formUploadImages->setValidationButton(CoreTranslator::Ok($lang), "invoicepdftemplate/" . $id_space);
        $formUploadImages->setButtonsWidth(2, 10);
        $formUploadImages->setColumnsWidth(0, 12);
        if ($formUploadImages->check()) {
            if (!file_exists('data/invoices/' . $id_space)) {
                mkdir('data/invoices/' . $id_space, 0777, true);
            }
            FileUpload::uploadFile('data/invoices/' . $id_space . '/', 'image', $_FILES["image"]["name"]);
            $this->redirect('invoicepdftemplate/' . $id_space);
            return;
        }

        $dataTable = new TableView();
        $dataTable->setTitle(InvoicesTranslator::Images($lang));
        $dataTable->addDeleteButton("invoicepdftemplatedelete/" . $id_space);

        $data = array();

        if (file_exists('data/invoices/' . $id_space)) {
            $files = scandir('data/invoices/' . $id_space);

            foreach ($files as $file) {
                if (strpos($file, ".") > 0 && $file != "template.twig") {
                    $data[] = array('name' => $file, 'id' => str_replace('.', "__pm__", $file));
                }
            }
        }

        $headers = array(
            "name" => InvoicesTranslator::Name($lang)
        );

        $tableHtml = $dataTable->view($data, $headers);

        $this->render(array("id_space" => $id_space,
            "formDownload" => $currentTemplate ? $formDownload->getHtml($lang): '',
            "formPreview" => $formPreview->getHtml($lang),
            "formUpload" => $formUpload->getHtml($lang), "tableHtml" => $tableHtml,
            "formUploadImages" => $formUploadImages->getHtml($lang),
            "lang" => $lang));
    }

    public function pdftemplatedeleteAction($id_space, $name) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $namefile = str_replace("__pm__", '.', $name);
        unlink('data/invoices/' . $id_space . '/' . $namefile);
        $this->redirect('invoicepdftemplate/' . $id_space);
    }

    protected function useInvoiceDatePaidForm($id_space, $lang) {

        $modelConfig = new CoreConfig();
        $useDatePaid = $modelConfig->getParamSpace("useInvoiceDatePaid", $id_space);

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(InvoicesTranslator::useInvoiceDatePaid($lang));

        $form->addSelect("useInvoiceDatePaid", InvoicesTranslator::useInvoiceDatePaid($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $useDatePaid);

        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    public function periodForm($modelCoreConfig, $id_space, $lang) {
        $invoiceperiodbegin = $modelCoreConfig->getParamSpace("invoiceperiodbegin", $id_space);
        $invoiceperiodend = $modelCoreConfig->getParamSpace("invoiceperiodend", $id_space);

        $form = new Form($this->request, "periodProjectForm");
        $form->addSeparator(InvoicesTranslator::invoiceperiod($lang));
        $form->addDate("invoiceperiodbegin", InvoicesTranslator::invoiceperiodbegin($lang), true, $invoiceperiodbegin);
        $form->addDate("invoiceperiodend", InvoicesTranslator::invoiceperiodend($lang), true, $invoiceperiodend);

        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
