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

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($id_space, 'helpdesk', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($id_space, 'helpdesk', 'credit-card');
            return $this->redirect("helpdeskconfig/".$id_space);
        }
        
        // menu name
        $menuNameForm = $this->menuNameForm($id_space, 'helpdesk', $lang);
        if ($menuNameForm->check()) {
            $this->setMenuName($id_space, 'helpdesk');
            return $this->redirect("helpdeskconfig/" . $id_space);
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

}
