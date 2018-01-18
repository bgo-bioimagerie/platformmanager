<?php

require_once 'Framework/Controller.php';
require_once 'Framework/Form.php';
require_once 'Modules/core/Controller/CoresecureController.php';
require_once 'Modules/testapi/Model/TestapiTranslator.php';

require_once 'Modules/testapi/Model/TestapiPeople.php';

/**
 * 
 * @author sprigent
 * Controller for the home page
 */
class CoreloginApi extends Controller {

    /**
     * Constructor
     */
    public function __construct(Request $request) {
        parent::__construct($request);
        //$this->checkAuthorizationMenu("testapi");
    }

    public function loginAction() {

        if ($this->request->isParameter("login") && $this->request->isParameter("password")) {
            $this->login();
        } else {
            echo json_encode(array('status' => "error", 'error' => "login and password are not in the request"));
        }
    }

    protected function login() {
        $login = $this->request->getParameter("login");
        $pwd = $this->request->getParameter('password');

        $modelUser = new CoreUser();
        $status = $modelUser->login($login, $pwd);
        if ($status == "allowed") {
            if ($this->request->isParameter("space")) {
                $spaceName = $this->request->getParameter("space");
                $modelSpace = new CoreSpace();
                
                $user = $modelUser->getUserByLogin($login);
                
                $id_space = $modelSpace->getSpaceIdFromName($spaceName);
                $spaceRole = $modelSpace->getUserSpaceRole($id_space, $user[0]);
                echo json_encode(array('status' => $status, "space_role" => $spaceRole));
            }
            else{
                echo json_encode(array('status' => $status));
            }
        } else {
            echo json_encode(array('status' => "denied", 'error' => $status));
        }
    }

}
