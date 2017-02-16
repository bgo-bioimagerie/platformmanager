<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
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
    public function __construct() {
        parent::__construct();

        if (!$this->isUserAuthorized(CoreStatus::$USER)) {
            throw new Exception("Error 503: Permission denied");
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
        $formMenusactivation = $this->menusactivationForm($id_space, $lang);
        if ($formMenusactivation->check()) {

            $modelSpace->setSpaceMenu($id_space, "invoices", "invoices", "glyphicon glyphicon-euro", $this->request->getParameter("invoicesmenustatus"));

            $this->redirect("invoicesconfig/" . $id_space);

            return;
        }

        // color menu form
        $formMenuColor = $this->menuColorForm($modelCoreConfig, $id_space, $lang);
        if ($formMenuColor->check()) {
            $modelCoreConfig->setParam("invoicesmenucolor", $this->request->getParameter("invoicesmenucolor"), $id_space);
            $modelCoreConfig->setParam("invoicesmenucolortxt", $this->request->getParameter("invoicesmenucolortxt"), $id_space);

            $this->redirect("invoicesconfig/" . $id_space);
            return;
        }
        
        // period invoices
        $formPeriod = $this->periodForm($modelCoreConfig, $id_space, $lang);
        if($formPeriod->check()){
            $modelCoreConfig->setParam("invoiceperiodbegin", CoreTranslator::dateToEn($this->request->getParameter("invoiceperiodbegin"), $lang) , $id_space);
            $modelCoreConfig->setParam("invoiceperiodend", CoreTranslator::dateToEn($this->request->getParameter("invoiceperiodend"), $lang), $id_space);
            
            $this->redirect("invoicesconfig/".$id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang), $formMenuColor->getHtml($lang),
                       $formPeriod->getHtml($lang)
        );
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($id_space, $lang) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "invoices");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("invoicesmenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);

        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    public function menuColorForm($modelCoreConfig, $id_space, $lang) {
        $ecmenucolor = $modelCoreConfig->getParamSpace("invoicesmenucolor", $id_space);
        $ecmenucolortxt = $modelCoreConfig->getParamSpace("invoicesmenucolortxt", $id_space);

        $form = new Form($this->request, "menuColorForm");
        $form->addSeparator(CoreTranslator::color($lang));
        $form->addColor("invoicesmenucolor", CoreTranslator::menu_color($lang), false, $ecmenucolor);
        $form->addColor("invoicesmenucolortxt", CoreTranslator::text_color($lang), false, $ecmenucolortxt);

        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    public function periodForm($modelCoreConfig, $id_space, $lang){
        $invoiceperiodbegin = CoreTranslator::dateFromEn($modelCoreConfig->getParamSpace("invoiceperiodbegin", $id_space), $lang);
        $invoiceperiodend = CoreTranslator::dateFromEn($modelCoreConfig->getParamSpace("invoiceperiodend", $id_space), $lang);
        
        $form = new Form($this->request, "periodProjectForm");
        $form->addSeparator(InvoicesTranslator::invoiceperiod($lang));
        $form->addDate("invoiceperiodbegin", InvoicesTranslator::invoiceperiodbegin($lang), true, $invoiceperiodbegin);
        $form->addDate("invoiceperiodend", InvoicesTranslator::invoiceperiodend($lang), true, $invoiceperiodend);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesconfig/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        return $form;
    }
    
    public function projectCommandForm($modelCoreConfig, $id_space, $lang){
        $servicesuseproject = CoreTranslator::dateFromEn($modelCoreConfig->getParamSpace("servicesuseproject", $id_space), $lang);
        $servicesusecommand = CoreTranslator::dateFromEn($modelCoreConfig->getParamSpace("servicesusecommand", $id_space), $lang);
        
        $form = new Form($this->request, "periodCommandForm");
        $form->addSeparator(InvoicesTranslator::invoiceperiod($lang));
        $form->addDate("servicesuseproject", InvoicesTranslator::invoiceperiodbegin($lang), true, $servicesuseproject);
        $form->addDate("servicesusecommand", InvoicesTranslator::invoiceperiodend($lang), true, $servicesusecommand);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "invoicesconfig/".$id_space);
        $form->setButtonsWidth(2, 9);
        
        return $form;
    }

}
