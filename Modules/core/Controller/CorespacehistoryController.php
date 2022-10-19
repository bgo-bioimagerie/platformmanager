<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Configuration.php';
require_once 'Framework/Errors.php';

require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreHistory.php';
require_once 'Modules/core/Controller/CorespaceController.php';


/**
 *
 * @author sprigent
 * Controller for the home page
 */
class CorespacehistoryController extends CoresecureController
{
    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($idSpace)
    {
        $userSpaceStatus = $this->getUserSpaceStatus($idSpace, $_SESSION["id_user"]);
        if ($userSpaceStatus < CoreSpace::$MANAGER) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
        $lang = $this->getLanguage();
        $m = new CoreHistory();
        $startFilter = $_GET["start"] ?? null;
        $endFilter = $_GET["end"] ?? null;
        $logs = $m->list($idSpace, $startFilter, $endFilter);
        $this->render(array(
            "lang" => $lang,
            "id_space" => $idSpace,
            "logs" => $logs,
            "data" => ["logs" => $logs]
        ));
    }
}
