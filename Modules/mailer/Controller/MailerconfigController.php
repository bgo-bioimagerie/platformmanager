<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/mailer/Model/MailerInstall.php';
require_once 'Modules/mailer/Model/MailerTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class MailerconfigController extends CoresecureController {

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

           $modelSpace->setSpaceMenu($id_space, "mailer", "mailer", "glyphicon-envelope", 
                   $this->request->getParameter("usermenustatus"),
                   $this->request->getParameter("displayMenu"),
                   0,
                   $this->request->getParameter("displayColor"),
                   $this->request->getParameter("displayColorTxt")
                   );
            
            $this->redirect("mailerconfig/".$id_space);
            return;
        }
        
        $MailerSetCopyToFrom = $this->MailerSetCopyToFromForm($lang, $id_space);
        if($MailerSetCopyToFrom->check()){
            $modelConfig = new CoreConfig();
            $modelConfig->setParam("MailerSetCopyToFrom", $this->request->getParameter('MailerSetCopyToFrom'), $id_space);
            
            $this->redirect("mailerconfig/".$id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
                       $MailerSetCopyToFrom->getHtml($lang) 
                        );
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

    protected function menusactivationForm($lang, $id_space) {
        
        $modelSpace = new CoreSpace();
        $statusUserMenu = $modelSpace->getSpaceMenusRole($id_space, "mailer");
        $displayMenu = $modelSpace->getSpaceMenusDisplay($id_space, "mailer");
        $displayColor = $modelSpace->getSpaceMenusColor($id_space, "mailer");
        $displayColorTxt = $modelSpace->getSpaceMenusTxtColor($id_space, "mailer");
        
        $form = new Form($this->request, "menusactivationForm");
        $form->addSeparator(CoreTranslator::Activate_desactivate_menus($lang));

        $roles = $modelSpace->roles($lang);

        $form->addSelect("usermenustatus", CoreTranslator::Users($lang), $roles["names"], $roles["ids"], $statusUserMenu);
        $form->addNumber("displayMenu", CoreTranslator::Display_order($lang), false, $displayMenu);
        $form->addColor("displayColor", CoreTranslator::color($lang), false, $displayColor);
        $form->addColor("displayColorTxt", CoreTranslator::color($lang), false, $displayColorTxt);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "mailerconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }
    
    protected function MailerSetCopyToFromForm($lang, $id_space){
        $modelConfig = new CoreConfig();
        $MailerSetCopyToFrom = $modelConfig->getParamSpace("MailerSetCopyToFrom", $id_space);
        
        $form = new Form($this->request, "MailerSetCopyToFromForm");
        $form->addSeparator(MailerTranslator::SendCopyToSender($lang));

        $form->addSelect("MailerSetCopyToFrom", "", array(CoreTranslator::yes($lang), CoreTranslator::no($lang)), array(1,0), $MailerSetCopyToFrom);
        
        $form->setValidationButton(CoreTranslator::Save($lang), "mailerconfig/".$id_space);
        $form->setButtonsWidth(2, 9);

        return $form;
    }

}
