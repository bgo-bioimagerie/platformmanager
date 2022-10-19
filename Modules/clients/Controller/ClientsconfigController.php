<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/clients/Model/ClientsInstall.php';
require_once 'Modules/clients/Model/ClientsTranslator.php';
require_once 'Modules/core/Model/CoreTranslator.php';
require_once 'Modules/core/Controller/CorespaceController.php';

require_once 'Modules/core/Model/CoreSpaceAccessOptions.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class ClientsconfigController extends CoresecureController
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

        // maintenance form
        //$formMenusactivation = $this->menusactivationForm($lang, $idSpace);
        $formMenusactivation = $this->menusactivationForm($idSpace, 'clients', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($idSpace, 'clients', 'credit-card');
            $modelAccess = new CoreSpaceAccessOptions();
            $toolname = "clientsuseraccounts";
            if ($this->request->getParameter("clientsMenustatus") > 0) {
                $modelAccess->exists($idSpace, $toolname)
                    ? $modelAccess->reactivate($idSpace, $toolname)
                    : $modelAccess->set($idSpace, $toolname, "clients", $toolname);
            } else {
                $modelAccess->delete($idSpace, $toolname);
            }

            $this->redirect("clientsconfig/".$idSpace);
            return;
        }

        // menu name
        $menuNameForm = $this->menuNameForm($idSpace, 'clients', $lang);
        if ($menuNameForm->check()) {
            $this->setMenuName($idSpace, 'clients');
            $this->redirect("clientsconfig/" . $idSpace);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
                       $menuNameForm->getHtml($lang)
            );

        $this->render(array("id_space" => $idSpace, "forms" => $forms, "lang" => $lang));
    }
}
