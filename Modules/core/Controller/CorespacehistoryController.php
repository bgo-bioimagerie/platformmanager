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
class CorespacehistoryController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);

        if (!$this->isUserAuthorized(CoreStatus::$ADMIN)) {
            throw new PfmAuthException("Error 403: Permission denied", 403);
        }
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {
        $lang = $this->getLanguage();
        $m = new CoreHistory();
        $startFilter = $_GET["start"] ?? null;
        $endFilter = $_GET["end"] ?? null;
        $logs = $m->list($id_space, $startFilter, $endFilter);
        $this->render(array(
            "lang" => $lang,
            "id_space" => $id_space,
            "logs" => $logs,
            "data" => ["logs" => $logs]
        ));
    }
}

?>