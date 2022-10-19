<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/bulletjournal/Model/BulletjournalInstall.php';
require_once 'Modules/bulletjournal/Model/BulletjournalTranslator.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class BulletjournalconfigController extends CoresecureController
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
    public function indexAction($id_space)
    {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();
        // maintenance form
        $formMenusactivation = $this->menusactivationForm($id_space, 'bulletjournal', $lang);
        if ($formMenusactivation->check()) {
            $this->menusactivation($id_space, 'bulletjournal', 'book');

            $this->redirect("bulletjournalconfig/".$id_space);
            return;
        }

        // view
        $forms = array($formMenusactivation->getHtml($lang));

        $this->render(array("id_space" => $id_space, "forms" => $forms, "lang" => $lang));
    }
}
