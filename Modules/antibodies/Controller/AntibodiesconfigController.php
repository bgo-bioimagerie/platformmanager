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
class AntibodiesconfigController extends CoresecureController
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
        $formMenusactivation = $this->menusactivationForm($idSpace, 'antibodies', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($idSpace, 'antibodies', 'person');
            return $this->redirect("antibodiesconfig/".$idSpace);
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang));

        return $this->render(array("id_space" => $idSpace, "forms" => $forms, "lang" => $lang));
    }
}
