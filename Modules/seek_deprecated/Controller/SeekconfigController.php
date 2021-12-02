<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/seek/Model/SeekInstall.php';
require_once 'Modules/seek/Model/SeekTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class SeekconfigController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);

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

            $modelSpace->setSpaceMenu($id_space, "seek", "seek", "glyphicon-briefcase", 
                    $this->request->getParameter("menustatus"), 
                    $this->request->getParameter("displayMenu"), 
                    0, 
                    $this->request->getParameter("menucolor")
            );

            $this->redirect("seekconfig/" . $id_space);
            return;
        }
        
        $formUrl = $this->seekURL($lang, $id_space);
        if($formUrl->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("seekurl", $this->request->getParameter("seekurl"), $id_space);
            
            $this->redirect("seekconfig/" . $id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang), $formUrl->getHtml($lang));

        $this->render(array("id_space" => $id_space, "forms" => $forms,
            "lang" => $lang));
    }

    protected function seekURL($lang, $id_space){
        
        $modelConfig = new CoreConfig();
        $seekUrl = $modelConfig->getParamSpace("seekurl", $id_space);
        
        $form = new Form($this->request, "seekurlform");
        $form->addSeparator(SeekTranslator::SeekUrl($lang));
        
        $form->addText("seekurl", SeekTranslator::Url($lang), true, $seekUrl);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "seekconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    protected function menusactivationForm($lang, $id_space) {

        $modelSpace = new CoreSpace();
        $statusMenu = $modelSpace->getSpaceMenusRole($id_space, "seek");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "seek");
        $displayColor = $modelSpace->getSpaceMenusColor($id_space, "seek");

        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("menustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor("menucolor", CoreTranslator::color($lang), false, $displayColor);

        $form->setValidationButton(CoreTranslator::Save($lang), "seekconfig/" . $id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
