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
class ClientsconfigController extends CoresecureController {

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

        // maintenance form
        //$formMenusactivation = $this->menusactivationForm($lang, $id_space);
        $formMenusactivation = $this->menusactivationForm($id_space, 'clients', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($id_space, 'clients', 'credit-card');
            $modelAccess = new CoreSpaceAccessOptions();
            $toolname = "clientsuseraccounts";
            if ( $this->request->getParameter("clientsMenustatus") > 0 ) {
                $modelAccess->exists($id_space, $toolname)
                    ? $modelAccess->reactivate($id_space, $toolname)
                    : $modelAccess->set($id_space, $toolname, "clients", $toolname);
            } else if ($modelAccess->exists($id_space, $toolname)) {
                $modelAccess->delete($id_space, $toolname);
            }

            $this->redirect("clientsconfig/".$id_space);
            return;
        }
        
        // menu name
        $menuNameForm = $this->menuNameForm($id_space, 'clients', $lang);
        if ($menuNameForm->check()) {
            $this->setMenuName($id_space, 'clients');
            $this->redirect("clientsconfig/" . $id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang),
                       $menuNameForm->getHtml($lang)
            );
        
        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }

}
