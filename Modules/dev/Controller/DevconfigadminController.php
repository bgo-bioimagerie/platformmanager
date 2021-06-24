<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/dev/Model/DevInstall.php';
require_once 'Modules/dev/Model/DevTranslator.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class DevconfigadminController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        
        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }
    
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction() {

        $lang = $this->getLanguage();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang);
        if ($formMenusactivation->check()) {

            //echo "dev menu status = " . $this->request->getParameter("devmenustatus") . "<br/>";
            
            $modelMenu = new CoreMenu();
            $modelMenu->setADminMenu("dev", "dev", "glyphicon glyphicon-console", $this->request->getParameter("devmenustatus"));
            
            $this->redirect("devconfigadmin");
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang)
                        );
        $this->render(array("forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang) {

        $modelMenu = new CoreMenu();
        //$statusSiteMenu = $modelMenu->getDataMenusUserType("dev");
        $statusSiteMenu = $modelMenu->isAdminMenu("dev");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $choices = array();
        $choicesid = array();
        $choices[] = CoreTranslator::disable($lang);
        $choicesid[] = 0;
        $choices[] = CoreTranslator::enable_for_admin($lang);
        $choicesid[] = 1;
        

        $form->addSelect("devmenustatus", DevTranslator::Dev($lang), $choices, $choicesid, $statusSiteMenu);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "devconfigadmin");
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
