<?php

require_once 'Framework/Controller.php';

/**
 * Controler managing the user connection 
 * 
 * @author Sylvain Prigent
 */
class CoreconnectscriptController extends Controller {

    /**
     * (non-PHPdoc)
     * @see Controller::index()
     * 
     */
    public function indexAction() {
        
        $modelUser = new CoreUser();
        //echo "update last connection <br/>";
        $modelUser->updateLastConnection($_SESSION['id_user']);
        
        
        $modelSpace = new CoreSpace();
        if ($modelSpace->isUserSpaceAdmin($_SESSION['id_user'])) {
            //echo "update user active <br/>";
            $modelUser->updateUsersActive();
        }
    }

}
