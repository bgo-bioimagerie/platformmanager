<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Framework/TableView.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/core/Model/CoreUser.php';
require_once 'Modules/core/Model/CoreStatus.php';
require_once 'Modules/core/Model/CoreSpace.php';
require_once 'Modules/core/Model/CoreInstalledModules.php';
require_once 'Modules/core/Model/CorePendingAccount.php';


/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CorespaceaccessController extends CoresecureController {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
    }

    /**
     * (non-PHPdoc)
     * @see Controller::indexAction()
     */
    public function indexAction($id_space) {

        $this->redirect("corespaceaccessusers/" . $id_space);
    }

    public function usersAction($id_space) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        $tableHtml = "table";

        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'tableHtml' => $tableHtml,
            "space" => $space
        ));
    }

    public function usersinactifAction($id_space) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        $tableHtml = "table";

        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'tableHtml' => $tableHtml,
            "space" => $space
        ));
    }

    public function pendingusersAction($id_space) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        $modelSpacePending = new CorePendingAccount();
        $modelUser = new CoreUser();

        $data = $modelSpacePending->getPendingForSpace($id_space);
        for ($i = 0; $i < count($data); $i++) {
            $data[$i]["fullname"] = $modelUser->getUserFUllName($data[$i]["id_user"]);
            $data[$i]["date_created"] = $modelUser->getDateCreated($data[$i]["id_user"]);
        }

        $table = new TableView();
        $table->setTitle(CoreTranslator::PendingUserAccounts($lang));
        $headers = array(
            'fullname' => CoreTranslator::Name($lang),
            'date_created' => CoreTranslator::DateCreated($lang)
        );
        $tableHtml = $table->view($data, $headers);

        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'tableHtml' => $tableHtml,
            "space" => $space
        ));
    }

    public function pendingusereditAction($id_space, $id_user) {
        $this->checkSpaceAdmin($id_space, $_SESSION["id_user"]);
        $lang = $this->getLanguage();

        $modelSpace = new CoreSpace();
        $space = $modelSpace->getSpace($id_space);

        $formHtml = "form";

        $this->render(array(
            'lang' => $lang,
            'id_space' => $id_space,
            'formHtml' => $formHtml,
            "space" => $space
        ));
    }

}
