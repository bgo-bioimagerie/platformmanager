<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/Errors.php';

require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Controller/CorespaceController.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreMail.php';
require_once 'Modules/core/Model/CoreSpace.php';

/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CoremailController extends CoresecureController
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
     * Show/edit mail subscriptions
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $spaceModel = new CoreSpace();
        $role = $spaceModel->getUserSpaceRole($idSpace, $_SESSION['id_user']);
        if ($role < CoreSpace::$USER) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
        $modules = Configuration::get("modules");
        $cm = new CoreMail();
        $mods = array();
        for ($i = 0; $i < count($modules); ++$i) {
            $moduleName = strtolower($modules[$i]);
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $subscription = $this->request->getParameterNoException("s_".$moduleName);
                if ($subscription == null) {
                    Configuration::getLogger()->debug("[core:mail] unsubscribe", ["module" => $moduleName]);
                    $cm->unsubscribe($_SESSION["id_user"], $idSpace, $moduleName);
                } else {
                    $cm->subscribe($_SESSION["id_user"], $idSpace, $moduleName);
                }
            }
            if ($cm->unsubscribed($_SESSION["id_user"], $idSpace, $moduleName)) {
                $mods[$moduleName] = 0;
            } else {
                $mods[$moduleName] = 1;
            }
        }


        $lang = $this->getLanguage();
        $this->render(array("lang" => $lang, "id_space" => $idSpace, "mods" => $mods));
    }
}
