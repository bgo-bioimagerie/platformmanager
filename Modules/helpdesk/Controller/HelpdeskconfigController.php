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


class HelpdeskconfigController extends CoresecureController
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

        $modelSpace = new CoreSpace();

        // maintenance form
        $formMenusactivation = $this->menusactivationForm($idSpace, 'helpdesk', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($idSpace, 'helpdesk', 'credit-card');
            return $this->redirect("helpdeskconfig/".$idSpace);
        }

        // menu name
        $menuNameForm = $this->menuNameForm($idSpace, 'helpdesk', $lang);
        if ($menuNameForm->check()) {
            $this->setMenuName($idSpace, 'helpdesk');
            return $this->redirect("helpdeskconfig/" . $idSpace);
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
                       $menuNameForm->getHtml($lang)
            );


        $space = $modelSpace->getSpace($idSpace);
        $hm = new Helpdesk();
        $fromAddress = $hm->fromAddress($space);

        $this->render(array(
            "id_space" => $idSpace,
            "forms" => $forms,
            "lang" => $lang,
            "fromAddress" => $fromAddress
        ));
    }
}
