<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/antibodies/Model/AntibodiesInstall.php';
require_once 'Modules/antibodies/Model/AntibodiesTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class AntibodiesconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        $_SESSION["openedNav"] = "antibodies";
        
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
        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang, $id_space);
        if ($formMenusactivation->check()) {

            
            $modelSpace->setSpaceMenu($id_space, "antibodies", "antibodies", "glyphicon-user", 
                    $this->request->getParameter("antibodiesmenustatus"),
                    $this->request->getParameter("displayMenu"),
                    1,
                    $this->request->getParameter("displayColor"),
                    $this->request->getParameter("displayTxtColor")
                    );
            
            $this->redirect("antibodiesconfig/".$id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang));
        
        return $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "antibodies");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "antibodies");
        $displayColor = $modelSpace->getSpaceMenusColor($id_space, "antibodies");
        $displayTxtColor = $modelSpace->getSpaceMenusTxtColor($id_space, "antibodies");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_modules($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("antibodiesmenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        $form->addNumber('displayMenu', CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor('displayColor', CoreTranslator::color($lang), false, $displayColor);
        $form->addColor('displayTxtColor', CoreTranslator::text_color($lang), false, $displayTxtColor);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "antibodiesconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

    
}
