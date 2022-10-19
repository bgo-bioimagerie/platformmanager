<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/resources/Model/ResourcesInstall.php';
require_once 'Modules/resources/Model/ResourcesTranslator.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';
require_once 'Modules/core/Controller/CorespaceController.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class ResourcesconfigController extends CoresecureController
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
        $formMenusactivation = $this->menusactivationForm($idSpace, 'resources', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($idSpace, 'resources', 'truck');
            return $this->redirect("resourcesconfig/".$idSpace);
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang)
                );

        $this->render(array("id_space" => $idSpace, "forms" => $forms, "lang" => $lang));
    }
}
