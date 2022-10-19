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

require_once 'Modules/core/Controller/CoreabstractpdftemplateController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class InvoicesconfigController extends PfmTemplateController
{
    /**
     * Constructor
     */
    public function __construct(Request $request, ?array $space=null)
    {
        parent::__construct($request, $space);

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }


    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $this->checkSpaceAdmin($idSpace, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelCoreConfig = new CoreConfig();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($idSpace, 'invoices', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($idSpace, 'invoices', 'currency-euro');
            return $this->redirect("invoicesconfig/" . $idSpace);
        }

        $modelCoreConfig = new CoreConfig();
        // period invoices
        $formPeriod = $this->periodForm($modelCoreConfig, $idSpace, $lang);
        if ($formPeriod->check()) {
            $modelCoreConfig->setParam("invoiceperiodbegin", CoreTranslator::dateToEn($this->request->getParameter("invoiceperiodbegin"), $lang), $idSpace);
            $modelCoreConfig->setParam("invoiceperiodend", CoreTranslator::dateToEn($this->request->getParameter("invoiceperiodend"), $lang), $idSpace);

            return $this->redirect("invoicesconfig/" . $idSpace);
        }

        // options
        $formUseInvoiceDatePaid = $this->useInvoiceDatePaidForm($idSpace, $lang);
        if ($formUseInvoiceDatePaid->check()) {
            $modelCoreConfig->setParam("useInvoiceDatePaid", $this->request->getParameter("useInvoiceDatePaid"), $idSpace);
            return $this->redirect("invoicesconfig/" . $idSpace);
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
            $formPeriod->getHtml($lang)
        );
        return $this->render(array("id_space" => $idSpace, "forms" => $forms, "lang" => $lang));
    }

    public function pdftemplateAction($idSpace)
    {
        return $this->pdftemplate($idSpace, 'invoices', new InvoicesTranslator());
    }

    public function pdftemplatedeleteAction($idSpace, $name)
    {
        return $this->pdftemplatedelete($idSpace, 'invoices', $name);
    }

    protected function useInvoiceDatePaidForm($idSpace, $lang)
    {
        $modelConfig = new CoreConfig();
        $useDatePaid = $modelConfig->getParamSpace("useInvoiceDatePaid", $idSpace);

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(InvoicesTranslator::useInvoiceDatePaid($lang));

        $form->addSelect("useInvoiceDatePaid", InvoicesTranslator::useInvoiceDatePaid($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $useDatePaid);

        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesconfig/" . $idSpace);


        return $form;
    }

    public function periodForm($modelCoreConfig, $idSpace, $lang)
    {
        $invoiceperiodbegin = $modelCoreConfig->getParamSpace("invoiceperiodbegin", $idSpace);
        $invoiceperiodend = $modelCoreConfig->getParamSpace("invoiceperiodend", $idSpace);

        $form = new Form($this->request, "periodProjectForm");
        $form->addSeparator(InvoicesTranslator::invoiceperiod($lang));
        $form->addDate("invoiceperiodbegin", InvoicesTranslator::invoiceperiodbegin($lang), true, $invoiceperiodbegin);
        $form->addDate("invoiceperiodend", InvoicesTranslator::invoiceperiodend($lang), true, $invoiceperiodend);

        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesconfig/" . $idSpace);


        return $form;
    }
}
