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

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class InvoicesconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }

    public function mainMenu() {
        $id_space = isset($this->args['id_space']) ? $this->args['id_space'] : null;
        if ($id_space) {
            $csc = new CoreSpaceController($this->request);
            return $csc->navbar($id_space);
        }
        return null;
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
        $formMenusactivation = $this->menusactivationForm($id_space, $lang);
        if ($formMenusactivation->check()) {

            $modelSpace->setSpaceMenu(
                $id_space,
                "invoices",
                "invoices",
                "glyphicon glyphicon-euro",
                $this->request->getParameter("invoicesmenustatus"),
                $this->request->getParameter("invoicesmenudisplay"),
                1,
                $this->request->getParameter("invoicesmenucolor"),
                $this->request->getParameter("invoicesmenucolorTxt")
            );

            $this->redirect("invoicesconfig/" . $id_space);

            return;
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
        $formDownload->addDownloadButton("url", InvoicesTranslator::Download($lang), 'data/invoices/' . $id_space . '/template.php');

        if ($formDownload->check()) {

            $file = $this->request->getParameter('url');
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$file");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");

            // read the file from disk
            readfile($file);
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
            FileUpload::uploadFile('data/invoices/' . $id_space . '/', 'template', 'template.php');

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
                if (strpos($file, ".") > 0 && $file != "template.php") {
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

    protected function menusactivationForm($id_space, $lang) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "invoices");
        $displayUserMenu = $modelSpace->getSpaceMenusDisplay($id_space, "invoices");
        $invoicesmenucolor = $modelSpace->getSpaceMenusColor($id_space, "invoices");
        $invoicesmenucolorTxt = $modelSpace->getSpaceMenusTxtColor($id_space, "invoices");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("invoicesmenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        $form->addNumber("invoicesmenudisplay", CoreTranslator::Display_order($lang), false, $displayUserMenu);
        $form->addColor("invoicesmenucolor", CoreTranslator::color($lang), false, $invoicesmenucolor);
        $form->addColor("invoicesmenucolorTxt", CoreTranslator::text_color($lang), false, $invoicesmenucolorTxt);

        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    public function periodForm($modelCoreConfig, $id_space, $lang) {
        $invoiceperiodbegin = CoreTranslator::dateFromEn($modelCoreConfig->getParamSpace("invoiceperiodbegin", $id_space), $lang);
        $invoiceperiodend = CoreTranslator::dateFromEn($modelCoreConfig->getParamSpace("invoiceperiodend", $id_space), $lang);

        $form = new Form($this->request, "periodProjectForm");
        $form->addSeparator(InvoicesTranslator::invoiceperiod($lang));
        $form->addDate("invoiceperiodbegin", InvoicesTranslator::invoiceperiodbegin($lang), true, $invoiceperiodbegin);
        $form->addDate("invoiceperiodend", InvoicesTranslator::invoiceperiodend($lang), true, $invoiceperiodend);

        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
