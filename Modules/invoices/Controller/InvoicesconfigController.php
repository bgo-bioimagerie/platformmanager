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
    public function indexAction($id_space)
    {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelCoreConfig = new CoreConfig();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($id_space, 'invoices', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($id_space, 'invoices', 'currency-euro');
            return $this->redirect("invoicesconfig/" . $id_space);
        }

        $modelCoreConfig = new CoreConfig();
        // period invoices
        $formPeriod = $this->periodForm($modelCoreConfig, $id_space, $lang);
        if ($formPeriod->check()) {
            $modelCoreConfig->setParam("invoiceperiodbegin", CoreTranslator::dateToEn($this->request->getParameter("invoiceperiodbegin"), $lang), $id_space);
            $modelCoreConfig->setParam("invoiceperiodend", CoreTranslator::dateToEn($this->request->getParameter("invoiceperiodend"), $lang), $id_space);

            return $this->redirect("invoicesconfig/" . $id_space);
        }

        // options
        $formUseInvoiceDatePaid = $this->useInvoiceDatePaidForm($id_space, $lang);
        if ($formUseInvoiceDatePaid->check()) {
            $modelCoreConfig->setParam("useInvoiceDatePaid", $this->request->getParameter("useInvoiceDatePaid"), $id_space);
            return $this->redirect("invoicesconfig/" . $id_space);
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
            $formPeriod->getHtml($lang)
        );
        return $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    public function pdftemplateAction($id_space)
    {
        return $this->pdftemplate($id_space, 'invoices', new InvoicesTranslator());
    }

    public function pdftemplatedeleteAction($id_space, $name)
    {
        return $this->pdftemplatedelete($id_space, 'invoices', $name);
    }

    protected function useInvoiceDatePaidForm($id_space, $lang)
    {
        $modelConfig = new CoreConfig();
        $useDatePaid = $modelConfig->getParamSpace("useInvoiceDatePaid", $id_space);

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(InvoicesTranslator::useInvoiceDatePaid($lang));

        $form->addSelect("useInvoiceDatePaid", InvoicesTranslator::useInvoiceDatePaid($lang), array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1, 0), $useDatePaid);

        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesconfig/" . $id_space);


        return $form;
    }

    public function periodForm($modelCoreConfig, $id_space, $lang)
    {
        $invoiceperiodbegin = $modelCoreConfig->getParamSpace("invoiceperiodbegin", $id_space);
        $invoiceperiodend = $modelCoreConfig->getParamSpace("invoiceperiodend", $id_space);

        $form = new Form($this->request, "periodProjectForm");
        $form->addSeparator(InvoicesTranslator::invoiceperiod($lang));
        $form->addDate("invoiceperiodbegin", InvoicesTranslator::invoiceperiodbegin($lang), true, $invoiceperiodbegin);
        $form->addDate("invoiceperiodend", InvoicesTranslator::invoiceperiodend($lang), true, $invoiceperiodend);

        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesconfig/" . $id_space);


        return $form;
    }
}
