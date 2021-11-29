<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/helpdesk/Model/HelpdeskTranslator.php';
require_once 'Modules/helpdesk/Model/Helpdesk.php';

require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/core/Model/CoreSpaceAccessOptions.php';


class HelpdeskconfigController extends CoresecureController {

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

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($lang, $id_space);
        if ($formMenusactivation->check()) {

            $modelSpace->setSpaceMenu($id_space, "helpdesk", "helpdesk", "glyphicon-credit-card", 
                    $this->request->getParameter("helpdeskmenustatus"),
                    $this->request->getParameter("displayMenu"),
                    1,
                    $this->request->getParameter("colorMenu"),
                    $this->request->getParameter("colorTxtMenu")
            );
                
            $this->redirect("helpdeskconfig/".$id_space);
            return;
        }
        
        // menu name
        $menuNameForm = $this->menuName($lang, $id_space);
        if ($menuNameForm->check()) {
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("helpdeskMenuName", $this->request->getParameter("helpdeskMenuName"), $id_space);
            $this->redirect("helpdeskconfig/" . $id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
                       $menuNameForm->getHtml($lang)
            );

       
        $space = $modelSpace->getSpace($id_space);
        $hm = new Helpdesk();
        $fromAddress = $hm->fromAddress($space);
        
        $this->render(array(
            "id_space" => $id_space,
            "forms" => $forms,
            "lang" => $lang,
            "fromAddress" => $fromAddress
        ));
    }

    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusHelpdeskMenu = $modelSpace->getSpaceMenusRole($id_space, "helpdesk");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "helpdesk");
        $colorMenu = $modelSpace->getSpaceMenusColor($id_space, "helpdesk");
        $colorTxtMenu = $modelSpace->getSpaceMenusTxtColor($id_space, "helpdesk");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("helpdeskmenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusHelpdeskMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor("colorMenu", CoreTranslator::color($lang), false, $colorMenu);
        $form->addColor("colorTxtMenu", CoreTranslator::text_color($lang), false, $colorTxtMenu);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "helpdeskconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    protected function menuName($lang, $id_space) {
        $modelConfig = new CoreConfig();
        $menuName = $modelConfig->getParamSpace("helpdeskMenuName", $id_space);

        $form = new Form($this->request, "helpdeskMenuNameForm");
        $form->addSeparator(HelpdeskTranslator::MenuName($lang));

        $form->addText("helpdeskMenuName", CoreTranslator::Name($lang), false, $menuName);

        $form->setValidationButton(CoreTranslator::Save($lang), "helpdeskconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
